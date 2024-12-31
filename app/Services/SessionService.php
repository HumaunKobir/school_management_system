<?php
namespace App\Services;
use App\Models\Session;

class SessionService
{
    protected $SessionModel;

    public function __construct(Session $sessionModel)
    {
        $this->sessionModel = $sessionModel;
    }

    public function list()
    {
        return  $this->sessionModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->sessionModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->sessionModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->sessionModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->sessionModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->sessionModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->sessionModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->sessionModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->sessionModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

