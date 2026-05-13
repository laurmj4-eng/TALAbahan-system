<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;

class Profile extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $customerName = (string) session()->get('username');
        $dashboard = new Dashboard();
        $counts = $dashboard->getCustomerOrderCounts($customerName);

        return inertia('customer/Profile', [
            'title' => 'My Profile',
            'username' => $customerName,
            'counts' => $counts,
            'isAJAX' => $this->request->isAJAX(),
        ]);
    }
}
