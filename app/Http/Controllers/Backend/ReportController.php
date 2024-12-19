<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use Illuminate\Support\Facades\DB;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class ReportController extends Controller
{
    use SystemTrait;

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Report/Index',
            [
                'pageTitle' => fn () => 'Report List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Report Manage'],
                    ['link' => route('backend.report.index'), 'title' => 'Report List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->reportService->list();

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
            'Backend/Report/Form',
            [
                'pageTitle' => fn () => 'Report Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Report Manage'],
                    ['link' => route('backend.report.create'), 'title' => 'Report Create'],
                ],
            ]
        );
    }


    public function store(ReportRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'reports');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'reports');


            $dataInfo = $this->reportService->create($data);

            if ($dataInfo) {
                $message = 'Report created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'reports', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Report.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'ReportController', 'store', substr($err->getMessage(), 0, 1000));
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
        $report = $this->reportService->find($id);

        return Inertia::render(
            'Backend/Report/Form',
            [
                'pageTitle' => fn () => 'Report Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Report Manage'],
                    ['link' => route('backend.report.edit', $id), 'title' => 'Report Edit'],
                ],
                'report' => fn () => $report,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(ReportRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $report = $this->reportService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'reports');
                $path = strstr($report->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($report->image ?? '', 'reports');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'reports/');
                $path = strstr($report->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($report->file ?? '', 'reports/');
            }

            $dataInfo = $this->reportService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Report updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'reports', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update reports.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ReportController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->reportService->delete($id)) {
                $message = 'Report deleted successfully';
                $this->storeAdminWorkLog($id, 'reports', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Report.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ReportController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->reportService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Report ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'reports', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Report.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'ReportController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }