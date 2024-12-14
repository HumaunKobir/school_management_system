<?php

namespace App\Services;

use App\Models\Classes;

class ClassService
{
    protected $classModel;

    public function __construct(Classes $classModel)
    {
        $this->classModel = $classModel;
    }

    public function list()
    {
        return $this->classModel->whereNull('deleted_at');
    }

    public function all()
    {
        return $this->classModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return $this->classModel->find($id);
    }

    public function create(array $data)
    {
        return $this->classModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo = $this->classModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo = $this->classModel->find($id);

        if(!empty($dataInfo)){

            $dataInfo->deleted_at=date('Y-m-d H:i:s');

            $dataInfo->status='Deleted';

            return ($dataInfo->save());
        }
            return false;
    }

    public function changeStatus($request)
    {
        $dataInfo = $this->classModel->findOrFail($request->id);

        $dataInfo->update($request->all());

        return $dataInfo;
    }
    public function AdminExists($userName)
    {
        return $this->classModel->whereNull('deleted_at')
                    ->where(function($q) use($userName){
                        $q->where('email',strtolower($userName))
                        ->orWhere('phone',$userName);
                    })->first();

    }


    public function activeList()
    {
        return $this->classModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}
