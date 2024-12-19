<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassesRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ClassesService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ClassesController extends Controller
{
    use SystemTrait;

    protected $classesService;

    public function __construct(ClassesService $classesService)
    {
        $this->classesService = $classesService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Classes/Index',
            [
                'pageTitle' => fn () => 'Classes List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Classes Manage'],
                    ['link' => route('backend.classes.index'), 'title' => 'Classes List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->classesService->list();

        if (request()->filled('name'))
            $query->where('name', 'like', request()->name . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->name = $data->name;
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.classes.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.classes.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.classes.destroy', $data->id),
                    'linkLabel' => getLinkLabel('Delete', null, null)
                ]

            ];
            return $customData;
        });

        return regeneratePagination($formatedDatas, $datas->total(), $datas->perPage(), $datas->currentPage());
    }

    private function dataFields()
    {
        return [
            ['fieldName' => 'index', 'class' => 'text-center'],
            ['fieldName' => 'name', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Name',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        return Inertia::render(
            'Backend/Classes/Form',
            [
                'pageTitle' => fn () => 'Classes Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Classes Manage'],
                    ['link' => route('backend.classes.create'), 'title' => 'Classes Create'],
                ],
            ]
        );
    }


    public function store(ClassesRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();


            $dataInfo = $this->classesService->create($data);

            if ($dataInfo) {
                $message = 'Classes created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Classes.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassesController', 'store', substr($err->getMessage(), 0, 1000));
            dd($err);
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            // dd($message);
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }

    public function edit($id)
    {
        $classes = $this->classesService->find($id);

        return Inertia::render(
            'Backend/Classes/Form',
            [
                'pageTitle' => fn () => 'Classes Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Classes Manage'],
                    ['link' => route('backend.classes.edit', $id), 'title' => 'Classes Edit'],
                ],
                'classes' => fn () => $classes,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(ClassesRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $classes = $this->classesService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'classes');
                $path = strstr($classes->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($classes->image ?? '', 'classes');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'classes/');
                $path = strstr($classes->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($classes->file ?? '', 'classes/');
            }

            $dataInfo = $this->classesService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Classes updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update classes.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassesController', 'update', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }

    public function destroy($id)
    {

        DB::beginTransaction();

        try {

            if ($this->classesService->delete($id)) {
                $message = 'Classes deleted successfully';
                $this->storeAdminWorkLog($id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Classes.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassesController', 'destroy', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }

    public function changeStatus(Request $request, $id, $status)
    {
        DB::beginTransaction();

        try {

            $dataInfo = $this->classesService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Classes ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Classes.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassesController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }