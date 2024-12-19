<?php
namespace App\Services;
use App\Models\Section;

class SectionService
{
    protected $SectionModel;

    public function __construct(Section $sectionModel)
    {
        $this->sectionModel = $sectionModel;
    }

    public function list()
    {
        return  $this->sectionModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->sectionModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->sectionModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->sectionModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->sectionModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->sectionModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->sectionModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->sectionModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->sectionModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

