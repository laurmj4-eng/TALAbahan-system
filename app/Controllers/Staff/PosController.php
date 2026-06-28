<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Controllers\Traits\PosOperationsTrait;
use App\Models\ProductModel;

class PosController extends BaseController
{
    use PosOperationsTrait;

    public function index()
    {
        $productModel = new ProductModel();
        $userModel = new \App\Models\UserModel();

        $data = [
            'title'     => 'MJ Talabahan Terminal (Staff)',
            'username'  => session()->get('username'),
            'products'  => $productModel->orderBy('name', 'ASC')->findAll(500),
            'customers' => $userModel->where('role', 'customer')->orderBy('username', 'ASC')->findAll(200),
        ];

        return inertia('staff/Pos', $data);
    }
}
