<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRoomRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ClassRoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ClassRoomController extends Controller
{
    use SystemTrait;

    protected $classroomService;

    public function __construct(ClassRoomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/ClassRoom/Index',
            [
                'pageTitle' => fn () => 'ClassRoom List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassRoom Manage'],
                    ['link' => route('backend.classroom.index'), 'title' => 'ClassRoom List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->classroomService->list();

        if (request()->filled('room_number'))
            $query->where('room_number', 'like', request()->room_number . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->room_number = $data->room_number;
            $customData->capacity = $data->capacity;
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
            ['fieldName' => 'room_number', 'class' => 'text-center'],
            ['fieldName' => 'capacity', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Room Number',
            'Capacity',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        return Inertia::render(
            'Backend/ClassRoom/Form',
            [
                'pageTitle' => fn () => 'ClassRoom Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassRoom Manage'],
                    ['link' => route('backend.classroom.create'), 'title' => 'ClassRoom Create'],
                ],
            ]
        );
    }


    public function store(ClassRoomRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'classrooms');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'classrooms');


            $dataInfo = $this->classroomService->create($data);

            if ($dataInfo) {
                $message = 'ClassRoom created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classrooms', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create ClassRoom.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoomController', 'store', substr($err->getMessage(), 0, 1000));
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
        $classroom = $this->classroomService->find($id);

        return Inertia::render(
            'Backend/ClassRoom/Form',
            [
                'pageTitle' => fn () => 'ClassRoom Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassRoom Manage'],
                    ['link' => route('backend.classroom.edit', $id), 'title' => 'ClassRoom Edit'],
                ],
                'classroom' => fn () => $classroom,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(ClassRoomRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $classroom = $this->classroomService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'classrooms');
                $path = strstr($classroom->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($classroom->image ?? '', 'classrooms');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'classrooms/');
                $path = strstr($classroom->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($classroom->file ?? '', 'classrooms/');
            }

            $dataInfo = $this->classroomService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'ClassRoom updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classrooms', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update classrooms.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoomController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->classroomService->delete($id)) {
                $message = 'ClassRoom deleted successfully';
                $this->storeAdminWorkLog($id, 'classrooms', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete ClassRoom.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoomController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->classroomService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'ClassRoom ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classrooms', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "ClassRoom.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoomController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }