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
            'title'     => 'TALAbahan Terminal (Staff)',
            'username'  => session()->get('username'),
            'products'  => $productModel->findAll(),
            'customers' => $userModel->where('role', 'customer')->findAll(),
        ];

        return inertia('staff/Pos', $data);
    }
}
