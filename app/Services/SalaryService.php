<?php
namespace App\Services;
use App\Models\Salary;

class SalaryService
{
    protected $SalaryModel;

    public function __construct(Salary $salaryModel)
    {
        $this->salaryModel = $salaryModel;
    }

    public function list()
    {
        return  $this->salaryModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->salaryModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->salaryModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->salaryModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->salaryModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->salaryModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->salaryModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->salaryModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->salaryModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

