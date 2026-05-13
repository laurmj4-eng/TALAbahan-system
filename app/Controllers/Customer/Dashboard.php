<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\OrderReviewModel;
use App\Models\ProductModel;
use App\Models\ShippingLocationModel;
use App\Models\SettingsModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Fetch models
        $productModel = new ProductModel();
        $shippingModel = new ShippingLocationModel();
        $orderItemModel = new OrderItemModel();
        $reviewModel = new OrderReviewModel();
        $settingsModel = new SettingsModel();

        $customerName = (string) session()->get('username');
        $orderCounts = $this->getCustomerOrderCounts($customerName);
        $activeOrdersCount = (int) ($orderCounts['to_pay'] + $orderCounts['to_ship'] + $orderCounts['to_receive']);

        // 3. Fetch products and calculate real-time social proof
        $products = $productModel->findAll();
        
        foreach ($products as &$p) {
            // Get actual sold count from order_items
            $p['real_sold_count'] = $orderItemModel->getTotalQtySoldByProduct((int)$p['id']);
            
            // Get real-time average rating
            // 1. Get all order IDs that contain this product
            $orderIds = array_column($orderItemModel->where('product_id', $p['id'])->select('order_id')->findAll(), 'order_id');
            
            if (!empty($orderIds)) {
                // 2. Get average rating from order_reviews for these orders
                $ratingResult = $reviewModel->selectAvg('rating')
                    ->whereIn('order_id', $orderIds)
                    ->first();
                $p['real_rating'] = $ratingResult['rating'] ? round((float)$ratingResult['rating'], 1) : null;
            } else {
                $p['real_rating'] = null;
            }
        }

        // 4. Prepare data for the view
        $data =[
            'title'             => 'Customer Portal',
            'username'          => session()->get('username'),
            'products'          => $products,
            'shippingLocations' => $shippingModel->where('is_active', 1)->findAll(),
            'ship_to_all'       => $settingsModel->getSetting('ship_to_all', '0'),
            'orderCounts'       => $orderCounts,
            'activeOrdersCount' => $activeOrdersCount,
            'isAJAX'            => $this->request->isAJAX(),
        ];

        // 5. Load the customer dashboard view using Inertia
        return inertia('customer/Dashboard', $data);
    }

    public function getData()
    {
        $productModel = new ProductModel();
        $orderItemModel = new OrderItemModel();
        $reviewModel = new OrderReviewModel();

        $products = $productModel->findAll();
        
        foreach ($products as &$p) {
            $p['real_sold_count'] = $orderItemModel->getTotalQtySoldByProduct((int)$p['id']);
            
            $orderIds = array_column($orderItemModel->where('product_id', $p['id'])->select('order_id')->findAll(), 'order_id');
            
            if (!empty($orderIds)) {
                $ratingResult = $reviewModel->selectAvg('rating')
                    ->whereIn('order_id', $orderIds)
                    ->first();
                $p['real_rating'] = $ratingResult['rating'] ? round((float)$ratingResult['rating'], 1) : null;
            } else {
                $p['real_rating'] = null;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'products' => $products
        ]);
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
