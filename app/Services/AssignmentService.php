<?php
namespace App\Services;
use App\Models\Assignment;

class AssignmentService
{
    protected $AssignmentModel;

    public function __construct(Assignment $assignmentModel)
    {
        $this->assignmentModel = $assignmentModel;
    }

    public function list()
    {
        return  $this->assignmentModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->assignmentModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->assignmentModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->assignmentModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->assignmentModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->assignmentModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->assignmentModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->assignmentModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->assignmentModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

