<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use Illuminate\Support\Facades\DB;
use App\Services\GroupService;
use App\Services\ClassesService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class GroupController extends Controller
{
    use SystemTrait;

    protected $groupService,$classesService;

    public function __construct(GroupService $groupService,ClassesService $classesService)
    {
        $this->groupService = $groupService;
        $this->classesService = $classesService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Group/Index',
            [
                'pageTitle' => fn () => 'Group List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Group Manage'],
                    ['link' => route('backend.group.index'), 'title' => 'Group List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->groupService->list();

        if (request()->filled('name'))
            $query->where('name', 'like', request()->name . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->class_id = $data->class->name;
            $customData->name = $data->name;
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.group.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.group.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.group.destroy', $data->id),
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
            ['fieldName' => 'class_id', 'class' => 'text-center'],
            ['fieldName' => 'name', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Class Name',
            'Name',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        $classes = $this->classesService->activeList();
        return Inertia::render(
            'Backend/Group/Form',
            [
                'pageTitle' => fn () => 'Group Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Group Manage'],
                    ['link' => route('backend.group.create'), 'title' => 'Group Create'],
                ],
                'classes' => fn() => $classes,
            ]
        );
    }


    public function store(GroupRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();


            $dataInfo = $this->groupService->create($data);

            if ($dataInfo) {
                $message = 'Group created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'groups', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Group.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'GroupController', 'store', substr($err->getMessage(), 0, 1000));
            //dd($err);
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
        $group = $this->groupService->find($id);
        $classes = $this->classesService->activeList();

        return Inertia::render(
            'Backend/Group/Form',
            [
                'pageTitle' => fn () => 'Group Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Group Manage'],
                    ['link' => route('backend.group.edit', $id), 'title' => 'Group Edit'],
                ],
                'group' => fn () => $group,
                'id' => fn () => $id,
                'classes' => fn() => $classes
            ]
        );
    }

    public function update(GroupRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $group = $this->groupService->find($id);

            

            $dataInfo = $this->groupService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Group updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'groups', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update groups.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'GroupController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->groupService->delete($id)) {
                $message = 'Group deleted successfully';
                $this->storeAdminWorkLog($id, 'groups', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Group.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'GroupController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->groupService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Group ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'groups', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Group.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'GroupController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }