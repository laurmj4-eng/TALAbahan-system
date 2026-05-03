<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderReviewModel;

class Reviews extends BaseController
{
    public function submitReview()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $rating = (int) $this->request->getPost('rating');
        $comment = trim((string) $this->request->getPost('comment'));

        if ($orderId <= 0 || $rating < 1 || $rating > 5) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid review payload.'])->setStatusCode(400);
        }

        $orderModel = new OrderModel();
        $reviewModel = new OrderReviewModel();
        $order = $orderModel->where('id', $orderId)
            ->where('customer_name', session()->get('username'))
            ->first();
        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $orders = new Orders();
        $lifecycle = $orders->buildLifecycleState($order);
        if (! ($lifecycle['actions']['can_review'] ?? false)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Review is not available for this order.'])->setStatusCode(400);
        }

        if ($reviewModel->where('order_id', $orderId)->first()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Review already submitted for this order.'])->setStatusCode(409);
        }

        $reviewModel->insert([
            'order_id' => $orderId,
            'customer_name' => (string) session()->get('username'),
            'rating' => $rating,
            'comment' => $comment === '' ? null : $comment,
            'media_paths' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Review submitted.']);
    }
}
