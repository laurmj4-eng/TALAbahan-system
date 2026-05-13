<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderItemModel;

class Orders extends BaseController
{
    /**
     * Get Orders Overview
     */
    public function index()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $orderModel = new OrderModel();
        $data = [
            'title'    => 'Order Tracking - Staff',
            'username' => session()->get('username'),
            'orders'   => $orderModel->getOrdersWithItemCount(),
        ];

        return inertia('staff/Orders', $data);
    }

    /**
     * Update Order Status (Staff)
     */
    public function updateOrderStatus()
    {
        if (session()->get('role') !== 'staff' || !$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Access Denied',
                'token'   => csrf_hash()
            ]);
        }

        try {
            $orderModel = new OrderModel();
            $orderId    = (int) $this->request->getPost('id');
            $newStatus  = trim((string) $this->request->getPost('status'));

            if (empty($orderId) || empty($newStatus)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Missing required data',
                    'token'   => csrf_hash()
                ])->setStatusCode(400);
            }

            $allowedStatuses = [
                OrderModel::STATUS_PENDING,
                OrderModel::STATUS_PROCESSING,
                OrderModel::STATUS_SHIPPED,
                OrderModel::STATUS_COMPLETED,
                OrderModel::STATUS_CANCELLED,
                OrderModel::STATUS_REFUNDED,
            ];
            if (! in_array($newStatus, $allowedStatuses, true)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Invalid order status.',
                    'token'   => csrf_hash(),
                ])->setStatusCode(400);
            }

            $order = $orderModel->find($orderId);
            if (!$order) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Order not found.',
                    'token'   => csrf_hash()
                ])->setStatusCode(404);
            }

            $db = db_connect();
            $db->transBegin();

            if (!$orderModel->update($orderId, ['status' => $newStatus])) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Database update failed: ' . implode(', ', $orderModel->errors()),
                    'token'   => csrf_hash()
                ])->setStatusCode(500);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Database update failed.',
                    'token'   => csrf_hash(),
                ])->setStatusCode(500);
            }
            $db->transCommit();

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Order status updated to ' . $newStatus,
                'token'   => csrf_hash()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'System error: ' . $e->getMessage(),
                'token'   => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get Orders (JSON)
     */
    public function getOrders()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $orders = $orderModel->getOrdersWithItemCount();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Orders fetched.', 'data' => $orders, 'token' => csrf_hash()]);
    }

    /**
     * Get Order Details
     */
    public function getOrderDetail($orderId)
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Access Denied',
                'token'   => csrf_hash()
            ]);
        }

        $orderModel = new OrderModel();
        $order      = $orderModel->find($orderId);

        if (!$order) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Order not found',
                'token'   => csrf_hash()
            ]);
        }

        $orderItemModel = new OrderItemModel();
        $order['items'] = $orderItemModel->getItemsByOrder($orderId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $order,
            'token'  => csrf_hash()
        ]);
    }
}
