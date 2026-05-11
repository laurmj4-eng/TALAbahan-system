<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShippingLocationModel;
use App\Models\SettingsModel;

class ShippingController extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $model = new ShippingLocationModel();
        $settingsModel = new SettingsModel();
        
        $data = [
            'title'             => 'Shipping Locations',
            'username'          => session()->get('username'),
            'locations'         => $model->findAll(),
            'ship_to_all'       => $settingsModel->getSetting('ship_to_all', '0')
        ];

        return view('admin/shipping_view', $data);
    }

    /**
     * Get all shipping locations (JSON) for SPA
     */
    public function getLocations()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new ShippingLocationModel();
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Locations fetched.',
            'data' => $model->findAll(),
            'token' => csrf_hash()
        ]);
    }

    public function updateGlobalShipping()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $settingsModel = new SettingsModel();
        $value = $this->request->getPost('ship_to_all');

        if ($settingsModel->updateSetting('ship_to_all', $value)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Global shipping setting updated!',
                'token'   => csrf_hash(),
            ])->setStatusCode(200);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update setting', 'token' => csrf_hash()])->setStatusCode(400);
    }

    public function store()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new ShippingLocationModel();
        $data = [
            'barangay_name'     => $this->request->getPost('barangay_name'),
            'city_municipality' => $this->request->getPost('city_municipality') ?: 'Bacolod City',
            'is_active'         => 1
        ];

        if ($model->insert($data)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Location added!',
                'data'    => ['id' => (int) $model->getInsertID()],
                'token'   => csrf_hash(),
            ])->setStatusCode(201);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to add location', 'token' => csrf_hash()])->setStatusCode(400);
    }

    public function update()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new ShippingLocationModel();
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid location ID', 'token' => csrf_hash()])->setStatusCode(400);
        }
        $data = [
            'barangay_name'     => $this->request->getPost('barangay_name'),
            'city_municipality' => $this->request->getPost('city_municipality'),
            'is_active'         => $this->request->getPost('is_active')
        ];

        if ($model->update($id, $data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Location updated!', 'data' => ['id' => $id], 'token' => csrf_hash()])->setStatusCode(200);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update location', 'token' => csrf_hash()])->setStatusCode(400);
    }

    public function delete()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new ShippingLocationModel();
        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid location ID', 'token' => csrf_hash()])->setStatusCode(400);
        }

        if ($model->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Location deleted!', 'data' => ['id' => $id], 'token' => csrf_hash()])->setStatusCode(200);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete location', 'token' => csrf_hash()])->setStatusCode(400);
    }

    public function getDetails($id)
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new ShippingLocationModel();
        $location = $model->find((int) $id);
        if (! $location) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Location not found', 'token' => csrf_hash()])->setStatusCode(404);
        }
        return $this->response->setJSON(['status' => 'success', 'message' => 'Location fetched.', 'data' => $location, 'token' => csrf_hash()]);
    }
}
