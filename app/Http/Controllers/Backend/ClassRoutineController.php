<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRoutineRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ClassRoutineService;
use App\Services\SessionService;
use App\Services\ClassesService;
use App\Services\SectionService;
use App\Services\GroupService;
use App\Services\TeacherService;
use App\Services\SubjectService;
use App\Services\ClassRoomService;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ClassRoutineController extends Controller
{
    use SystemTrait;

    protected $classroutineService,$sessionService, $classesService,$sectionService,$groupService,$subjectService,$classRoomService,$teacherService;

    public function __construct(ClassRoutineService $classroutineService,SessionService $sessionService, ClassesService $classesService,SectionService $sectionService,GroupService $groupService,SubjectService $subjectService,ClassRoomService $classRoomService, TeacherService $teacherService)
    {
        $this->classroutineService = $classroutineService;
        $this->sessionService = $sessionService;
        $this->classesService = $classesService;
        $this->sectionService = $sectionService;
        $this->groupService = $groupService;
        $this->teacherService = $teacherService;
        $this->subjectService = $subjectService;
        $this->classRoomService = $classRoomService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/ClassRoutine/Index',
            [
                'pageTitle' => fn () => 'ClassRoutine List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassRoutine Manage'],
                    ['link' => route('backend.classroutine.index'), 'title' => 'ClassRoutine List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {   
        $query = $this->classroutineService->list();

        if (request()->filled('class'))
            $query->where('phone', 'like', request()->phone . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->session_id = $data->session->session_year ?? '';
            $customData->class_id = $data->class->name ?? '';
            $customData->section_id = $data->section->name ?? '';
            $customData->group_id = $data->group->name ?? '';
            $customData->teacher_id = $data->teacher->name ?? '';
            $customData->subject_id = $data->subject->name ?? '';
            $customData->room_id = $data->classRoom->room_number ?? '';
            $customData->day = $data->day ?? '';
            $customData->start_time = $data->start_time ?? '';
            $customData->end_time = $data->end_time ?? '';
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.classroutine.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.classroutine.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.classroutine.destroy', $data->id),
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
            ['fieldName' => 'session_id', 'class' => 'text-center'],
            ['fieldName' => 'class_id', 'class' => 'text-center'],
            ['fieldName' => 'section_id', 'class' => 'text-center'],
            ['fieldName' => 'group_id', 'class' => 'text-center'],
            ['fieldName' => 'teacher_id', 'class' => 'text-center'],
            ['fieldName' => 'subject_id', 'class' => 'text-center'],
            ['fieldName' => 'room_id', 'class' => 'text-center'],
            ['fieldName' => 'day', 'class' => 'text-center'],
            ['fieldName' => 'start_time', 'class' => 'text-center'],
            ['fieldName' => 'end_time', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Session Year',
            'Class Name',
            'Section',
            'Group',
            'Teacher',
            'Subject',
            'Room Number',
            'Day',
            'Start Time',
            'End Time',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        $sessions = $this->sessionService->activeList();
        $classes = $this->classesService->activeList();
        $sections = $this->sectionService->activeList();
        $groups = $this->groupService->activeList();
        $teachers = $this->teacherService->activeList();
        $subjects = $this->subjectService->activeList();
        $rooms = $this->classRoomService->activeList();
        return Inertia::render(
            'Backend/ClassRoutine/Form',
            [
                'pageTitle' => fn () => 'ClassRoutine Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassRoutine Manage'],
                    ['link' => route('backend.classroutine.create'), 'title' => 'ClassRoutine Create'],
                ],
                'sessions' => fn() => $sessions,
                'classes' => fn() => $classes,
                'sections' => fn() => $sections,
                'groups' => fn() => $groups,
                'teachers' => fn() => $teachers,
                'subjects' => fn() => $subjects,
                'rooms' => fn() => $rooms,
            ]
        );
    }


    public function store(ClassRoutineRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();


            $dataInfo = $this->classroutineService->create($data);

            if ($dataInfo) {
                $message = 'ClassRoutine created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classroutines', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create ClassRoutine.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoutineController', 'store', substr($err->getMessage(), 0, 1000));
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
        $classroutine = $this->classroutineService->find($id);
        $sessions = $this->sessionService->activeList();
        $classes = $this->classesService->activeList();
        $sections = $this->sectionService->activeList();
        $groups = $this->groupService->activeList();
        $teachers = $this->teacherService->activeList();
        $subjects = $this->subjectService->activeList();
        $rooms = $this->classRoomService->activeList();

        return Inertia::render(
            'Backend/ClassRoutine/Form',
            [
                'pageTitle' => fn () => 'ClassRoutine Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassRoutine Manage'],
                    ['link' => route('backend.classroutine.edit', $id), 'title' => 'ClassRoutine Edit'],
                ],
                'classroutine' => fn () => $classroutine,
                'id' => fn () => $id,
                'sessions' => fn() => $sessions,
                'classes' => fn() => $classes,
                'sections' => fn() => $sections,
                'groups' => fn() => $groups,
                'teachers' => fn() => $teachers,
                'subjects' => fn() => $subjects,
                'rooms' => fn() => $rooms,
            ]
        );
    }

    public function update(ClassRoutineRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $classroutine = $this->classroutineService->find($id);

            $dataInfo = $this->classroutineService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'ClassRoutine updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classroutines', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update classroutines.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoutineController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->classroutineService->delete($id)) {
                $message = 'ClassRoutine deleted successfully';
                $this->storeAdminWorkLog($id, 'classroutines', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete ClassRoutine.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoutineController', 'destroy', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
    public function getGroupsByClass($classId)
    {
        $groups = Group::where('class_id', $classId)->get();
        return response()->json($groups);
    }
    public function getSectionsByClass($classId)
    {
        $sections = Section::where('class_id', $classId)->get();
        return response()->json($sections);
    }
    public function getSubjectsByGroup($groupId)
    {
        $subjects = Subject::where('group_id', $groupId)->get();
        return response()->json($subjects);
    }
    public function changeStatus(Request $request, $id, $status)
    {
        DB::beginTransaction();

        try {

            $dataInfo = $this->classroutineService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'ClassRoutine ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'classroutines', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "ClassRoutine.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ClassRoutineController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }