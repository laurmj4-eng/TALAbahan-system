<?php

namespace App\Libraries;

use App\Models\FcmTokenModel;
use App\Models\NotificationModel;
use Exception;

class FirebaseCloudMessenger
{
    private string $projectId;
    private string $adminKeyPath;
    private ?string $cachedToken = null;
    private ?int $tokenExpiresAt = null;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID', 'sefood-d603d');
        $this->adminKeyPath = WRITEPATH . 'conf/talabahan-firebase-admin.json';
        $this->ensureKeyFile();
    }

    private function ensureKeyFile(): void
    {
        if (file_exists($this->adminKeyPath)) {
            return;
        }

        $b64 = env('FIREBASE_ADMIN_KEY_B64');
        if (empty($b64)) {
            return;
        }

        $json = base64_decode($b64, true);
        if ($json === false || empty($json)) {
            log_message('error', '[FCM] FIREBASE_ADMIN_KEY_B64 is not valid Base64.');
            return;
        }

        $dir = dirname($this->adminKeyPath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                log_message('error', '[FCM] Failed to create directory: ' . $dir);
                return;
            }
        }

        if (file_put_contents($this->adminKeyPath, $json) === false) {
            log_message('error', '[FCM] Failed to write Firebase admin key to: ' . $this->adminKeyPath);
            return;
        }

        log_message('info', '[FCM] Firebase admin key written from FIREBASE_ADMIN_KEY_B64');
    }

    public function setAdminKeyPath(string $path): void
    {
        $this->adminKeyPath = $path;
    }

    private function getAccessToken(): string
    {
        if ($this->cachedToken !== null && $this->tokenExpiresAt !== null && time() < $this->tokenExpiresAt) {
            return $this->cachedToken;
        }

        if (!file_exists($this->adminKeyPath)) {
            throw new Exception('Firebase Admin SDK key not found at: ' . $this->adminKeyPath . '. Create it from Firebase Console > Service Accounts.');
        }

        $serviceAccount = json_decode(file_get_contents($this->adminKeyPath), true);
        if (!$serviceAccount || !isset($serviceAccount['client_email'], $serviceAccount['private_key'])) {
            throw new Exception('Invalid Firebase Admin SDK key file.');
        }

        $now = time();
        $payload = [
            'iss'   => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ];

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $body   = $this->base64UrlEncode(json_encode($payload));

        $privateKey = openssl_get_privatekey($serviceAccount['private_key']);
        if (!$privateKey) {
            throw new Exception('Failed to parse Firebase private key.');
        }

        $signature = '';
        if (!openssl_sign($header . '.' . $body, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Failed to sign JWT assertion.');
        }
        openssl_free_key($privateKey);

        $jwt = $header . '.' . $body . '.' . $this->base64UrlEncode($signature);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://oauth2.googleapis.com/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new Exception('Failed to obtain OAuth2 token: HTTP ' . $httpCode . ' ' . ($error ?: $response));
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['access_token'], $data['expires_in'])) {
            throw new Exception('Invalid OAuth2 token response.');
        }

        $this->cachedToken = $data['access_token'];
        $this->tokenExpiresAt = $now + (int) $data['expires_in'] - 60;

        return $this->cachedToken;
    }

    public function sendToDevice(string $token, string $title, string $body, array $data = []): array
    {
        $message = [
            'token' => $token,
            'data'  => array_merge([
                'title' => $title,
                'body'  => $body,
            ], $data),
        ];

        return $this->sendMessage($message);
    }

    public function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $tokenModel = new FcmTokenModel();
        $tokens = $tokenModel->getActiveTokensByUser($userId);

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active device tokens for user.'];
        }

        $results = [];
        foreach ($tokens as $row) {
            $results[] = $this->sendToDevice($row['token'], $title, $body, $data);
        }

        return ['success' => true, 'results' => $results];
    }

    public function sendToRole(string $role, string $title, string $body, array $data = []): array
    {
        $tokenModel = new FcmTokenModel();
        $tokens = $tokenModel->getActiveTokensByRole($role);

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active device tokens for role: ' . $role];
        }

        $results = [];
        foreach ($tokens as $row) {
            $results[] = $this->sendToDevice($row['token'], $title, $body, $data);
        }

        return ['success' => true, 'results' => $results];
    }

    public function sendToRoleAndPersist(string $role, string $type, string $title, string $body, array $data = []): array
    {
        $tokenModel = new FcmTokenModel();
        $tokens = $tokenModel->getActiveTokensByRole($role);

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No active device tokens for role: ' . $role];
        }

        $notificationModel = new NotificationModel();
        $results = [];
        $seenUserIds = [];

        foreach ($tokens as $row) {
            if (!in_array($row['user_id'], $seenUserIds, true)) {
                $notificationModel->insert([
                    'user_id' => $row['user_id'],
                    'type'    => $type,
                    'title'   => $title,
                    'body'    => $body,
                    'data'    => !empty($data) ? json_encode($data) : null,
                ]);
                $seenUserIds[] = (int) $row['user_id'];
            }
            $results[] = $this->sendToDevice($row['token'], $title, $body, $data);
        }

        return ['success' => true, 'results' => $results, 'notified_users' => $seenUserIds];
    }

    public function sendToTrustedAdmins(string $title, string $body, array $data = []): array
    {
        $tokenModel = new FcmTokenModel();
        $tokens = $tokenModel->getActiveTrustedDeviceTokens();

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No trusted admin devices.'];
        }

        $results = [];
        foreach ($tokens as $row) {
            $results[] = $this->sendToDevice($row['token'], $title, $body, $data);
        }

        return ['success' => true, 'results' => $results];
    }

    public function sendToTrustedAdminsAndPersist(string $type, string $title, string $body, array $data = []): array
    {
        $tokenModel = new FcmTokenModel();
        $tokens = $tokenModel->getActiveTrustedDeviceTokens();

        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No trusted admin devices.'];
        }

        $notificationModel = new NotificationModel();
        $results = [];
        $seenUserIds = [];

        foreach ($tokens as $row) {
            $uid = $row['user_id'] !== null ? (int) $row['user_id'] : null;
            if ($uid !== null && !in_array($uid, $seenUserIds, true)) {
                $notificationModel->insert([
                    'user_id' => $uid,
                    'type'    => $type,
                    'title'   => $title,
                    'body'    => $body,
                    'data'    => !empty($data) ? json_encode($data) : null,
                ]);
                $seenUserIds[] = $uid;
            } elseif ($uid === null) {
                $notificationModel->insert([
                    'user_id' => null,
                    'type'    => $type,
                    'title'   => $title,
                    'body'    => $body,
                    'data'    => !empty($data) ? json_encode($data) : null,
                ]);
            }
            $results[] = $this->sendToDevice($row['token'], $title, $body, $data);
        }

        return ['success' => true, 'results' => $results, 'notified_users' => $seenUserIds];
    }

    public function sendToAllAndPersist(string $type, string $title, string $body, array $data = []): array
    {
        $tokenModel = new FcmTokenModel();
        $tokens = $tokenModel->getAllActiveTokens();

        if (empty($tokens)) {
            return ['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'No active device tokens.'];
        }

        $notificationModel = new NotificationModel();
        $sent   = 0;
        $failed = 0;
        $seenUserIds = [];

        foreach ($tokens as $row) {
            $result = $this->sendToDevice($row['token'], $title, $body, $data);

            if (!empty($result['success'])) {
                $sent++;
            } else {
                $failed++;
                if (!empty($result['error'])) {
                    log_message('error', '[FCM] Broadcast failed for token ' . substr($row['token'], 0, 20) . '...: ' . $result['error']);
                }
            }

            $uid = $row['user_id'] !== null ? (int) $row['user_id'] : null;
            if ($uid !== null && !in_array($uid, $seenUserIds, true)) {
                $notificationModel->insert([
                    'user_id' => $uid,
                    'type'    => $type,
                    'title'   => $title,
                    'body'    => $body,
                    'data'    => !empty($data) ? json_encode($data) : null,
                ]);
                $seenUserIds[] = $uid;
            } elseif ($uid === null) {
                $notificationModel->insert([
                    'user_id' => null,
                    'type'    => $type,
                    'title'   => $title,
                    'body'    => $body,
                    'data'    => !empty($data) ? json_encode($data) : null,
                ]);
            }
        }

        return ['success' => true, 'sent' => $sent, 'failed' => $failed, 'notified_users' => $seenUserIds];
    }

    public function sendToUserAndPersist(int $userId, string $type, string $title, string $body, array $data = []): array
    {
        $notificationModel = new NotificationModel();
        $notificationModel->insert([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => !empty($data) ? json_encode($data) : null,
        ]);

        return $this->sendToUser($userId, $title, $body, $data);
    }

    private function sendMessage(array $message): array
    {
        $accessToken = $this->getAccessToken();
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = json_encode(['message' => $message]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            log_message('error', '[FCM] Request failed: ' . $error);
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);

        if ($httpCode === 200) {
            return ['success' => true, 'name' => $result['name'] ?? ''];
        }

        $errorMsg = $result['error']['message'] ?? ($result['error']['status'] ?? 'Unknown FCM error');
        log_message('error', '[FCM] HTTP ' . $httpCode . ': ' . $errorMsg);

        if ($httpCode === 404 && strpos($errorMsg, 'NotRegistered') !== false) {
            $this->handleUnregisteredToken($message['token'] ?? '');
        }

        return ['success' => false, 'error' => $errorMsg, 'httpCode' => $httpCode];
    }

    private function handleUnregisteredToken(string $token): void
    {
        if ($token === '') return;
        $tokenModel = new FcmTokenModel();
        $tokenModel->deactivateToken($token);
        log_message('info', '[FCM] Deactivated unregistered token: ' . substr($token, 0, 20) . '...');
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
