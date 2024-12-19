<?php
namespace App\Services;
use App\Models\Exam;

class ExamService
{
    protected $ExamModel;

    public function __construct(Exam $examModel)
    {
        $this->examModel = $examModel;
    }

    public function list()
    {
        return  $this->examModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->examModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->examModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->examModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->examModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->examModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->examModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->examModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->examModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

