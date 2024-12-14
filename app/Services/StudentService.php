<?php

namespace App\Services;

use App\Models\Student;

class StudentService
{
    protected $studentModel;

    public function __construct(Student $studentModel)
    {
        $this->studentModel = $studentModel;
    }

    public function list()
    {
        return $this->studentModel->whereNull('deleted_at');
    }

    public function all()
    {
        return $this->studentModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return $this->studentModel->find($id);
    }

    public function create(array $data)
    {
        return $this->studentModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo = $this->studentModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo = $this->studentModel->find($id);

        if(!empty($dataInfo)){

            $dataInfo->deleted_at=date('Y-m-d H:i:s');

            $dataInfo->status='Deleted';

            return ($dataInfo->save());
        }
            return false;
    }

    public function changeStatus($request)
    {
        $dataInfo = $this->studentModel->findOrFail($request->id);

        $dataInfo->update($request->all());

        return $dataInfo;
    }


    public function activeList()
    {
        return $this->studentModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}
