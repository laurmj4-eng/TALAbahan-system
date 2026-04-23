<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShippingLocationModel;

class ShippingController extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $model = new ShippingLocationModel();
        $data = [
            'title'     => 'Shipping Locations',
            'username'  => session()->get('username'),
            'locations' => $model->findAll()
        ];

        return view('admin/shipping_view', $data);
    }

    public function store()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied']);
        }

        $model = new ShippingLocationModel();
        $data = [
            'barangay_name'     => $this->request->getPost('barangay_name'),
            'city_municipality' => $this->request->getPost('city_municipality') ?: 'Bacolod City',
            'is_active'         => 1
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Location added!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add location']);
    }

    public function update()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied']);
        }

        $model = new ShippingLocationModel();
        $id = $this->request->getPost('id');
        $data = [
            'barangay_name'     => $this->request->getPost('barangay_name'),
            'city_municipality' => $this->request->getPost('city_municipality'),
            'is_active'         => $this->request->getPost('is_active')
        ];

        if ($model->update($id, $data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Location updated!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update location']);
    }

    public function delete()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied']);
        }

        $model = new ShippingLocationModel();
        $id = $this->request->getPost('id');

        if ($model->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Location deleted!']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete location']);
    }

    public function getDetails($id)
    {
        $model = new ShippingLocationModel();
        $location = $model->find($id);
        return $this->response->setJSON($location);
    }
}
