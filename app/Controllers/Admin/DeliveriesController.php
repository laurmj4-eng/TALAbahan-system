<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DeliveryModel;

class DeliveriesController extends BaseController
{
    private function hasTable(): bool
    {
        return db_connect()->tableExists('deliveries');
    }

    public function index()
    {
        if (! $this->hasTable()) {
            return view('admin/deliveries_view', [
                'deliveries' => [],
                'orders'     => [],
                'error'      => 'Deliveries table is missing. Run: php spark migrate',
            ]);
        }

        $model = new DeliveryModel();
        $orders = new \App\Models\OrderModel();
        $q = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $builder = $model->orderBy('created_at', 'DESC');
        if ($q !== '') {
            $builder = $builder->like('rider_name', $q);
        }
        if ($status !== '') {
            $builder = $builder->where('status', $status);
        }
        return view('admin/deliveries_view', [
            'deliveries' => $builder->paginate(10),
            'pager'      => $model->pager,
            'filters'    => compact('q', 'status'),
            'orders'     => $orders->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function store()
    {
        if (! $this->hasTable()) {
            return redirect()->to('/admin/deliveries')->with('error', 'Deliveries table is missing. Run: php spark migrate');
        }

        $model = new DeliveryModel();
        $data  = [
            'order_id'      => (int) $this->request->getPost('order_id'),
            'rider_name'    => trim((string) $this->request->getPost('rider_name')),
            'route_note'    => trim((string) $this->request->getPost('route_note')),
            'eta_at'        => $this->request->getPost('eta_at') ?: null,
            'status'        => trim((string) $this->request->getPost('status')) ?: DeliveryModel::STATUS_SCHEDULED,
            'proof_url'     => trim((string) $this->request->getPost('proof_url')),
            'delivered_at'  => $this->request->getPost('delivered_at') ?: null,
            'notes'         => trim((string) $this->request->getPost('notes')),
        ];

        if (! $model->insert($data)) {
            return redirect()->back()->with('errors', $model->errors())->withInput();
        }

        return redirect()->to('/admin/deliveries')->with('msg', 'Delivery saved.');
    }

    public function markDelivered($id)
    {
        $model = new DeliveryModel();
        $model->update((int) $id, [
            'status'       => DeliveryModel::STATUS_DELIVERED,
            'delivered_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/admin/deliveries')->with('msg', 'Delivery marked as Delivered.');
    }
}
