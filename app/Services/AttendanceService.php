<?php
namespace App\Services;
use App\Models\Attendance;

class AttendanceService
{
    protected $AttendanceModel;

    public function __construct(Attendance $attendanceModel)
    {
        $this->attendanceModel = $attendanceModel;
    }

    public function list()
    {
        return  $this->attendanceModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->attendanceModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->attendanceModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->attendanceModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->attendanceModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->attendanceModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->attendanceModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->attendanceModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->attendanceModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

