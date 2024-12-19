<?php
namespace App\Services;
use App\Models\ClassRoom;

class ClassRoomService
{
    protected $ClassRoomModel;

    public function __construct(ClassRoom $classroomModel)
    {
        $this->classroomModel = $classroomModel;
    }

    public function list()
    {
        return  $this->classroomModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->classroomModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->classroomModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->classroomModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->classroomModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->classroomModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->classroomModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->classroomModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->classroomModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

