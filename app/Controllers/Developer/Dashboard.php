<?php

namespace App\Controllers\Developer;

use App\Controllers\BaseController;
use App\Libraries\FirebaseCloudMessenger;
use App\Models\FcmTokenModel;
use App\Models\NotificationModel;

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

    public function getDevices()
    {
        $tokenModel = new FcmTokenModel();
        $devices = $tokenModel->getDeviceTrackingData();

        return $this->response->setJSON([
            'status'  => 'success',
            'devices' => $devices,
            'total'   => count($devices),
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
