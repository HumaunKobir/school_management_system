<?php
namespace App\Services;
use App\Models\Payment;

class PaymentService
{
    protected $PaymentModel;

    public function __construct(Payment $paymentModel)
    {
        $this->paymentModel = $paymentModel;
    }

    public function list()
    {
        return  $this->paymentModel->whereNull('deleted_at');
    }

    public function all()
    {
        return  $this->paymentModel->whereNull('deleted_at')->all();
    }

    public function find($id)
    {
        return  $this->paymentModel->find($id);
    }

    public function create(array $data)
    {
        return  $this->paymentModel->create($data);
    }

    public function update(array $data, $id)
    {
        $dataInfo =  $this->paymentModel->findOrFail($id);

        $dataInfo->update($data);

        return $dataInfo;
    }

    public function delete($id)
    {
        $dataInfo =  $this->paymentModel->find($id);

        if (!empty($dataInfo)) {

            $dataInfo->deleted_at = date('Y-m-d H:i:s');

            $dataInfo->status = 'Deleted';

            return ($dataInfo->save());
        }
        return false;
    }

    public function changeStatus($id,$status)
    {
        $dataInfo =  $this->paymentModel->findOrFail($id);
        $dataInfo->status = $status;
        $dataInfo->update();

        return $dataInfo;
    }

    public function AdminExists($userName)
    {
        return  $this->paymentModel->whereNull('deleted_at')
            ->where(function ($q) use ($userName) {
                $q->where('email', strtolower($userName))
                    ->orWhere('phone', $userName);
            })->first();

    }


    public function activeList()
    {
        return  $this->paymentModel->whereNull('deleted_at')->where('status', 'Active')->get();
    }

}

