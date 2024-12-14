<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ClassService;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ClassController extends Controller
{
    use SystemTrait;

    protected $classService;

    public function __construct(ClassService $classService)
    {
        $this->classService = $classService;
    }

    public function index()
    {
        return Inertia::render(
            'Backend/Class/Index',
            [
                'pageTitle' => fn () => 'User List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'User Manage'],
                    ['link' => route('backend.class.index'), 'title' => 'User List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas()
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->classService->list();


        if (request()->filled('class_name'))
            $query->where('class_name', 'like', request()->class_name . '%');

        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->name = $data->class_name;
            $customData->email = $data->section;
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.class.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.class.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.class.destroy', $data->id),
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
            ['fieldName' => 'class_name', 'class' => 'text-center'],
            ['fieldName' => 'section', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Class Name',
            'Section',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        return Inertia::render(
            'Backend/Class/Form',
            [
                'pageTitle' => fn () => 'Class Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Class Manage'],
                    ['link' => route('backend.class.create'), 'title' => 'Class Create'],
                ],
            ]
        );
    }

    public function store(ClassRequest $request)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();


            $dataInfo = $this->classService->create($data);

            if ($dataInfo) {
                $message = 'Class created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create user.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassController', 'store', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }

    public function edit($id)
    {
        $user = $this->classService->find($id);

        return Inertia::render(
            'Backend/Class/Form',
            [
                'pageTitle' => fn () => 'Class Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Class Manage'],
                    ['link' => route('backend.class.edit', $user->id), 'title' => 'Branch Edit'],
                ],
            ]
        );
    }

    public function update(ClassRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $admin = $this->classService->find($id);
            $data = $request->validated();

            $dataInfo = $this->classService->update($data, $id);
            if ($dataInfo->wasChanged()) {
                $message = 'Class updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update Branch.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassController', 'update', substr($err->getMessage(), 0, 1000));
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
            $dataInfo = $this->classService->delete($id);

            if ($dataInfo->wasChanged()) {
                $message = 'Class deleted successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete User.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassController', 'destroy', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }

    public function changeStatus()
    {
        DB::beginTransaction();

        try {
            $dataInfo = $this->classService->changeStatus(request());

            if ($dataInfo->wasChanged()) {
                $message = 'User ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classes', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . " User.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
}
