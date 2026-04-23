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
     * Save a new Daily Catch to the inventory
     */
    public function store()
    {
        $model = new ProductModel();
        
        $img = $this->request->getFile('image');
        $imageName = null;
        if ($img && $img->isValid() && ! $img->hasMoved()) {
            $imageName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/products', $imageName);
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

        if (! $model->save($data)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => implode(' ', $model->errors())]);
            }
            return redirect()->back()->with('error', implode(' ', $model->errors()))->withInput();
        }
        
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
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['error' => 'Product not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($product);
    }

    /**
     * Update an existing Product
     */
    public function update()
    {
        if (session()->get('role') !== 'admin' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $productId = (int) $this->request->getPost('id');
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found'])->setStatusCode(404);
        }

        $img = $this->request->getFile('image');
        $imageName = $product['image'];

        if ($img && $img->isValid() && ! $img->hasMoved()) {
            // Delete old image if exists
            if ($imageName && file_exists(ROOTPATH . 'public/uploads/products/' . $imageName)) {
                unlink(ROOTPATH . 'public/uploads/products/' . $imageName);
            }
            $imageName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/products', $imageName);
        }

        $data = [
            'name'          => trim($this->request->getPost('name')),
            'cost_price'    => (float) $this->request->getPost('cost_price'),
            'selling_price' => (float) $this->request->getPost('selling_price'),
            'current_stock' => (float) $this->request->getPost('current_stock'),
            'unit'          => trim($this->request->getPost('unit')),
            'image'         => $imageName,
        ];

        if (!$productModel->update($productId, $data)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $productModel->errors())
            ]);
        }

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
        if (session()->get('role') !== 'admin' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $productId = (int) $this->request->getPost('id');

        if (!$productModel->delete($productId)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to delete product.'
            ]);
        }

        log_message('info', 'Admin ' . session()->get('username') . ' deleted product ID ' . $productId);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Product deleted successfully!'
        ]);
    }
}