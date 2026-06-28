<?php

namespace App\Controllers\Developer;

use App\Controllers\BaseController;
use App\Libraries\FirebaseCloudMessenger;
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

        try {
            $fcm = new FirebaseCloudMessenger();
            $data = ['type' => 'broadcast', 'source' => 'developer', 'target' => $target];

            $result = match ($target) {
                'admin'    => $fcm->sendToRoleAndPersist('admin', 'broadcast', $title, $body, $data),
                'staff'    => $fcm->sendToRoleAndPersist('staff', 'broadcast', $title, $body, $data),
                'customer' => $fcm->sendToRoleAndPersist('customer', 'broadcast', $title, $body, $data),
                'trusted'  => $fcm->sendToTrustedAdminsAndPersist('broadcast', $title, $body, $data),
                default    => $fcm->sendToAllAndPersist('broadcast', $title, $body, $data),
            };

            $sent   = $result['sent'] ?? 0;
            $failed = $result['failed'] ?? 0;

            log_message('info', "[Developer] Broadcast sent: title='{$title}' target={$target} sent={$sent} failed={$failed}");

            return $this->response->setJSON([
                'status' => 'success',
                'sent'   => $sent,
                'failed' => $failed,
                'message' => "Broadcast sent to {$sent} device(s)" . ($failed ? " ({$failed} failed)" : ''),
            ]);
        } catch (\Exception $e) {
            log_message('error', '[Developer] Broadcast failed: ' . $e->getMessage());

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Broadcast failed: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}
