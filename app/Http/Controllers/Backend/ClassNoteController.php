<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassNoteRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ClassNoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ClassNoteController extends Controller
{
    use SystemTrait;

    protected $classnoteService;

    public function __construct(ClassNoteService $classnoteService)
    {
        $this->classnoteService = $classnoteService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/ClassNote/Index',
            [
                'pageTitle' => fn () => 'ClassNote List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassNote Manage'],
                    ['link' => route('backend.classnote.index'), 'title' => 'ClassNote List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {

        $query = $this->classnoteService->list();

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
            'Backend/ClassNote/Form',
            [
                'pageTitle' => fn () => 'ClassNote Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassNote Manage'],
                    ['link' => route('backend.classnote.create'), 'title' => 'ClassNote Create'],
                ],
            ]
        );
    }


    public function store(ClassNoteRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'classnotes');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'classnotes');


            $dataInfo = $this->classnoteService->create($data);

            if ($dataInfo) {
                $message = 'ClassNote created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classnotes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create ClassNote.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassNoteController', 'store', substr($err->getMessage(), 0, 1000));
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
        $classnote = $this->classnoteService->find($id);

        return Inertia::render(
            'Backend/ClassNote/Form',
            [
                'pageTitle' => fn () => 'ClassNote Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassNote Manage'],
                    ['link' => route('backend.classnote.edit', $id), 'title' => 'ClassNote Edit'],
                ],
                'classnote' => fn () => $classnote,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(ClassNoteRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $classnote = $this->classnoteService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'classnotes');
                $path = strstr($classnote->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($classnote->image ?? '', 'classnotes');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'classnotes/');
                $path = strstr($classnote->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($classnote->file ?? '', 'classnotes/');
            }

            $dataInfo = $this->classnoteService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'ClassNote updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classnotes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update classnotes.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassNoteController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->classnoteService->delete($id)) {
                $message = 'ClassNote deleted successfully';
                $this->storeAdminWorkLog($id, 'classnotes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete ClassNote.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassNoteController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->classnoteService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'ClassNote ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classnotes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "ClassNote.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassNoteController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }