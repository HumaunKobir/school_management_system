<?php
namespace App\Services;
use App\Models\Report;

class ReportService
{
    protected $ReportModel;

    public function __construct(Report $reportModel)
    {
        $this->reportModel = $reportModel;
    }

    public function list()
    {
        return  $this->reportModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->reportModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->reportModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->reportModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->reportModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->reportModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->reportModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->reportModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->reportModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

