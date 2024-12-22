<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use Illuminate\Support\Facades\DB;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class TeacherController extends Controller
{
    use SystemTrait;

    protected $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Teacher/Index',
            [
                'pageTitle' => fn () => 'Teacher List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Teacher Manage'],
                    ['link' => route('backend.teacher.index'), 'title' => 'Teacher List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->teacherService->list();

        if (request()->filled('phone'))
            $query->where('phone', 'like', request()->phone . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->teacher_id = $data->teacher_id;
            $customData->name = $data->name;
            $customData->father_name = $data->father_name;
            $customData->mother_name = $data->mother_name;
            $customData->phone = $data->phone;
            $customData->email = $data->email;
            $customData->address = $data->address;
            $customData->date_of_birth = $data->date_of_birth;
            $customData->education_level = $data->education_level;
            $customData->jonning_date = $data->jonning_date;
            $customData->file = "<a href='" . $data->file . "' target='_blank'>View File</a>";
            $customData->photo = '<img src="' . $data->photo . '" height="50" width="50"/>';
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.teacher.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.teacher.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.teacher.destroy', $data->id),
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
            ['fieldName' => 'teacher_id', 'class' => 'text-center'],
            ['fieldName' => 'name', 'class' => 'text-center'],
            ['fieldName' => 'father_name', 'class' => 'text-center'],
            ['fieldName' => 'mother_name', 'class' => 'text-center'],
            ['fieldName' => 'phone', 'class' => 'text-center'],
            ['fieldName' => 'email', 'class' => 'text-center'],
            ['fieldName' => 'address', 'class' => 'text-center'],
            ['fieldName' => 'date_of_birth', 'class' => 'text-center'],
            ['fieldName' => 'education_level', 'class' => 'text-center'],
            ['fieldName' => 'jonning_date', 'class' => 'text-center'],
            ['fieldName' => 'file', 'class' => 'text-center'],
            ['fieldName' => 'photo', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Teacher Id',
            'Name',
            'Father Name',
            'Mother Name',
            'Phone',
            'Email',
            'Address',
            'Date of Birth',
            'Education Level',
            'Joning Date',
            'CV',
            'Photo',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        return Inertia::render(
            'Backend/Teacher/Form',
            [
                'pageTitle' => fn () => 'Teacher Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Teacher Manage'],
                    ['link' => route('backend.teacher.create'), 'title' => 'Teacher Create'],
                ],
            ]
        );
    }


    public function store(TeacherRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();
            $currentDate = now();
            $year = $currentDate->format('Y');

            $lastStudent = DB::table('teachers')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastStudent) {
                $lastNumber = (int) substr($lastStudent->teacher_id, -4);
                $uniqueNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $uniqueNumber = '0001';
            }

            $data['teacher_id'] = $year . $uniqueNumber;

            if ($request->hasFile('photo'))
                $data['photo'] = $this->imageUpload($request->file('photo'), 'teachers');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'teachersCv');


            $dataInfo = $this->teacherService->create($data);

            if ($dataInfo) {
                $message = 'Teacher created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'teachers', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Teacher.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'TeacherController', 'store', substr($err->getMessage(), 0, 1000));
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
        $teacher = $this->teacherService->find($id);

        return Inertia::render(
            'Backend/Teacher/Form',
            [
                'pageTitle' => fn () => 'Teacher Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Teacher Manage'],
                    ['link' => route('backend.teacher.edit', $id), 'title' => 'Teacher Edit'],
                ],
                'teacher' => fn () => $teacher,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(TeacherRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $teacher = $this->teacherService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'teachers');
                $path = strstr($teacher->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($teacher->image ?? '', 'teachers');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'teachers/');
                $path = strstr($teacher->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($teacher->file ?? '', 'teachers/');
            }

            $dataInfo = $this->teacherService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Teacher updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'teachers', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update teachers.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'TeacherController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->teacherService->delete($id)) {
                $message = 'Teacher deleted successfully';
                $this->storeAdminWorkLog($id, 'teachers', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Teacher.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'TeacherController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->teacherService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Teacher ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'teachers', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Teacher.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'TeacherController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }