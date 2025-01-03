<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use Illuminate\Support\Facades\DB;
use App\Services\AttendanceService;
use App\Services\SessionService;
use App\Services\ClassRoutineService;
use App\Services\TeacherService;
use App\Services\SubjectService;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class AttendanceController extends Controller
{
    use SystemTrait;

    protected $attendanceService,$sessionService,$classRoutineService, $teacherService,$subjectService;

    public function __construct(AttendanceService $attendanceService, SessionService $sessionService, ClassRoutineService $classRoutineService, TeacherService $teacherService, SubjectService $subjectService)
    {
        $this->attendanceService = $attendanceService;
        $this->sessionService = $sessionService;
        $this->classRoutineService = $classRoutineService;
        $this->teacherService = $teacherService;
        $this->subjectService = $subjectService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Attendance/Index',
            [
                'pageTitle' => fn () => 'Attendance List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Attendance Manage'],
                    ['link' => route('backend.attendance.index'), 'title' => 'Attendance List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->attendanceService->list();
        
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
        $sessions = $this->sessionService->activeList();
        return Inertia::render(
            'Backend/Attendance/Form',
            [
                'pageTitle' => fn () => 'Attendance',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Attendance Manage'],
                    ['link' => route('backend.attendance.create'), 'title' => 'Attendance'],
                ],
                'sessions' => fn() => $sessions,
            ]
        );
    }


    public function store(AttendanceRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'attendances');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'attendances');


            $dataInfo = $this->attendanceService->create($data);

            if ($dataInfo) {
                $message = 'Attendance created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'attendances', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Attendance.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'AttendanceController', 'store', substr($err->getMessage(), 0, 1000));
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
        $attendance = $this->attendanceService->find($id);

        return Inertia::render(
            'Backend/Attendance/Form',
            [
                'pageTitle' => fn () => 'Attendance Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Attendance Manage'],
                    ['link' => route('backend.attendance.edit', $id), 'title' => 'Attendance Edit'],
                ],
                'attendance' => fn () => $attendance,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(AttendanceRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $attendance = $this->attendanceService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'attendances');
                $path = strstr($attendance->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($attendance->image ?? '', 'attendances');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'attendances/');
                $path = strstr($attendance->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($attendance->file ?? '', 'attendances/');
            }

            $dataInfo = $this->attendanceService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Attendance updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'attendances', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update attendances.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'AttendanceController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->attendanceService->delete($id)) {
                $message = 'Attendance deleted successfully';
                $this->storeAdminWorkLog($id, 'attendances', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Attendance.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'AttendanceController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->attendanceService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Attendance ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'attendances', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Attendance.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'AttendanceController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
    public function show()
    {
        $classRoutines = $this->classRoutineService->activeList();
        $teachers = $this->teacherService->activeList();
        $sessions = $this->sessionService->activeList();
        return Inertia::render(
            'Backend/Attendance/ClassSection',
            [
                'pageTitle' => fn () => 'Attendance',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Attendance Manage'],
                ],
                'classRoutines' => fn() => $classRoutines,
                'teachers' => fn() => $teachers,
                'sessions' => fn() => $sessions,
            ]
        );
    }
    public function getTeachersBySession($sessionId)
    {
        $teachers = $this->classRoutineService->getTeachersBySession($sessionId);
        return response()->json($teachers);
    }

    public function getClassesByTeacher($sessionId, $teacherId)
    {
        $classes = $this->classRoutineService->getClassesByTeacher($sessionId, $teacherId);
        return response()->json($classes);
    }

    public function getSectionsByClass($classId)
    {
        $sections = $this->classRoutineService->getSectionsByClass($classId);
        return response()->json($sections);
    }
    public function getSubjectsBySection($sectionId)
    {
        $subjects = $this->classRoutineService->getSubjectsBySection($sectionId);
        return response()->json($subjects);
    }
    public function filterStudents($sessionId, $classId, $sectionId)
    {
        $students = Student::with('class','section')->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();

        return response()->json($students);
    }

}