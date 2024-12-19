<?php
namespace App\Services;
use App\Models\Event;

class EventService
{
    protected $EventModel;

    public function __construct(Event $eventModel)
    {
        $this->eventModel = $eventModel;
    }

    public function list()
    {
        return  $this->eventModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->eventModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->eventModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->eventModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->eventModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->eventModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->eventModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->eventModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->eventModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

