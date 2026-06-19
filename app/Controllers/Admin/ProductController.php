<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Traits\ApiResponseTrait;
use App\Models\ProductModel;

class ProductController extends BaseController
{
    use ApiResponseTrait;

    /**
     * Allowed image MIME types and their extensions for product uploads.
     * Locked down to prevent arbitrary file uploads (e.g. .php) into the web root.
     */
    private const ALLOWED_IMAGE_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    private const ALLOWED_IMAGE_EXT  = ['jpg', 'jpeg', 'png', 'webp'];
    private const MAX_IMAGE_KB       = 2048; // 2 MB

    /**
     * Display the Daily Seafood Inventory
     */
    public function index()
    {
        $model = new ProductModel();
        
        $data = [
            'title'    => 'Daily Seafood Inventory',
            'products' => $model->getDailyInventory(),
            'username' => session()->get('username')
        ];

        return inertia('admin/Products', $data);
    }

    /**
     * Fetch all products as JSON for Vue
     */
    public function list()
    {
        if (session()->get('role') !== 'admin') {
            return $this->errorResponse('Access denied', 403);
        }

        $model = new ProductModel();
        $products = $model->getDailyInventory();

        return $this->successResponse($products);
    }

    /**
     * Save a new Daily Catch to the inventory
     */
    public function store()
    {
        if (session()->get('role') !== 'admin') {
            return $this->errorResponse('Access denied', 403);
        }

        $model = new ProductModel();
        $db = db_connect();
        
        $img = $this->request->getFile('image');
        $imageName = null;
        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'products';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        if ($img && $img->isValid() && ! $img->hasMoved()) {
            $validation = $this->_validateImage($img);
            if ($validation !== null) {
                return $this->errorResponse($validation, 400);
            }
            $imageName = $img->getRandomName();
            if (! $img->move($uploadDir, $imageName)) {
                return $this->errorResponse('Failed to upload image', 500);
            }
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'cost_price'    => $this->request->getPost('cost_price'),
            'selling_price' => $this->request->getPost('selling_price'),
            'unit'          => $this->request->getPost('unit'),
            'image'         => $imageName,
            'initial_stock' => $this->request->getPost('current_stock') ?: 100,
            'current_stock' => $this->request->getPost('current_stock') ?: 100,
        ];

        $db->transBegin();
        if (! $model->save($data)) {
            $db->transRollback();
            if ($this->request->isAJAX()) {
                return $this->errorResponse(implode(' ', $model->errors()), 400);
            }
            return redirect()->back()->with('error', implode(' ', $model->errors()))->withInput();
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->errorResponse('Failed to save product', 500);
        }
        $db->transCommit();
        
        if ($this->request->isAJAX()) {
            return $this->successResponse([], 'Seafood stock added successfully!');
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
            return $this->errorResponse('Access denied', 403);
        }

        $productModel = new ProductModel();
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found', 'token' => csrf_hash()])->setStatusCode(404);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Product fetched.', 'data' => $product, 'token' => csrf_hash()]);
    }

    /**
     * Validate an uploaded product image: type, extension, and size.
     * Returns null on success, or an error message string on failure.
     */
    private function _validateImage($img): ?string
    {
        // MIME type from the real file info (not the client-supplied claim)
        $mime = $img->getMimeType();
        if (! in_array($mime, self::ALLOWED_IMAGE_MIME, true)) {
            return 'Invalid image type. Allowed: JPG, PNG, WebP.';
        }

        // Extension from the client name (getRandomName() regenerates it anyway)
        $ext = strtolower($img->getClientExtension() ?: pathinfo($img->getClientName(), PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_IMAGE_EXT, true)) {
            return 'Invalid image extension. Allowed: jpg, jpeg, png, webp.';
        }

        if ($img->getSizeByUnit('kb') > self::MAX_IMAGE_KB) {
            return 'Image is too large. Maximum size is ' . self::MAX_IMAGE_KB . ' KB.';
        }

        return null;
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
        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'products';
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
            $validation = $this->_validateImage($img);
            if ($validation !== null) {
                return $this->response->setJSON(['status' => 'error', 'message' => $validation])->setStatusCode(400);
            }
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
            'unit'          => trim($this->request->getPost('unit')),
            'current_stock' => (float) $this->request->getPost('current_stock'),
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