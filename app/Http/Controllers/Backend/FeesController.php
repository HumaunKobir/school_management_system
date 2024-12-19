<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeesRequest;
use Illuminate\Support\Facades\DB;
use App\Services\FeesService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class FeesController extends Controller
{
    use SystemTrait;

    protected $feesService;

    public function __construct(FeesService $feesService)
    {
        $this->feesService = $feesService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Fees/Index',
            [
                'pageTitle' => fn () => 'Fees List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Fees Manage'],
                    ['link' => route('backend.fees.index'), 'title' => 'Fees List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->feesService->list();

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
            'Backend/Fees/Form',
            [
                'pageTitle' => fn () => 'Fees Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Fees Manage'],
                    ['link' => route('backend.fees.create'), 'title' => 'Fees Create'],
                ],
            ]
        );
    }


    public function store(FeesRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();

            if ($request->hasFile('image'))
                $data['image'] = $this->imageUpload($request->file('image'), 'fees');

            if ($request->hasFile('file'))
                $data['file'] = $this->fileUpload($request->file('file'), 'fees');


            $dataInfo = $this->feesService->create($data);

            if ($dataInfo) {
                $message = 'Fees created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'fees', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Fees.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'FeesController', 'store', substr($err->getMessage(), 0, 1000));
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
        $fees = $this->feesService->find($id);

        return Inertia::render(
            'Backend/Fees/Form',
            [
                'pageTitle' => fn () => 'Fees Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Fees Manage'],
                    ['link' => route('backend.fees.edit', $id), 'title' => 'Fees Edit'],
                ],
                'fees' => fn () => $fees,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(FeesRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $fees = $this->feesService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'fees');
                $path = strstr($fees->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($fees->image ?? '', 'fees');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'fees/');
                $path = strstr($fees->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($fees->file ?? '', 'fees/');
            }

            $dataInfo = $this->feesService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Fees updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'fees', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update fees.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'FeesController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->feesService->delete($id)) {
                $message = 'Fees deleted successfully';
                $this->storeAdminWorkLog($id, 'fees', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Fees.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'FeesController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->feesService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Fees ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'fees', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Fees.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'FeesController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }