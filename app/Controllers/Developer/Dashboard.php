<?php

namespace App\Controllers\Developer;

use App\Controllers\BaseController;
use App\Libraries\FirebaseCloudMessenger;
use App\Models\BroadcastLogModel;
use App\Models\BroadcastReceiptModel;
use App\Models\FcmTokenModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $data = [
            'title'    => 'Developer Dashboard',
            'username' => session()->get('username') ?? 'Developer',
        ];

        return inertia('developer/Dashboard', $data);
    }

    public function settings()
    {
        $data = [
            'title'    => 'Developer Settings',
            'username' => session()->get('username') ?? 'Developer',
            'email'    => session()->get('email') ?? '',
        ];

        return inertia('developer/Settings', $data);
    }

    public function updateProfile()
    {
        $json = $this->request->getJSON(true);
        if (!$json) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid request.',
            ])->setStatusCode(400);
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Not authenticated.',
            ])->setStatusCode(401);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'User not found.',
            ])->setStatusCode(404);
        }

        $username     = trim($json['username'] ?? '');
        $currentPass  = $json['current_password'] ?? '';
        $newPassword  = $json['new_password'] ?? '';

        if (empty($username)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Username is required.',
            ])->setStatusCode(400);
        }

        if (strlen($username) < 3) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Username must be at least 3 characters.',
            ])->setStatusCode(400);
        }

        if (!empty($newPassword) && strlen($newPassword) < 6) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Password must be at least 6 characters.',
            ])->setStatusCode(400);
        }

        if (!password_verify($currentPass, $user['password'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Current password is incorrect.',
            ])->setStatusCode(403);
        }

        if ($username !== $user['username']) {
            $existing = $userModel->where('username', $username)->where('id !=', $userId)->first();
            if ($existing) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Username is already taken.',
                ])->setStatusCode(400);
            }
        }

        $updateData = ['username' => $username];
        if (!empty($newPassword)) {
            $updateData['password'] = $newPassword;
        }

        if (!$userModel->update($userId, $updateData)) {
            $errors = $userModel->errors();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => !empty($errors) ? implode(' ', $errors) : 'Failed to update profile.',
            ])->setStatusCode(400);
        }

        session()->set('username', $username);

        log_message('info', "[Developer] Profile updated: user_id={$userId} username={$username}");

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Profile updated successfully.',
            'username' => $username,
        ]);
    }

    public function getDevices()
    {
        $tokenModel = new FcmTokenModel();
        $devices   = $tokenModel->getDeviceTrackingData();
        $analytics = $tokenModel->getDeviceAnalytics();

        return $this->response->setJSON([
            'status'    => 'success',
            'devices'   => $devices,
            'total'     => count($devices),
            'analytics' => $analytics,
        ]);
    }

    public function broadcast()
    {
        $json = $this->request->getJSON(true);
        if (!$json || empty($json['body'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Missing broadcast message body.',
            ])->setStatusCode(400);
        }

        $title  = trim($json['title'] ?? 'System Broadcast');
        $body   = trim($json['body']);
        $target = trim($json['target'] ?? 'all');
        $userId = (int) session()->get('user_id');

        try {
            $tokenModel = new FcmTokenModel();

            // Get target tokens with user info
            $tokens = match ($target) {
                'admin'    => $tokenModel->getActiveTokensByRole('admin'),
                'staff'    => $tokenModel->getActiveTokensByRole('staff'),
                'customer' => $tokenModel->getActiveTokensByRole('customer'),
                'trusted'  => $tokenModel->getActiveTrustedDeviceTokens(),
                default    => $tokenModel->getAllActiveTokens(),
            };

            if (empty($tokens)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'No active devices found for target: ' . $target,
                ])->setStatusCode(404);
            }

            // Create broadcast log
            $logModel = new BroadcastLogModel();
            $broadcastId = $logModel->insert([
                'title'         => $title,
                'body'          => $body,
                'target'        => $target,
                'total_devices' => count($tokens),
                'created_by'    => $userId ?: null,
            ]);

            if (!$broadcastId) {
                throw new \RuntimeException('Failed to create broadcast log.');
            }

            // Create receipt rows + collect user info
            $receiptModel = new BroadcastReceiptModel();
            $receiptIds = [];
            $tokenToReceipt = [];
            foreach ($tokens as $row) {
                $username = $row['username'] ?? null;
                $email    = $row['email'] ?? null;
                $deviceModel = $row['device_model'] ?? null;

                $rid = $receiptModel->insert([
                    'broadcast_id'  => $broadcastId,
                    'token'         => $row['token'],
                    'user_id'       => !empty($row['user_id']) ? (int) $row['user_id'] : null,
                    'username'      => $username,
                    'email'         => $email,
                    'device_model'  => $deviceModel,
                    'status'        => 'pending',
                ]);
                $receiptIds[] = $rid;
                $tokenToReceipt[$row['token']] = $rid;
            }

            // Send via FCM with broadcast_id in data payload
            $data = [
                'type'         => 'broadcast',
                'source'       => 'developer',
                'target'       => $target,
                'broadcast_id' => (string) $broadcastId,
            ];

            $fcm = new FirebaseCloudMessenger();
            $sent   = 0;
            $failed = 0;

            foreach ($tokens as $row) {
                $rid = $tokenToReceipt[$row['token']] ?? null;
                $result = $fcm->sendToDevice($row['token'], $title, $body, $data);

                if (!empty($result['success'])) {
                    $sent++;
                    if ($rid) {
                        $receiptModel->update($rid, [
                            'status' => 'sent',
                        ]);
                    }
                } else {
                    $failed++;
                    if ($rid) {
                        $receiptModel->update($rid, [
                            'status'    => 'failed',
                            'fcm_error' => substr($result['error'] ?? 'Unknown', 0, 500),
                        ]);
                    }
                }
            }

            // Also persist to notifications table
            $notificationModel = new NotificationModel();
            $seenUserIds = [];
            foreach ($tokens as $row) {
                $uid = !empty($row['user_id']) ? (int) $row['user_id'] : null;
                if ($uid !== null && !in_array($uid, $seenUserIds, true)) {
                    $notificationModel->insert([
                        'user_id' => $uid,
                        'type'    => 'broadcast',
                        'title'   => $title,
                        'body'    => $body,
                        'data'    => json_encode(['broadcast_id' => $broadcastId]),
                    ]);
                    $seenUserIds[] = $uid;
                } elseif ($uid === null) {
                    $notificationModel->insert([
                        'user_id' => null,
                        'type'    => 'broadcast',
                        'title'   => $title,
                        'body'    => $body,
                        'data'    => json_encode(['broadcast_id' => $broadcastId]),
                    ]);
                }
            }

            // Update broadcast log counts
            $logModel->update($broadcastId, [
                'sent_count'   => $sent,
                'failed_count' => $failed,
            ]);

            log_message('info', "[Developer] Broadcast #{$broadcastId} sent: title='{$title}' target={$target} tokens=" . count($tokens) . " sent={$sent} failed={$failed}");

            return $this->response->setJSON([
                'status'       => 'success',
                'broadcast_id' => (int) $broadcastId,
                'sent'         => $sent,
                'failed'       => $failed,
                'total'        => count($tokens),
                'message'      => "Broadcast sent to {$sent} device(s)" . ($failed ? " ({$failed} failed)" : ''),
            ]);
        } catch (\Exception $e) {
            log_message('error', '[Developer] Broadcast failed: ' . $e->getMessage());

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Broadcast failed: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function broadcastHistory()
    {
        $logModel = new BroadcastLogModel();
        $broadcasts = $logModel->getBroadcastsWithStats(20);

        return $this->response->setJSON([
            'status'     => 'success',
            'broadcasts' => $broadcasts,
        ]);
    }

    public function broadcastReceipts(int $broadcastId)
    {
        $receiptModel = new BroadcastReceiptModel();
        $receipts = $receiptModel->getReceiptsByBroadcast($broadcastId);

        return $this->response->setJSON([
            'status'   => 'success',
            'receipts' => $receipts,
        ]);
    }
}
