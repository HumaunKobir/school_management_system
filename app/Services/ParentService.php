<?php
namespace App\Services;
use App\Models\Parents;

class ParentService
{
    protected $ParentModel;

    public function __construct(Parents $parentModel)
    {
        $this->parentModel = $parentModel;
    }

    public function list()
    {
        return  $this->parentModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->parentModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->parentModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->parentModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->parentModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->parentModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->parentModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->parentModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->parentModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

