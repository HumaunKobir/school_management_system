<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use Illuminate\Support\Facades\DB;
use App\Services\StudentService;
use App\Services\SessionService;
use App\Services\ClassesService;
use App\Services\SectionService;
use App\Services\GroupService;
use App\Models\Group;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class StudentController extends Controller
{
    use SystemTrait;

    protected $studentService,$classesService,$sectionService,$groupService,$sessionService;

    public function __construct(StudentService $studentService, ClassesService $classesService,SectionService $sectionService,GroupService $groupService,SessionService $sessionService)
    {
        $this->studentService = $studentService;
        $this->sessionService = $sessionService;
        $this->classesService = $classesService;
        $this->sectionService = $sectionService;
        $this->groupService = $groupService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Student/Index',
            [
                'pageTitle' => fn () => 'Student List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Student Manage'],
                    ['link' => route('backend.student.index'), 'title' => 'Student List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->studentService->list();

        if (request()->filled('phone'))
            $query->where('phone', 'like', request()->phone . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->student_id = $data->student_id;
            $customData->name = $data->name;
            $customData->father_name = $data->father_name;
            $customData->mother_name = $data->mother_name;
            $customData->phone = $data->phone;
            $customData->email = $data->email;
            $customData->address = $data->address;
            $customData->date_of_birth = $data->date_of_birth;
            $customData->admission_date = $data->admission_date;
            $customData->session_id = $data->session->session_year;
            $customData->class_id = $data->class->name;
            $customData->section_id = $data->section->name;
            $customData->group_id = $data->group->name;
            $customData->photo = '<img src="' . $data->photo . '" height="50" width="50"/>';
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.student.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.student.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.student.destroy', $data->id),
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
            ['fieldName' => 'student_id', 'class' => 'text-center'],
            ['fieldName' => 'name', 'class' => 'text-center'],
            ['fieldName' => 'father_name', 'class' => 'text-center'],
            ['fieldName' => 'mother_name', 'class' => 'text-center'],
            ['fieldName' => 'phone', 'class' => 'text-center'],
            ['fieldName' => 'email', 'class' => 'text-center'],
            ['fieldName' => 'address', 'class' => 'text-center'],
            ['fieldName' => 'date_of_birth', 'class' => 'text-center'],
            ['fieldName' => 'admission_date', 'class' => 'text-center'],
            ['fieldName' => 'photo', 'class' => 'text-center'],
            ['fieldName' => 'session_id', 'class' => 'text-center'],
            ['fieldName' => 'class_id', 'class' => 'text-center'],
            ['fieldName' => 'section_id', 'class' => 'text-center'],
            ['fieldName' => 'group_id', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Student Id',
            'Name',
            'Father Name',
            'Mother Name',
            'Phone',
            'Email',
            'Address',
            'Date Of Birth',
            'Admission Date',
            'Photo',
            'Session Year',
            'Class Name',
            'Section Name',
            'Group Name',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        $classes = $this->classesService->activeList();
        $sessions = $this->sessionService->activeList();
        return Inertia::render(
            'Backend/Student/Form',
            [
                'pageTitle' => fn () => 'Student Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Student Manage'],
                    ['link' => route('backend.student.create'), 'title' => 'Student Create'],
                ],
                'sessions' => fn() => $sessions,
                'classes' => fn() => $classes,
            ]
        );
    }


    public function store(StudentRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();
            $currentDate = now();
            $classCode = $request->class_id;
            $year = $currentDate->format('Y');

            $lastStudent = DB::table('students')
                ->where('class_id', $classCode)
                ->orderBy('id', 'desc')
                ->first();

            if ($lastStudent) {
                $lastNumber = (int) substr($lastStudent->student_id, -3);
                $uniqueNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $uniqueNumber = '001';
            }

            $data['student_id'] = $year . '0' . $classCode . $uniqueNumber;
            if ($request->hasFile('photo'))
                $data['photo'] = $this->imageUpload($request->file('photo'), 'students');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'students');

            $section = Section::find($request->section_id);

            if ($section->students()->count() >= $section->total_sit) {
                return back()->withErrors(['section_id' => 'The selected section is full.']);
            }
            $dataInfo = $this->studentService->create($data);

            if ($dataInfo) {
                $message = 'Student created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'students', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Student.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'StudentController', 'store', substr($err->getMessage(), 0, 1000));
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
        $student = $this->studentService->find($id);
        $sessions = $this->sessionService->activeList();
        $classes = $this->classesService->activeList();
        $sections = $this->sectionService->findByClassId($student->class_id);
        $groups = $this->groupService->findByClassId($student->class_id);

        return Inertia::render(
            'Backend/Student/Form',
            [
                'pageTitle' => fn () => 'Student Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Student Manage'],
                    ['link' => route('backend.student.edit', $id), 'title' => 'Student Edit'],
                ],
                'student' => fn () => $student,
                'id' => fn () => $id,
                'sessions' => fn () => $sessions,
                'classes' => fn () => $classes,
                'sections' => fn() => $sections,
                'groups' => fn() => $groups,
            ]
        );
    }

    public function update(StudentRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $student = $this->studentService->find($id);

            if ($request->hasFile('photo')) {
                $data['photo'] = $this->imageUpload($request->file('photo'), 'students');
                $path = strstr($student->photo, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['photo'] = strstr($student->photo ?? '', 'students');
            }


            $dataInfo = $this->studentService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Student updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'students', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update students.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'StudentController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->studentService->delete($id)) {
                $message = 'Student deleted successfully';
                $this->storeAdminWorkLog($id, 'students', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Student.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'StudentController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->studentService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Student ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'students', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Student.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'StudentController', 'changeStatus', substr($err->getMessage(), 0, 1000));
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
        $sections = Section::where('class_id', $classId)->withCount('students')->get();
        return response()->json($sections->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'total_sit' => $section->total_sit,
                'current_students' => $section->students_count,
            ];
        }));
    }
}