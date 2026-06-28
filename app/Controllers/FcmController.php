<?php

namespace App\Controllers;

use App\Libraries\FirebaseCloudMessenger;
use App\Models\FcmTokenModel;
use App\Models\NotificationModel;
use App\Models\OrderModel;
use App\Models\UserModel;

class FcmController extends BaseController
{
    public function registerToken()
    {
        $json = $this->request->getJSON(true);
        if (!$json || empty($json['token'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Missing FCM token.',
            ])->setStatusCode(400);
        }

        $userId   = (int) ($json['user_id'] ?? session()->get('user_id'));
        $token    = trim($json['token']);
        $platform = trim($json['platform'] ?? 'android');
        $deviceModel = trim($json['device_model'] ?? '');

        if ($userId <= 0 && !empty($json['username'])) {
            $userModel = new UserModel();
            $user = $userModel->where('username', trim($json['username']))->first();
            $userId = $user ? (int) $user['id'] : 0;
        }

        if ($userId <= 0) {
            $userId = null;
        }

        $tokenModel = new FcmTokenModel();

        if ($tokenModel->tokenExists($token)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Token already registered.',
            ]);
        }

        $inserted = $tokenModel->insert([
            'user_id'      => $userId,
            'token'        => $token,
            'platform'     => $platform,
            'device_model' => $deviceModel,
            'is_active'    => 1,
        ]);

        if (!$inserted) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to register token.',
            ])->setStatusCode(500);
        }

        log_message('info', '[FCM] Token registered for user_id=' . var_export($userId, true) . ': ' . substr($token, 0, 20) . '...');

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Token registered successfully.',
        ]);
    }

    public function unregisterToken()
    {
        $json = $this->request->getJSON(true);
        if (!$json || empty($json['token'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Missing FCM token.',
            ])->setStatusCode(400);
        }

        $tokenModel = new FcmTokenModel();
        $tokenModel->deactivateToken(trim($json['token']));

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Token deactivated.',
        ]);
    }

    public function sendNotification()
    {
        $json = $this->request->getJSON(true);
        if (!$json || empty($json['user_id']) || empty($json['title'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Missing required fields: user_id, title.',
            ])->setStatusCode(400);
        }

        $fcm = new FirebaseCloudMessenger();
        $body = $json['body'] ?? '';
        $data = $json['data'] ?? [];

        try {
            $result = $fcm->sendToUserAndPersist(
                (int) $json['user_id'],
                $json['type'] ?? 'generic',
                $json['title'],
                $body,
                $data
            );

            return $this->response->setJSON([
                'status'  => $result['success'] ? 'success' : 'error',
                'message' => $result['success'] ? 'Notification sent.' : ($result['message'] ?? 'Failed to send.'),
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function unreadCount()
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Not authenticated.',
            ])->setStatusCode(401);
        }

        $notificationModel = new NotificationModel();
        $count = $notificationModel->getUnreadCount($userId);
        $recent = $notificationModel->getRecentUnread($userId, 10);

        return $this->response->setJSON([
            'status'        => 'success',
            'unread_count'  => $count,
            'notifications' => $recent,
        ]);
    }

    public function markRead(int $id)
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Not authenticated.',
            ])->setStatusCode(401);
        }

        $notificationModel = new NotificationModel();
        $notificationModel->markAsRead($id, $userId);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllRead()
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Not authenticated.',
            ])->setStatusCode(401);
        }

        $notificationModel = new NotificationModel();
        $notificationModel->markAllAsRead($userId);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function list()
    {
        $userId = (int) session()->get('user_id');
        if ($userId <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Not authenticated.',
            ])->setStatusCode(401);
        }

        $notificationModel = new NotificationModel();
        $notifications = $notificationModel->getRecentNotifications($userId);

        return $this->response->setJSON([
            'status'        => 'success',
            'notifications' => $notifications,
        ]);
    }

    public function sendOrderStatusPush(int $orderId, string $newStatus): void
    {
        try {
            $orderModel = new OrderModel();
            $order = $orderModel->find($orderId);

            if (!$order) {
                return;
            }

            $userId = !empty($order['user_id']) ? (int) $order['user_id'] : 0;

            if ($userId <= 0 && !empty($order['customer_name'])) {
                $userModel = new UserModel();
                $user = $userModel->where('username', $order['customer_name'])->first();
                $userId = $user ? (int) $user['id'] : 0;
            }

            if ($userId <= 0) {
                return;
            }

            $statusMessages = [
                'Pending'    => ['title' => 'Order Confirmed',                    'body' => 'Your order has been placed and is awaiting confirmation.'],
                'Processing' => ['title' => 'Preparing Your Order',               'body' => 'Your order is now being prepared!'],
                'Shipped'    => ['title' => 'Order Shipped',                      'body' => 'Your TALAbahan order has been shipped! Track your delivery now.'],
                'Completed'  => ['title' => 'Order Delivered',                    'body' => 'Your order has been delivered. Enjoy your meal!'],
                'Cancelled'  => ['title' => 'Order Cancelled',                    'body' => 'Your order has been cancelled.'],
                'Refunded'   => ['title' => 'Order Refunded',                     'body' => 'Your order has been refunded.'],
            ];

            $msg = $statusMessages[$newStatus] ?? ['title' => 'Order Update', 'body' => 'Your order status has been updated to: ' . $newStatus];
            $title = 'Order #' . $order['transaction_code'] . ' — ' . $msg['title'];
            $body  = $msg['body'];

            $fcm = new FirebaseCloudMessenger();
            $fcm->sendToUserAndPersist(
                $userId,
                'order_update',
                $title,
                $body,
                ['order_id' => (string) $orderId, 'status' => $newStatus, 'type' => 'order_update']
            );
        } catch (\Exception $e) {
            log_message('error', '[FCM] sendOrderStatusPush failed: ' . $e->getMessage());
        }
    }
}
