<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VoucherModel;

class VoucherController extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $model = new VoucherModel();
        $data = [
            'title' => 'Voucher Management',
            'username' => session()->get('username'),
            'vouchers' => $model->orderBy('created_at', 'DESC')->findAll(),
        ];

        return inertia('admin/Vouchers', $data);
    }

    /**
     * Get all vouchers (JSON) for SPA
     */
    public function getVouchers()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new VoucherModel();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Vouchers fetched.',
            'data' => $model->orderBy('created_at', 'DESC')->findAll(),
            'token' => csrf_hash()
        ]);
    }

    public function store()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new VoucherModel();
        $code = strtoupper(trim((string) $this->request->getPost('code')));
        $name = trim((string) $this->request->getPost('name'));
        $scope = strtolower(trim((string) $this->request->getPost('scope')));
        $discountType = strtolower(trim((string) $this->request->getPost('discount_type')));
        $discountValue = (float) $this->request->getPost('discount_value');
        $maxDiscount = trim((string) $this->request->getPost('max_discount'));
        $minOrderAmount = (float) $this->request->getPost('min_order_amount');
        $paymentLimit = strtoupper(trim((string) $this->request->getPost('payment_method_limit')));

        if ($code === '' || $name === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Code and name are required.', 'token' => csrf_hash()])->setStatusCode(400);
        }
        if (! in_array($scope, ['platform', 'shop'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Scope must be platform or shop.', 'token' => csrf_hash()])->setStatusCode(400);
        }
        if (! in_array($discountType, ['fixed', 'percent'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Discount type must be fixed or percent.', 'token' => csrf_hash()])->setStatusCode(400);
        }
        if ($discountValue <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Discount value must be greater than zero.', 'token' => csrf_hash()])->setStatusCode(400);
        }
        if ($discountType === 'percent' && $discountValue > 100) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Percent discount cannot exceed 100.', 'token' => csrf_hash()])->setStatusCode(400);
        }
        if ($paymentLimit !== '' && ! in_array($paymentLimit, ['COD', 'GCASH'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Payment limit must be COD or GCASH.', 'token' => csrf_hash()])->setStatusCode(400);
        }
        if ($model->where('code', $code)->first()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Voucher code already exists.', 'token' => csrf_hash()])->setStatusCode(409);
        }

        $payload = [
            'code' => $code,
            'name' => $name,
            'scope' => $scope,
            'discount_type' => $discountType,
            'discount_value' => round($discountValue, 2),
            'max_discount' => $maxDiscount === '' ? null : round((float) $maxDiscount, 2),
            'min_order_amount' => round($minOrderAmount, 2),
            'payment_method_limit' => $paymentLimit === '' ? null : $paymentLimit,
            'is_active' => 1,
            'starts_at' => null,
            'ends_at' => null,
        ];

        if (! $model->insert($payload)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save voucher.', 'token' => csrf_hash()])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Voucher created successfully.',
            'data' => ['id' => (int) $model->getInsertID()],
            'token' => csrf_hash(),
        ])->setStatusCode(201);
    }

    public function toggle()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid voucher ID.', 'token' => csrf_hash()])->setStatusCode(400);
        }

        $model = new VoucherModel();
        $voucher = $model->find($id);
        if (! $voucher) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Voucher not found.', 'token' => csrf_hash()])->setStatusCode(404);
        }

        $next = ((int) $voucher['is_active']) === 1 ? 0 : 1;
        if (! $model->update($id, ['is_active' => $next])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update voucher.', 'token' => csrf_hash()])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $next ? 'Voucher activated.' : 'Voucher deactivated.',
            'data' => ['id' => $id, 'is_active' => $next],
            'token' => csrf_hash(),
        ]);
    }
}
