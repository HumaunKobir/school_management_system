<?php
namespace App\Services;
use App\Models\Group;

class GroupService
{
    protected $GroupModel;

    public function __construct(Group $groupModel)
    {
        $this->groupModel = $groupModel;
    }

    public function list()
    {
        return  $this->groupModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->groupModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->groupModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->groupModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->groupModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->groupModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->groupModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->groupModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->groupModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

