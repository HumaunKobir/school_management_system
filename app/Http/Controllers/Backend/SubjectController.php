<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use Illuminate\Support\Facades\DB;
use App\Services\SubjectService;
use App\Services\ClassesService;
use App\Services\GroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class SubjectController extends Controller
{
    use SystemTrait;

    protected $subjectService,$classesService,$groupService;

    public function __construct(SubjectService $subjectService ,ClassesService $classesService,GroupService $groupService)
    {
        $this->subjectService = $subjectService;
        $this->classesService = $classesService;
        $this->groupService = $groupService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Subject/Index',
            [
                'pageTitle' => fn () => 'Subject List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Subject Manage'],
                    ['link' => route('backend.subject.index'), 'title' => 'Subject List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->subjectService->list();

        if (request()->filled('phone'))
            $query->where('phone', 'like', request()->phone . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->class_id = $data->class->name;
            $customData->group_id = $data->group->name;
            $customData->name = $data->name;
            $customData->subject_code = $data->subject_code;
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.subject.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.subject.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.subject.destroy', $data->id),
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
            ['fieldName' => 'group_id', 'class' => 'text-center'],
            ['fieldName' => 'name', 'class' => 'text-center'],
            ['fieldName' => 'subject_code', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Class',
            'Group',
            'Name',
            'Subject Code',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        $classes = $this->classesService->activeList();
        $groups = $this->groupService->activeList();
        return Inertia::render(
            'Backend/Subject/Form',
            [
                'pageTitle' => fn () => 'Subject Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Subject Manage'],
                    ['link' => route('backend.subject.create'), 'title' => 'Subject Create'],
                ],
                'classes' => fn() => $classes,
                'groups' => fn() => $groups,
            ]
        );
    }


    public function store(SubjectRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'subjects');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'subjects');


            $dataInfo = $this->subjectService->create($data);

            if ($dataInfo) {
                $message = 'Subject created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'subjects', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Subject.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'SubjectController', 'store', substr($err->getMessage(), 0, 1000));
           
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
        $subject = $this->subjectService->find($id);
        $classes = $this->classesService->activeList();
        $groups = $this->groupService->activeList();

        return Inertia::render(
            'Backend/Subject/Form',
            [
                'pageTitle' => fn () => 'Subject Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Subject Manage'],
                    ['link' => route('backend.subject.edit', $id), 'title' => 'Subject Edit'],
                ],
                'subject' => fn () => $subject,
                'id' => fn () => $id,
                'classes' => fn() => $classes,
                'groups' => fn() => $groups,
            ]
        );
    }

    public function update(SubjectRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $subject = $this->subjectService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'subjects');
                $path = strstr($subject->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($subject->image ?? '', 'subjects');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'subjects/');
                $path = strstr($subject->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($subject->file ?? '', 'subjects/');
            }

            $dataInfo = $this->subjectService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Subject updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'subjects', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update subjects.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SubjectController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->subjectService->delete($id)) {
                $message = 'Subject deleted successfully';
                $this->storeAdminWorkLog($id, 'subjects', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Subject.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SubjectController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->subjectService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Subject ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'subjects', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Subject.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SubjectController', 'changeStatus', substr($err->getMessage(), 0, 1000));
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
}