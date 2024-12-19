<?php
namespace App\Services;
use App\Models\ClassRoutine;

class ClassRoutineService
{
    protected $ClassRoutineModel;

    public function __construct(ClassRoutine $classroutineModel)
    {
        $this->classroutineModel = $classroutineModel;
    }

    public function list()
    {
        return  $this->classroutineModel->with('class','group','section','subject','classRoom')->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->classroutineModel->with('class','group','section','subject','classRoom')->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->classroutineModel->with('class','group','section','subject','classRoom')->find($id);
    }

    public function create(array $data)
    {
        return  $this->classroutineModel->with('class','group','section','subject','classRoom')->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->classroutineModel->with('class','group','section','subject','classRoom')->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->classroutineModel->with('class','group','section','subject','classRoom')->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->classroutineModel->with('class','group','section','subject','classRoom')->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->classroutineModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->classroutineModel->with('class','group','section','subject','classRoom')->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

