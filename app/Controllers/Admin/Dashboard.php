<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel; // Add this!
use App\Models\PurchaseModel;
use App\Models\DeliveryModel;
use App\Models\PaymentModel;

class Dashboard extends BaseController
{
    private function tableExists(string $table): bool
    {
        return db_connect()->tableExists($table);
    }

    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        
        $data = [
            'title'    => 'Admin Dashboard',
            'username' => session()->get('username'),
            'users'    => $userModel->findAll(),
            'cards'    => [
                'today_purchases'    => 0,
                'pending_deliveries' => 0,
                'unpaid_amount'      => 0,
            ],
        ];

        if ($this->tableExists('purchases')) {
            $purchaseModel = new PurchaseModel();
            $data['cards']['today_purchases'] = (float) ($purchaseModel
                ->selectSum('total_cost')
                ->where('purchase_date', date('Y-m-d'))
                ->first()['total_cost'] ?? 0);
        }

        if ($this->tableExists('deliveries')) {
            $deliveryModel = new DeliveryModel();
            $data['cards']['pending_deliveries'] = $deliveryModel
                ->whereIn('status', [DeliveryModel::STATUS_SCHEDULED, DeliveryModel::STATUS_IN_TRANSIT])
                ->countAllResults();
        }

        if ($this->tableExists('payments')) {
            $paymentModel = new PaymentModel();
            $data['cards']['unpaid_amount'] = (float) ($paymentModel
                ->selectSum('amount')
                ->where('status', PaymentModel::STATUS_PENDING)
                ->first()['amount'] ?? 0);
        }

        return view('admin/dashboard', $data);
    }
}