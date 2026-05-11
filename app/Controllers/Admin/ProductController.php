<?php

namespace App\Controllers\Admin; // Updated namespace for the subfolder

// Import the necessary classes from their respective locations
use App\Controllers\BaseController;
use App\Models\ProductModel;

class ProductController extends BaseController
{
    /**
     * Display the Daily Seafood Inventory
     */
    public function index()
    {
        $model = new ProductModel();
        
        $data = [
            'title'    => 'Daily Seafood Inventory',
            'products' => $model->getDailyInventory() // Uses the custom method we added to the model
        ];

        // Points to app/Views/admin/productview.php
        return view('admin/productview', $data);
    }

    /**
     * Fetch all products as JSON for Vue
     */
    public function list()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $model = new ProductModel();
        $products = $model->getDailyInventory();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $products,
            'token' => csrf_hash()
        ]);
    }

    /**
     * Save a new Daily Catch to the inventory
     */
    public function store()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $model = new ProductModel();
        $db = db_connect();
        
        $img = $this->request->getFile('image');
        $imageName = null;
        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        if ($img && $img->isValid() && ! $img->hasMoved()) {
            $imageName = $img->getRandomName();
            if (! $img->move($uploadDir, $imageName)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to upload image'])->setStatusCode(500);
            }
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'cost_price'    => $this->request->getPost('cost_price'),
            'selling_price' => $this->request->getPost('selling_price'),
            'initial_stock' => $this->request->getPost('quantity'),
            'current_stock' => $this->request->getPost('quantity'), // At morning, current stock equals initial
            'unit'          => $this->request->getPost('unit'),
            'image'         => $imageName,
        ];

        $db->transBegin();
        if (! $model->save($data)) {
            $db->transRollback();
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => implode(' ', $model->errors())])->setStatusCode(400);
            }
            return redirect()->back()->with('error', implode(' ', $model->errors()))->withInput();
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save product'])->setStatusCode(500);
        }
        $db->transCommit();
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Seafood stock added successfully!']);
        }

        // Redirect back to the inventory list
        return redirect()->to('/admin/products')->with('msg', 'Seafood stock added successfully!');
    }

    /**
     * Get Product Details for editing (JSON)
     */
    public function getDetails($productId)
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found', 'token' => csrf_hash()])->setStatusCode(404);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Product fetched.', 'data' => $product, 'token' => csrf_hash()]);
    }

    /**
     * Update an existing Product
     */
    public function update()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $db = db_connect();
        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $productId = (int) $this->request->getPost('id');
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found'])->setStatusCode(404);
        }

        $img = $this->request->getFile('image');
        $imageName = $product['image'];

        if ($img && $img->isValid() && ! $img->hasMoved()) {
            // Delete old image if exists
            if ($imageName) {
                $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $imageName;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $imageName = $img->getRandomName();
            if (! $img->move($uploadDir, $imageName)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Failed to upload new image.'
                ])->setStatusCode(500);
            }
        }

        $data = [
            'name'          => trim($this->request->getPost('name')),
            'cost_price'    => (float) $this->request->getPost('cost_price'),
            'selling_price' => (float) $this->request->getPost('selling_price'),
            'current_stock' => (float) $this->request->getPost('current_stock'),
            'unit'          => trim($this->request->getPost('unit')),
            'image'         => $imageName,
        ];

        $db->transBegin();
        if (!$productModel->update($productId, $data)) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $productModel->errors())
            ])->setStatusCode(400);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Database update failed.'
            ])->setStatusCode(500);
        }
        $db->transCommit();

        log_message('info', 'Admin ' . session()->get('username') . ' updated product ID ' . $productId);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Product updated successfully!'
        ]);
    }

    /**
     * Delete a Product
     */
    public function delete()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $db = db_connect();
        $productId = (int) $this->request->getPost('id');
        if ($productId <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid product ID.'
            ])->setStatusCode(400);
        }

        $product = $productModel->find($productId);
        if (! $product) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Product not found.'
            ])->setStatusCode(404);
        }

        $db->transBegin();
        if (!$productModel->delete($productId)) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete product.'
            ])->setStatusCode(500);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Database delete failed.'
            ])->setStatusCode(500);
        }
        $db->transCommit();

        log_message('info', 'Admin ' . session()->get('username') . ' deleted product ID ' . $productId);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Product deleted successfully!'
        ]);
    }

    /**
     * Toggle Live Availability status of a Product (AJAX)
     */
    public function toggleStatus($id = null)
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $product = $productModel->find((int) $id);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found.'])->setStatusCode(404);
        }

        // Flip the value: 1 → 0 or 0 → 1
        $newStatus = ((int) $product['is_available'] === 1) ? 0 : 1;

        if (!$productModel->update((int) $id, ['is_available' => $newStatus])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update availability.'
            ])->setStatusCode(500);
        }

        log_message('info', 'Admin ' . session()->get('username') . ' toggled product ID ' . $id . ' availability to ' . ($newStatus ? 'LIVE' : 'HIDDEN'));

        return $this->response->setJSON([
            'status'       => 'success',
            'message'      => $newStatus ? 'Product is now LIVE.' : 'Product is now HIDDEN from customers.',
            'is_available' => $newStatus,
        ]);
    }
}