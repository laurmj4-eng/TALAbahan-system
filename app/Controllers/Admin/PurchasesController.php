<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PurchaseModel;
use App\Models\ProductModel;

class PurchasesController extends BaseController
{
    private function hasRequiredTables(): bool
    {
        $db = db_connect();
        return $db->tableExists('purchases')
            && $db->tableExists('purchase_items')
            && $db->tableExists('inventory_movements');
    }

    public function index()
    {
        if (! $this->hasRequiredTables()) {
            return view('admin/purchases_view', [
                'purchases' => [],
                'products'  => [],
                'error'     => 'Purchase module tables are missing. Run: php spark migrate',
            ]);
        }

        $model = new PurchaseModel();
        $productModel = new ProductModel();
        $q = trim((string) $this->request->getGet('q'));
        $from = trim((string) $this->request->getGet('from'));
        $to = trim((string) $this->request->getGet('to'));
        $status = trim((string) $this->request->getGet('status'));
        $builder = $model->orderBy('created_at', 'DESC');
        if ($q !== '') {
            $builder = $builder->groupStart()->like('reference_no', $q)->orLike('supplier_name', $q)->groupEnd();
        }
        if ($from !== '') {
            $builder = $builder->where('purchase_date >=', $from);
        }
        if ($to !== '') {
            $builder = $builder->where('purchase_date <=', $to);
        }
        if ($status !== '') {
            $builder = $builder->where('status', $status);
        }
        return view('admin/purchases_view', [
            'purchases' => $builder->paginate(10),
            'pager'     => $model->pager,
            'filters'   => compact('q', 'from', 'to', 'status'),
            'products'  => $productModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function receive()
    {
        if (! $this->hasRequiredTables()) {
            return redirect()->to('/admin/purchases')->with('error', 'Purchase module tables are missing. Run: php spark migrate');
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $productIds = (array) $this->request->getPost('product_id');
            $quantities = (array) $this->request->getPost('quantity');
            $unitCosts  = (array) $this->request->getPost('unit_cost');
            $items      = [];
            foreach ($productIds as $idx => $pid) {
                if (! $pid) {
                    continue;
                }
                $items[] = [
                    'product_id' => (int) $pid,
                    'quantity'   => (float) ($quantities[$idx] ?? 0),
                    'unit_cost'  => (float) ($unitCosts[$idx] ?? 0),
                ];
            }
            $payload = [
                'reference_no'  => $this->request->getPost('reference_no'),
                'supplier_name' => $this->request->getPost('supplier_name'),
                'purchase_date' => $this->request->getPost('purchase_date'),
                'notes'         => $this->request->getPost('notes'),
                'items'         => $items,
            ];
        }

        $payload['moved_by'] = (int) (session()->get('user_id') ?? 0);

        $model  = new PurchaseModel();
        $result = $model->createReceivedPurchase($payload);
        if (! $result['ok']) {
            return redirect()->back()->with('error', $result['message'])->withInput();
        }

        return redirect()->to('/admin/purchases')->with('msg', 'Purchase received: ' . $result['reference_no']);
    }
}
