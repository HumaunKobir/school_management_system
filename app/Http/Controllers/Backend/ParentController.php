<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParentRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ParentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ParentController extends Controller
{
    use SystemTrait;

    protected $parentService;

    public function __construct(ParentService $parentService)
    {
        $this->parentService = $parentService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Parent/Index',
            [
                'pageTitle' => fn () => 'Parent List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Parent Manage'],
                    ['link' => route('backend.parent.index'), 'title' => 'Parent List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->parentService->list();

        if (request()->filled('phone'))
            $query->where('phone', 'like', request()->phone . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->name = $data->name;
            $customData->photo = '<img src="' . $data->photo . '" height="50" width="50"/>';
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.lowercaseModel.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.lowercaseModel.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.lowercaseModel.destroy', $data->id),
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
            ['fieldName' => 'photo', 'class' => 'text-center'],
            ['fieldName' => 'name', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Photo',
            'Name',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        return Inertia::render(
            'Backend/Parent/Form',
            [
                'pageTitle' => fn () => 'Parent Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Parent Manage'],
                    ['link' => route('backend.parent.create'), 'title' => 'Parent Create'],
                ],
            ]
        );
    }


    public function store(ParentRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'parents');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'parents');


            $dataInfo = $this->parentService->create($data);

            if ($dataInfo) {
                $message = 'Parent created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'parents', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Parent.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'ParentController', 'store', substr($err->getMessage(), 0, 1000));
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
        $parent = $this->parentService->find($id);

        return Inertia::render(
            'Backend/Parent/Form',
            [
                'pageTitle' => fn () => 'Parent Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Parent Manage'],
                    ['link' => route('backend.parent.edit', $id), 'title' => 'Parent Edit'],
                ],
                'parent' => fn () => $parent,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(ParentRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $parent = $this->parentService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'parents');
                $path = strstr($parent->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($parent->image ?? '', 'parents');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'parents/');
                $path = strstr($parent->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($parent->file ?? '', 'parents/');
            }

            $dataInfo = $this->parentService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Parent updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'parents', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update parents.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ParentController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->parentService->delete($id)) {
                $message = 'Parent deleted successfully';
                $this->storeAdminWorkLog($id, 'parents', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Parent.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ParentController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->parentService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Parent ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'parents', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Parent.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ParentController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }