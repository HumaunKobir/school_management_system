<?php
namespace App\Services;
use App\Models\Subject;

class SubjectService
{
    protected $SubjectModel;

    public function __construct(Subject $subjectModel)
    {
        $this->subjectModel = $subjectModel;
    }

    public function list()
    {
        return  $this->subjectModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->subjectModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->subjectModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->subjectModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->subjectModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->subjectModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->subjectModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->subjectModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->subjectModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

