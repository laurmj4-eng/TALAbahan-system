<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\ShippingLocationModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Fetch products and shippable locations
        $productModel = new ProductModel();
        $shippingModel = new ShippingLocationModel();
        $customerName = (string) session()->get('username');
        $orderCounts = $this->getCustomerOrderCounts($customerName);
        $activeOrdersCount = (int) ($orderCounts['to_pay'] + $orderCounts['to_ship'] + $orderCounts['to_receive']);

        // 3. Prepare data for the view
        $data =[
            'title'             => 'Customer Portal',
            'username'          => session()->get('username'),
            'products'          => $productModel->findAll(),
            'shippingLocations' => $shippingModel->where('is_active', 1)->findAll(),
            'orderCounts'       => $orderCounts,
            'activeOrdersCount' => $activeOrdersCount,
        ];

        // 4. Load the customer dashboard view
        return view('customer/dashboard', $data);
    }

    public function getCustomerOrderCounts(string $customerName): array
    {
        $orderModel = new OrderModel();

        $all = (int) $orderModel
            ->where('customer_name', $customerName)
            ->countAllResults();

        $toPayQuery = (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_PENDING);
        
        $db = db_connect();
        if ($db->fieldExists('payment_status', 'orders')) {
            $toPayQuery->whereIn('payment_status', ['unpaid', 'failed', 'pending_confirmation']);
        }
        $toPay = (int) $toPayQuery->countAllResults();

        $toShip = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_PROCESSING)
            ->countAllResults();

        $toReceive = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_SHIPPED)
            ->countAllResults();

        $completed = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_COMPLETED)
            ->countAllResults();

        return [
            'all' => $all,
            'to_pay' => $toPay,
            'to_ship' => $toShip,
            'to_receive' => $toReceive,
            'completed' => $completed,
        ];
    }
}
