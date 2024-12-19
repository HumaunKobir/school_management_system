<?php
namespace App\Services;
use App\Models\Notice;

class NoticeService
{
    protected $NoticeModel;

    public function __construct(Notice $noticeModel)
    {
        $this->noticeModel = $noticeModel;
    }

    public function list()
    {
        return  $this->noticeModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->noticeModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->noticeModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->noticeModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->noticeModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->noticeModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->noticeModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->noticeModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->noticeModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

