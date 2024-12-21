<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SectionRequest;
use Illuminate\Support\Facades\DB;
use App\Services\SectionService;
use App\Services\ClassesService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use App\Traits\SystemTrait;
use Exception;

class SectionController extends Controller
{
    use SystemTrait;

    protected $sectionService,$classesService;

    public function __construct(SectionService $sectionService, ClassesService $classesService)
    {
        $this->sectionService = $sectionService;
        $this->classesService = $classesService;
    }



    public function index()
    {
        return Inertia::render(
            'Backend/Section/Index',
            [
                'pageTitle' => fn () => 'Section List',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Section Manage'],
                    ['link' => route('backend.section.index'), 'title' => 'Section List'],
                ],
                'tableHeaders' => fn () => $this->getTableHeaders(),
                'dataFields' => fn () => $this->dataFields(),
                'datas' => fn () => $this->getDatas(),
            ]
        );
    }

    private function getDatas()
    {
        $query = $this->sectionService->list();

        if (request()->filled('name'))
            $query->where('name', 'like', request()->name . '%');


        $datas = $query->paginate(request()->numOfData ?? 10)->withQueryString();

        $formatedDatas = $datas->map(function ($data, $index) {
            $customData = new \stdClass();
            $customData->index = $index + 1;
            $customData->class_id = $data->class->class_id;
            $customData->name = $data->name;
            $customData->total_sit = $data->total_sit;
            $customData->status = getStatusText($data->status);

            $customData->hasLink = true;
            $customData->links = [
                [
                    'linkClass' => 'semi-bold text-white statusChange ' . (($data->status == 'Active') ? "bg-gray-500" : "bg-green-500"),
                    'link' => route('backend.section.status.change', ['id' => $data->id, 'status' => $data->status == 'Active' ? 'Inactive' : 'Active']),
                    'linkLabel' => getLinkLabel((($data->status == 'Active') ? "Inactive" : "Active"), null, null)
                ],
                [
                    'linkClass' => 'bg-yellow-400 text-black semi-bold',
                    'link' => route('backend.section.edit',  $data->id),
                    'linkLabel' => getLinkLabel('Edit', null, null)
                ],
                [
                    'linkClass' => 'deleteButton bg-red-500 text-white semi-bold',
                    'link' => route('backend.section.destroy', $data->id),
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
            ['fieldName' => 'name', 'class' => 'text-center'],
            ['fieldName' => 'total_sit', 'class' => 'text-center'],
            ['fieldName' => 'status', 'class' => 'text-center'],
        ];
    }
    private function getTableHeaders()
    {
        return [
            'Sl/No',
            'Class Name',
            'Name',
            'total_sit',
            'Status',
            'Action',
        ];
    }

    public function create()
    {
        $classes = $this->classesService->activeList();
        return Inertia::render(
            'Backend/Section/Form',
            [
                'pageTitle' => fn () => 'Section Create',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Section Manage'],
                    ['link' => route('backend.section.create'), 'title' => 'Section Create'],
                ],
                'classes' => fn() => $classes,
            ]
        );
    }


    public function store(SectionRequest $request)
    {

        DB::beginTransaction();
        try {

            $data = $request->validated();


            $dataInfo = $this->sectionService->create($data);

            if ($dataInfo) {
                $message = 'Section created successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'sections', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To create Section.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            //   dd($err);
            DB::rollBack();
            $this->storeSystemError('Backend', 'SectionController', 'store', substr($err->getMessage(), 0, 1000));
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
        $section = $this->sectionService->find($id);
        $classes = $this->classesService->activeList();

        return Inertia::render(
            'Backend/Section/Form',
            [
                'pageTitle' => fn () => 'Section Edit',
                'breadcrumbs' => fn () => [
                    ['link' => null, 'title' => 'Section Manage'],
                    ['link' => route('backend.section.edit', $id), 'title' => 'Section Edit'],
                ],
                'section' => fn () => $section,
                'id' => fn () => $id,
                'classes' => fn() => $classes,
            ]
        );
    }

    public function update(SectionRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $data = $request->validated();
            $section = $this->sectionService->find($id);

           

            $dataInfo = $this->sectionService->update($data, $id);

            if ($dataInfo->save()) {
                $message = 'Section updated successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'sections', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To update sections.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SectionController', 'update', substr($err->getMessage(), 0, 1000));
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

            if ($this->sectionService->delete($id)) {
                $message = 'Section deleted successfully';
                $this->storeAdminWorkLog($id, 'sections', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To Delete Section.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SectionController', 'destroy', substr($err->getMessage(), 0, 1000));
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

            $dataInfo = $this->sectionService->changeStatus($id, $status);

            if ($dataInfo->wasChanged()) {
                $message = 'Section ' . request()->status . ' Successfully';
                $this->storeAdminWorkLog($dataInfo->id, 'sections', $message);

                DB::commit();

                return redirect()
                    ->back()
                    ->with('successMessage', $message);
            } else {
                DB::rollBack();

                $message = "Failed To " . request()->status . "Section.";
                return redirect()
                    ->back()
                    ->with('errorMessage', $message);
            }
        } catch (Exception $err) {
            DB::rollBack();
            $this->storeSystemError('Backend', 'SectionController', 'changeStatus', substr($err->getMessage(), 0, 1000));
            DB::commit();
            $message = "Server Errors Occur. Please Try Again.";
            return redirect()
                ->back()
                ->with('errorMessage', $message);
        }
    }
        }