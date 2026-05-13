<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class Products extends BaseController
{
    /**
     * View All Products (Staff - View Only)
     */
    public function index()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $productModel = new ProductModel();
        $data = [
            'title'    => 'Product Inventory - Staff',
            'username' => session()->get('username'),
            'products' => $productModel->getWithCategory(),
        ];

        return inertia('staff/Products', $data);
    }

    /**
     * Get Products (JSON for table)
     */
    public function getProducts()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Products fetched.', 'data' => $productModel->getWithCategory(), 'token' => csrf_hash()]);
    }

    /**
     * Get Product Details (JSON)
     */
    public function getDetails($productId)
    {
        if (session()->get('role') !== 'staff') {
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
     * Get Low Stock Products
     */
    public function getLowStockProducts()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $lowStock = $productModel->getLowStockProducts(10);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Low-stock products fetched.', 'data' => $lowStock, 'token' => csrf_hash()]);
    }

    /**
     * Get Best Sellers
     */
    public function getBestSellers()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $bestSellers = $productModel->getBestSellers(10);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Best sellers fetched.', 'data' => $bestSellers, 'token' => csrf_hash()]);
    }
}
