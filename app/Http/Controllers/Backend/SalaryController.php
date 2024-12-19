<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalaryRequest;
use Illuminate\Support\Facades\DB;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class SalaryController extends Controller
{
    use SystemTrait;

    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Salary/Index',
            [
                'pageTitle' => fn () => 'Salary List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Salary Manage'],
                    ['link' => route('backend.salary.index'), 'title' => 'Salary List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->salaryService->list();

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
            'Backend/Salary/Form',
            [
                'pageTitle' => fn () => 'Salary Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Salary Manage'],
                    ['link' => route('backend.salary.create'), 'title' => 'Salary Create'],
                ],
            ]
        );
    }


    public function store(SalaryRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'salaries');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'salaries');


            $dataInfo = $this->salaryService->create($data);

            if ($dataInfo) {
                $message = 'Salary created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'salaries', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Salary.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'SalaryController', 'store', substr($err->getMessage(), 0, 1000));
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
        $salary = $this->salaryService->find($id);

        return Inertia::render(
            'Backend/Salary/Form',
            [
                'pageTitle' => fn () => 'Salary Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Salary Manage'],
                    ['link' => route('backend.salary.edit', $id), 'title' => 'Salary Edit'],
                ],
                'salary' => fn () => $salary,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(SalaryRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $salary = $this->salaryService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'salaries');
                $path = strstr($salary->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($salary->image ?? '', 'salaries');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'salaries/');
                $path = strstr($salary->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($salary->file ?? '', 'salaries/');
            }

            $dataInfo = $this->salaryService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Salary updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'salaries', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update salaries.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SalaryController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->salaryService->delete($id)) {
                $message = 'Salary deleted successfully';
                $this->storeAdminWorkLog($id, 'salaries', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Salary.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SalaryController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->salaryService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Salary ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'salaries', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Salary.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SalaryController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }