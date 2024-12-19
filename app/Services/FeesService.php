<?php
namespace App\Services;
use App\Models\Fees;

class FeesService
{
    protected $FeesModel;

    public function __construct(Fees $feesModel)
    {
        $this->feesModel = $feesModel;
    }

    public function list()
    {
        return  $this->feesModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->feesModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->feesModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->feesModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->feesModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->feesModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->feesModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->feesModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->feesModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

