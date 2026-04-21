<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;

class PaymentsController extends BaseController
{
    private function hasTable(): bool
    {
        return db_connect()->tableExists('payments');
    }

    public function index()
    {
        if (! $this->hasTable()) {
            return view('admin/payments_view', [
                'payments' => [],
                'orders'   => [],
                'error'    => 'Payments table is missing. Run: php spark migrate',
            ]);
        }

        $model = new PaymentModel();
        $orders = new \App\Models\OrderModel();
        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $from = trim((string) $this->request->getGet('from'));
        $to = trim((string) $this->request->getGet('to'));
        $builder = $model->orderBy('created_at', 'DESC');
        if ($q !== '') {
            $builder = $builder->groupStart()->like('reference_no', $q)->orLike('method', $q)->groupEnd();
        }
        if ($status !== '') {
            $builder = $builder->where('status', $status);
        }
        if ($from !== '') {
            $builder = $builder->where('DATE(created_at) >=', $from);
        }
        if ($to !== '') {
            $builder = $builder->where('DATE(created_at) <=', $to);
        }
        return view('admin/payments_view', [
            'payments' => $builder->paginate(10),
            'pager'    => $model->pager,
            'filters'  => compact('q', 'status', 'from', 'to'),
            'orders'   => $orders->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function store()
    {
        if (! $this->hasTable()) {
            return redirect()->to('/admin/payments')->with('error', 'Payments table is missing. Run: php spark migrate');
        }

        $model = new PaymentModel();
        $data  = [
            'order_id'      => (int) $this->request->getPost('order_id'),
            'method'        => trim((string) $this->request->getPost('method')),
            'status'        => trim((string) $this->request->getPost('status')),
            'amount'        => (float) $this->request->getPost('amount'),
            'reference_no'  => trim((string) $this->request->getPost('reference_no')),
            'paid_at'       => $this->request->getPost('paid_at') ?: null,
            'notes'         => trim((string) $this->request->getPost('notes')),
        ];

        if (! $model->insert($data)) {
            return redirect()->back()->with('errors', $model->errors())->withInput();
        }

        return redirect()->to('/admin/payments')->with('msg', 'Payment saved.');
    }

    public function markPaid($id)
    {
        $model = new PaymentModel();
        $model->update((int) $id, ['status' => PaymentModel::STATUS_PAID, 'paid_at' => date('Y-m-d H:i:s')]);
        return redirect()->to('/admin/payments')->with('msg', 'Payment marked as Paid.');
    }
}
