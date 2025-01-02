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
        return  $this->classroutineModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->classroutineModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->classroutineModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->classroutineModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->classroutineModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->classroutineModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->classroutineModel->findOrFail($id);
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
        return  $this->classroutineModel->with('session','class','section')->whereNull('deleted_at')->where('status', 'Active')->get();
    }
    public function getTeachersBySession($sessionId)
    {
        // Query to retrieve teachers based on session ID
        return ClassRoutine::where('session_id', $sessionId)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->unique('id')
            ->values();
    }

    public function getClassesByTeacher($sessionId, $teacherId)
    {
        // Query to retrieve classes based on session ID and teacher ID
        return ClassRoutine::where('session_id', $sessionId)
            ->where('teacher_id', $teacherId)
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id')
            ->values();
    }

    public function getSectionsByClass($classId)
    {
        // Query to retrieve sections based on class ID
        return ClassRoutine::where('class_id', $classId)
            ->with('section')
            ->get()
            ->pluck('section')
            ->unique('id')
            ->values();
    }
    public function getSubjectsBySection($sectionId)
    {
        // Query to retrieve sections based on class ID
        return ClassRoutine::where('section_id', $sectionId)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->values();
    }
}

