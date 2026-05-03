<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\RefundRequestModel;

class Refunds extends BaseController
{
    public function submitRefundRequest()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $reason = trim((string) $this->request->getPost('reason'));
        if ($orderId <= 0 || $reason === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Refund reason is required.'])->setStatusCode(400);
        }

        $orderModel = new OrderModel();
        $refundModel = new RefundRequestModel();
        $order = $orderModel->where('id', $orderId)
            ->where('customer_name', session()->get('username'))
            ->first();
        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $orders = new Orders();
        $lifecycle = $orders->buildLifecycleState($order);
        if (! ($lifecycle['actions']['can_refund_request'] ?? false)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Refund request window has closed.'])->setStatusCode(400);
        }

        $existing = $refundModel->where('order_id', $orderId)
            ->whereIn('status', ['Pending', 'Under Review'])
            ->first();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'A refund request is already open for this order.'])->setStatusCode(409);
        }

        $refundModel->insert([
            'order_id' => $orderId,
            'customer_name' => (string) session()->get('username'),
            'reason' => $reason,
            'status' => 'Pending',
            'evidence_paths' => null,
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Refund request submitted.']);
    }
}
