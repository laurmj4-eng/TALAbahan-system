<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ActivityLogModel;

class ActivityLogger implements FilterInterface
{
    /**
     * We use the 'after' filter to capture the response status code
     * and ensure the page has loaded before logging the visit.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // No action needed before request
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $session = session();
        $agent = $request->getUserAgent();
        $ip = $request->getIPAddress();
        $path = uri_string();

        // Skip logging for debug toolbar and internal AJAX requests if necessary
        if (strpos($path, 'debugbar') !== false || $request->isAJAX()) {
            return;
        }

        // 1. Determine Location
        $location = $this->getLocation($ip);

        // 2. Determine Identity
        $identity = $session->get('email') ?? 'Guest';
        $userId = $session->get('user_id');
        $role = $session->get('role') ?? 'guest';

        // 3. Determine Device (Browser & OS)
        $device = $agent->getBrowser() . ' on ' . $agent->getPlatform();

        // 4. Map URL to Friendly Event
        $event = $this->getFriendlyEvent($path, $request->getMethod());

        // 5. Capture Status Code
        $statusCode = $response->getStatusCode();

        // Save to Database
        $logModel = new ActivityLogModel();
        $logModel->insert([
            'user_id'       => $userId,
            'user_identity' => $identity,
            'role'          => $role,
            'event'         => $event,
            'device'        => $device,
            'location'      => $location,
            'ip_address'    => $ip,
            'status_code'   => $statusCode,
        ]);

        // 6. Update User Last Active
        if ($userId) {
            $userModel = new \App\Models\UserModel();
            $userModel->update($userId, ['last_active' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Get location from IP using ip-api.com
     */
    private function getLocation($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }

        try {
            $context = stream_context_create(['http' => ['timeout' => 2]]); // 2 second timeout
            $response = @file_get_contents("http://ip-api.com/json/{$ip}", false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    return $data['city'] . ', ' . $data['country'];
                }
            }
        } catch (\Exception $e) {
            log_message('error', '[ActivityLogger] Location Error: ' . $e->getMessage());
        }

        return 'Unknown Location';
    }

    /**
     * Turn technical URLs into readable events
     */
    private function getFriendlyEvent($path, $method)
    {
        if ($path === '' || $path === '/') return 'Viewing Home Page';
        
        // Dynamic patterns using regex
        $patterns = [
            '/^admin\/activity\/user\/\d+$/' => 'Viewing a User Timeline',
            '/^admin\/orders\/show\/\d+$/'   => 'Reviewing Order Details',
            '/^admin\/products\/getDetails\/\d+$/' => 'Viewing Product Specifications',
            '/^customer\/order-details\/\d+$/' => 'Checking Order Status',
            '/^customer\/tracking\/\d+$/'    => 'Tracking Delivery',
        ];

        foreach ($patterns as $pattern => $description) {
            if (preg_match($pattern, $path)) {
                return $description;
            }
        }

        // Exact matches
        $routes = [
            'login'                => 'Accessing Login Page',
            'register'             => 'Accessing Registration Page',
            'admin/dashboard'      => 'Viewing Admin Dashboard',
            'admin/users'          => 'Managing User Database',
            'admin/products'       => 'Managing Product Inventory',
            'admin/orders'         => 'Reviewing Customer Orders',
            'admin/shipping'       => 'Configuring Shipping Rates',
            'admin/vouchers'       => 'Managing Discount Vouchers',
            'admin/activity'       => 'Viewing Activity Monitor',
            'customer/dashboard'   => 'Viewing Customer Portal',
            'customer/order-items' => 'Checking Order History',
            'customer/profile'     => 'Updating User Profile',
            'staff/dashboard'      => 'Viewing Staff Panel',
            'staff/orders'         => 'Processing Orders',
            'logout'               => 'Signing Out',
        ];

        foreach ($routes as $routePath => $description) {
            if ($path === $routePath || strpos($path, $routePath . '/') === 0) {
                return $description;
            }
        }

        return ucfirst(strtolower($method)) . ' Request: /' . $path;
    }
}
