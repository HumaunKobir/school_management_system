<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassNoteRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ClassNoteService;
use App\Services\SessionService;
use App\Services\ClassesService;
use App\Services\SectionService;
use App\Services\GroupService;
use App\Services\TeacherService;
use App\Services\SubjectService;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ClassNoteController extends Controller
{
    use SystemTrait;

    protected $classnoteService,$sessionService, $classesService,$sectionService,$groupService,$subjectService,$teacherService;

    public function __construct(ClassNoteService $classnoteService,SessionService $sessionService,ClassesService $classesService,SectionService $sectionService,GroupService $groupService,SubjectService $subjectService,TeacherService $teacherService)
    {
        $this->classnoteService = $classnoteService;
        $this->sessionService = $sessionService;
        $this->classesService = $classesService;
        $this->sectionService = $sectionService;
        $this->groupService = $groupService;
        $this->teacherService = $teacherService;
        $this->subjectService = $subjectService;
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
            $customData->session_id = $data->session->session_year ?? '';  
            $customData->class_id = $data->class->name ?? '';
            $customData->section_id = $data->section->name ?? '';
            $customData->group_id = $data->group->name ?? '';
            $customData->teacher_id = $data->teacher->name ?? '';
            $customData->subject_id = $data->subject->name ?? '';
            $customData->date = $data->date ?? '';
            $customData->note_photo = '<img src="' . $data->note_photo . '" height="50" width="50"/>'; 
            $customData->note_pdf = "<a href='" . $data->note_pdf . "' target='_blank'>View File</a>";
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.classnote.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.classnote.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.classnote.destroy', $data->id),
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
            ['fieldName' => 'date', 'class' => 'text-center'],
            ['fieldName' => 'note_photo', 'class' => 'text-center'],
            ['fieldName' => 'note_pdf', 'class' => 'text-center'],
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
            'Date',
            'Note Photo',
            'Note PDF',
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
        return Inertia::render(
            'Backend/ClassNote/Form',
            [
                'pageTitle' => fn () => 'ClassNote Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'ClassNote Manage'],
                    ['link' => route('backend.classnote.create'), 'title' => 'ClassNote Create'],
                ],
                'sessions' => fn() => $sessions,
                'classes' => fn() => $classes,
                'sections' => fn() => $sections,
                'groups' => fn() => $groups,
                'teachers' => fn() => $teachers,
                'subjects' => fn() => $subjects,
            ]
        );
    }


    public function store(ClassNoteRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('note_photo'))
                $data['note_photo'] = $this->imageUpload($request->file('note_photo'), 'notePhoto');

            if ($request->hasFile('note_pdf'))
                $data['note_pdf'] = $this->fileUpload($request->file('note_pdf'), 'notePdf');


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
        $classnote = $this->classnoteService->find($id);
        $sessions = $this->sessionService->activeList();
        $classes = $this->classesService->activeList();
        $sections = $this->sectionService->activeList();
        $groups = $this->groupService->activeList();
        $teachers = $this->teacherService->activeList();
        $subjects = $this->subjectService->activeList();

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
                'sessions' => fn() => $sessions,
                'classes' => fn() => $classes,
                'sections' => fn() => $sections,
                'groups' => fn() => $groups,
                'teachers' => fn() => $teachers,
                'subjects' => fn() => $subjects,
            ]
        );
    }

    public function update(ClassNoteRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $classnote = $this->classnoteService->find($id);

            if ($request->hasFile('note_photo')) {
                $data['note_photo'] = $this->imageUpload($request->note_photo, 'notePhoto');
            } else {
                $data['note_photo'] = strstr($classnote->note_photo ?? '', 'notePhoto/');
            }
            if ($request->hasFile('note_pdf')) {
                $data['note_pdf'] = $this->fileUpload($request->note_pdf, 'notePdf');
            } else {
                $data['note_pdf'] = strstr($classnote->note_pdf ?? '', 'notePdf/');
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