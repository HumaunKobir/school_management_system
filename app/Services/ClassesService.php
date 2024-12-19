<?php
namespace App\Services;
use App\Models\Classes;

class ClassesService
{
    protected $ClassesModel;

    public function __construct(Classes $classesModel)
    {
        $this->classesModel = $classesModel;
    }

    public function list()
    {
        return  $this->classesModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->classesModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->classesModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->classesModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->classesModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->classesModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->classesModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->classesModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->classesModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

