<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\OrderReviewModel;
use App\Models\ProductModel;
use App\Models\ShippingLocationModel;
use App\Models\SettingsModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Fetch models
        $productModel = new ProductModel();
        $shippingModel = new ShippingLocationModel();
        $orderItemModel = new OrderItemModel();
        $reviewModel = new OrderReviewModel();
        $settingsModel = new SettingsModel();

        $customerName = (string) session()->get('username');
        $orderCounts = $this->getCustomerOrderCounts($customerName);
        $activeOrdersCount = (int) ($orderCounts['to_pay'] + $orderCounts['to_ship'] + $orderCounts['to_receive']);

        // 3. Fetch products from cache or DB
        $cache = \Config\Services::cache();
        $products = $cache->get('customer_dashboard_products');

        if ($products === null) {
            $products = $productModel->findAll();
            
            foreach ($products as &$p) {
                // Typecast status/visibility to integer for strict JS comparison
                $p['is_available'] = isset($p['is_available']) ? (int)$p['is_available'] : 0;
                
                // Read from our new background aggregated columns
                $p['real_sold_count'] = (int) ($p['real_sold_count'] ?? 0);
                $p['real_rating'] = $p['real_rating'] !== null ? round((float)$p['real_rating'], 1) : null;
            }

            $cache->save('customer_dashboard_products', $products, 60);
        }

        // 4. Prepare data for the view
        $data =[
            'title'             => 'Customer Portal',
            'username'          => session()->get('username'),
            'products'          => $products,
            'shippingLocations' => $shippingModel->where('is_active', 1)->findAll(),
            'ship_to_all'       => $settingsModel->getSetting('ship_to_all', '0'),
            'orderCounts'       => $orderCounts,
            'activeOrdersCount' => $activeOrdersCount,
            'isAJAX'            => $this->request->isAJAX(),
        ];

        // 5. Load the customer dashboard view using Inertia
        return inertia('customer/Dashboard', $data);
    }

    public function getData()
    {
        $cache = \Config\Services::cache();
        $products = $cache->get('customer_dashboard_products');

        if ($products === null) {
            $productModel = new \App\Models\ProductModel();
            $products = $productModel->findAll();
            
            foreach ($products as &$p) {
                // Typecast status/visibility to integer for strict JS comparison
                $p['is_available'] = isset($p['is_available']) ? (int)$p['is_available'] : 0;
                
                // Read from our new background aggregated columns
                $p['real_sold_count'] = (int) ($p['real_sold_count'] ?? 0);
                $p['real_rating'] = $p['real_rating'] !== null ? round((float)$p['real_rating'], 1) : null;
            }

            $cache->save('customer_dashboard_products', $products, 60);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'products' => $products
        ]);
    }

    public function getCustomerOrderCounts(string $customerName): array
    {
        $orderModel = new OrderModel();

        $all = (int) $orderModel
            ->where('customer_name', $customerName)
            ->countAllResults();

        $toPayQuery = (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_PENDING);
        
        $db = db_connect();
        if ($db->fieldExists('payment_status', 'orders')) {
            $toPayQuery->whereIn('payment_status', ['unpaid', 'failed', 'pending_confirmation']);
        }
        $toPay = (int) $toPayQuery->countAllResults();

        $toShip = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_PROCESSING)
            ->countAllResults();

        $toReceive = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_SHIPPED)
            ->countAllResults();

        $completed = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_COMPLETED)
            ->countAllResults();

        $cancelled = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_CANCELLED)
            ->countAllResults();

        return [
            'all' => $all,
            'to_pay' => $toPay,
            'to_ship' => $toShip,
            'to_receive' => $toReceive,
            'completed' => $completed,
            'cancelled' => $cancelled,
        ];
    }

    public function details($id)
    {
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $productModel = new ProductModel();
        $product = $productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Product with ID $id not found");
        }

        // Prepare data with fallbacks for UI richness
        $data = [
            'product' => [
                'id' => (int)$product['id'],
                'name' => $product['name'],
                'description' => $product['description'] ?? 'Experience the ultimate seafood delight. A rich, deep flavor profile infused with our signature spices and a hint of smoky essence, crafted to perfection.',
                'price' => (float)$product['selling_price'],
                'unit' => $product['unit'] ?? 'Per serving',
                'image' => $product['image'] ?? '',
                'category' => $product['category'] ?? 'Premium',
                'prep_style' => $product['prep_style'] ?? 'Kinilaw / Grilled',
                'flavor_notes' => $product['flavor_notes'] ?? 'Spicy, Citrusy, Savory',
                'portion_size' => $product['portion_size'] ?? 'Good for 2-3 persons',
                'stock_status' => ((int)$product['is_available'] === 1) ? 'available' : 'out_of_stock'
            ]
        ];

        return inertia('customer/ProductDetails', $data);
    }
}
