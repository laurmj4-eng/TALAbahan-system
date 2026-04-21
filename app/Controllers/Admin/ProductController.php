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
        
        $data = [
            'name'          => $this->request->getPost('name'),
            'cost_price'    => $this->request->getPost('cost_price'),
            'selling_price' => $this->request->getPost('selling_price'),
            'initial_stock' => $this->request->getPost('quantity'),
            'current_stock' => $this->request->getPost('quantity'), // At morning, current stock equals initial
            'unit'          => $this->request->getPost('unit'),
        ];

        if (! $model->save($data)) {
            return redirect()->back()->with('error', implode(' ', $model->errors()))->withInput();
        }
        
        // Redirect back to the inventory list
        return redirect()->to('/admin/products')->with('msg', 'Seafood stock added successfully!');
    }
}