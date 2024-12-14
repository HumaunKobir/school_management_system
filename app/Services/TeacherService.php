<?php
namespace App\Services;
use App\Models\Teacher;

class TeacherService
{
    protected $TeacherModel;

    public function __construct(Teacher $teacherModel)
    {
        $this->teacherModel = $teacherModel;
    }

    public function list()
    {
        return  $this->teacherModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->teacherModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->teacherModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->teacherModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->teacherModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->teacherModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->teacherModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->teacherModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->teacherModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

