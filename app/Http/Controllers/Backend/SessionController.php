<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use Illuminate\Support\Facades\DB;
use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class SessionController extends Controller
{
    use SystemTrait;

    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Session/Index',
            [
                'pageTitle' => fn () => 'Session List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Session Manage'],
                    ['link' => route('backend.session.index'), 'title' => 'Session List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->sessionService->list();

        if (request()->filled('name'))
            $query->where('name', 'like', request()->name . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->session_year = $data->session_year;
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.session.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.session.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.session.destroy', $data->id),
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
            ['fieldName' => 'session_year', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Session Year',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        return Inertia::render(
            'Backend/Session/Form',
            [
                'pageTitle' => fn () => 'Session Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Session Manage'],
                    ['link' => route('backend.session.create'), 'title' => 'Session Create'],
                ],
            ]
        );
    }


    public function store(SessionRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();


            $dataInfo = $this->sessionService->create($data);

            if ($dataInfo) {
                $message = 'Session created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'sessions', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Session.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'SessionController', 'store', substr($err->getMessage(), 0, 1000));
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
        $session = $this->sessionService->find($id);

        return Inertia::render(
            'Backend/Session/Form',
            [
                'pageTitle' => fn () => 'Session Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Session Manage'],
                    ['link' => route('backend.session.edit', $id), 'title' => 'Session Edit'],
                ],
                'session' => fn () => $session,
                'id' => fn () => $id,
            ]
        );
    }

    public function update(SessionRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $session = $this->sessionService->find($id);

            if ($request->hasFile('image')) {
                $data['image'] = $this->imageUpload($request->file('image'), 'sessions');
                $path = strstr($session->image, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['image'] = strstr($session->image ?? '', 'sessions');
            }

            if ($request->hasFile('file')) {
                $data['file'] = $this->fileUpload($request->file('file'), 'sessions/');
                $path = strstr($session->file, 'storage/');
                if (file_exists($path)) {
                    unlink($path);
                }
            } else {

                $data['file'] = strstr($session->file ?? '', 'sessions/');
            }

            $dataInfo = $this->sessionService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Session updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'sessions', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update sessions.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SessionController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->sessionService->delete($id)) {
                $message = 'Session deleted successfully';
                $this->storeAdminWorkLog($id, 'sessions', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Session.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SessionController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->sessionService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Session ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'sessions', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Session.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SessionController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }