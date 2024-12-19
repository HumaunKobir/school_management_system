<?php
namespace App\Services;
use App\Models\Scholarship;

class ScholarshipService
{
    protected $ScholarshipModel;

    public function __construct(Scholarship $scholarshipModel)
    {
        $this->scholarshipModel = $scholarshipModel;
    }

    public function list()
    {
        return  $this->scholarshipModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->scholarshipModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->scholarshipModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->scholarshipModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->scholarshipModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->scholarshipModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->scholarshipModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->scholarshipModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->scholarshipModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

