<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScholarshipRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ScholarshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ScholarshipController extends Controller
{
    use SystemTrait;

    protected $scholarshipService;

    public function __construct(ScholarshipService $scholarshipService)
    {
        $this->scholarshipService = $scholarshipService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Scholarship/Index',
            [
                'pageTitle' => fn () => 'Scholarship List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Scholarship Manage'],
                    ['link' => route('backend.scholarship.index'), 'title' => 'Scholarship List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->scholarshipService->list();

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
            'Backend/Scholarship/Form',
            [
                'pageTitle' => fn () => 'Scholarship Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Scholarship Manage'],
                    ['link' => route('backend.scholarship.create'), 'title' => 'Scholarship Create'],
                ],
            ]
        );
    }


    public function store(ScholarshipRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'scholarships');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'scholarships');


            $dataInfo = $this->scholarshipService->create($data);

            if ($dataInfo) {
                $message = 'Scholarship created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'scholarships', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Scholarship.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'ScholarshipController', 'store', substr($err->getMessage(), 0, 1000));
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
        $scholarship = $this->scholarshipService->find($id);

        return Inertia::render(
            'Backend/Scholarship/Form',
            [
                'pageTitle' => fn () => 'Scholarship Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Scholarship Manage'],
                    ['link' => route('backend.scholarship.edit', $id), 'title' => 'Scholarship Edit'],
                ],
                'scholarship' => fn () => $scholarship,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(ScholarshipRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $scholarship = $this->scholarshipService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'scholarships');
                $path = strstr($scholarship->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($scholarship->image ?? '', 'scholarships');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'scholarships/');
                $path = strstr($scholarship->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($scholarship->file ?? '', 'scholarships/');
            }

            $dataInfo = $this->scholarshipService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Scholarship updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'scholarships', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update scholarships.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ScholarshipController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->scholarshipService->delete($id)) {
                $message = 'Scholarship deleted successfully';
                $this->storeAdminWorkLog($id, 'scholarships', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Scholarship.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ScholarshipController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->scholarshipService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Scholarship ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'scholarships', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Scholarship.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ScholarshipController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }