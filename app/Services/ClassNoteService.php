<?php
namespace App\Services;
use App\Models\ClassNote;

class ClassNoteService
{
    protected $ClassNoteModel;

    public function __construct(ClassNote $classnoteModel)
    {
        $this->classnoteModel = $classnoteModel;
    }

    public function list()
    {
        return  $this->classnoteModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->classnoteModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->classnoteModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->classnoteModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->classnoteModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->classnoteModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->classnoteModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->classnoteModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->classnoteModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

