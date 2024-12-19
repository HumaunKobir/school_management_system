<?php
namespace App\Services;
use App\Models\Result;

class ResultService
{
    protected $ResultModel;

    public function __construct(Result $resultModel)
    {
        $this->resultModel = $resultModel;
    }

    public function list()
    {
        return  $this->resultModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->resultModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->resultModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->resultModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->resultModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->resultModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->resultModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->resultModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->resultModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}
