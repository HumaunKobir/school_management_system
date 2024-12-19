<?php
namespace App\Services;
use App\Models\Donation;

class DonationService
{
    protected $DonationModel;

    public function __construct(Donation $donationModel)
    {
        $this->donationModel = $donationModel;
    }

    public function list()
    {
        return  $this->donationModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->donationModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->donationModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->donationModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->donationModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->donationModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->donationModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->donationModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->donationModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

