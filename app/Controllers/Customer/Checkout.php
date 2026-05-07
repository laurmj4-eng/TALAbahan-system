<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ShippingLocationModel;
use App\Services\CheckoutService;
use App\Models\ProductModel;
use App\Models\ProductPaymentConstraintModel;
use App\Models\VoucherModel;
use App\Models\CodComplianceModel;

class Checkout extends BaseController
{
    private const ALLOWED_PAYMENT_METHODS = ['COD', 'GCASH'];
    protected $checkoutService;

    public function __construct()
    {
        $this->checkoutService = new CheckoutService();
    }

    public function preCheckout()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderDataJson = $this->request->getPost('order_data');
        $orderData = json_decode((string) $orderDataJson, true);
        
        if (! is_array($orderData)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid checkout payload.'])->setStatusCode(400);
        }

        $username = session()->get('username');
        $quote = $this->checkoutService->buildCheckoutQuote($orderData, $username);
        
        if (! $quote['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $quote['message']])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $quote['data'],
        ]);
    }

    public function placeOrder()
    {
        if (session()->get('role') !== 'customer' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderDataJson = $this->request->getPost('order_data');
        $orderData = json_decode((string) $orderDataJson, true);
        
        if (! is_array($orderData)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid checkout payload.'])->setStatusCode(400);
        }

        $username = session()->get('username');
        $quote = $this->checkoutService->buildCheckoutQuote($orderData, $username);
        
        if (! $quote['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $quote['message']])->setStatusCode(400);
        }

        $result = $this->checkoutService->placeOrder($quote['data'], $username);
        
        if (! $result['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $result['message']])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $result['message'],
            'transaction_code' => $result['transaction_code'],
        ]);
    }

    /**
     * Validate if the detected barangay is shippable
     */
    public function validateLocation()
    {
        $barangay = trim($this->request->getPost('barangay'));
        if (empty($barangay)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No location detected']);
        }

        $settingsModel = new \App\Models\SettingsModel();
        if ($settingsModel->getSetting('ship_to_all', '0') === '1') {
            return $this->response->setJSON(['status' => 'success']);
        }

        $shippingModel = new ShippingLocationModel();
        
        $location = $shippingModel->where('barangay_name', $barangay)
                                 ->where('is_active', 1)
                                 ->first();

        if (!$location) {
            $location = $shippingModel->like('barangay_name', $barangay)
                                     ->where('is_active', 1)
                                     ->first();
        }

        if ($location) {
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Location not supported']);
    }
}
