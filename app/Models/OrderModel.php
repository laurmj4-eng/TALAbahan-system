<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class OrderModel extends Model
{
    public const STATUS_PENDING   = 'Pending';
    public const STATUS_COMPLETED = 'Completed';

    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'transaction_code', 'customer_name', 'total_amount', 'status', 'notes'
    ];

    protected $validationRules = [
        'customer_name' => 'required|min_length[2]|max_length[120]',
        'total_amount'  => 'required|decimal|greater_than_equal_to[0]',
        'status'        => 'required|in_list[Pending,Completed]',
    ];

    protected $validationMessages = [
        'status' => [
            'in_list' => 'Order status must be Pending or Completed.',
        ],
    ];

    protected $beforeInsert = ['applyDefaultStatus'];

    /**
     * Fetch all orders with the count of items in each order.
     * This powers the main Order Tracking table.
     */
    public function getOrdersWithItemCount()
    {
        return $this->db->table('orders o')
            ->select('o.*, COUNT(oi.id) as item_count')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->groupBy('o.id')
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get a single order with all its line items.
     * Used for the detail/receipt modal.
     */
    public function getOrderWithItems($orderId)
    {
        $order = $this->find($orderId);
        if (!$order) return null;

        $order['items'] = $this->db->table('order_items')
            ->where('order_id', $orderId)
            ->get()
            ->getResultArray();

        return $order;
    }

    /**
     * Get today's revenue (sum of completed orders).
     */
    public function getTodayRevenue()
    {
        $result = $this->selectSum('total_amount')
            ->where('status', self::STATUS_COMPLETED)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->first();
        return $result['total_amount'] ?? 0;
    }

    /**
     * Sum completed orders for the given date (YYYY-mm-dd).
     */
    public function getDailyRevenue(string $date): float
    {
        $result = $this->selectSum('total_amount')
            ->where('status', self::STATUS_COMPLETED)
            ->where('DATE(created_at)', $date)
            ->first();

        return (float) ($result['total_amount'] ?? 0);
    }

    /**
     * Sum completed orders for the given month (YYYY-mm).
     */
    public function getMonthlyRevenue(string $yearMonth): float
    {
        $result = $this->selectSum('total_amount')
            ->where('status', self::STATUS_COMPLETED)
            ->where("DATE_FORMAT(created_at, '%Y-%m')", $yearMonth)
            ->first();

        return (float) ($result['total_amount'] ?? 0);
    }

    /**
     * Get count of orders by status.
     */
    public function getCountByStatus($status)
    {
        return $this->where('status', $status)->countAllResults();
    }

    protected function applyDefaultStatus(array $data): array
    {
        if (!isset($data['data']['status']) || $data['data']['status'] === '') {
            $data['data']['status'] = self::STATUS_PENDING;
        }

        return $data;
    }

    /**
     * Creates order header + line items + stock deductions atomically.
     */
    public function createFromCheckout(array $payload): array
    {
        $productModel   = new ProductModel();
        $orderItemModel = new OrderItemModel();
        $salesModel     = new SalesModel();
        $paymentModel   = new PaymentModel();
        $movementModel  = new InventoryMovementModel();
        $deliveryModel  = new DeliveryModel();

        $customerName = trim((string) ($payload['customer_name'] ?? 'Walk-in Customer'));
        $cartItems     = $payload['items'] ?? [];

        if ($cartItems === []) {
            return ['ok' => false, 'message' => 'Cart is empty.'];
        }

        $lineItems   = [];
        $itemsSummary = [];
        $totalAmount = 0.0;

        foreach ($cartItems as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $qty       = (float) ($item['qty'] ?? 0);
            if ($productId <= 0 || $qty <= 0) {
                return ['ok' => false, 'message' => 'Invalid product/quantity detected.'];
            }

            $product = $productModel->getSellableById($productId);
            if (! $product) {
                return ['ok' => false, 'message' => 'One of the products is unavailable.'];
            }

            $currentStock = (float) ($product['current_stock'] ?? 0);
            if ($currentStock < $qty) {
                return ['ok' => false, 'message' => "Insufficient stock for {$product['name']}."];
            }

            $unitPrice = (float) $product['selling_price'];
            $subtotal  = round($unitPrice * $qty, 2);
            $totalAmount += $subtotal;

            $lineItems[] = [
                'product_id'   => $productId,
                'product_name' => $product['name'],
                'unit'         => $product['unit'] ?? 'piece',
                'quantity'     => $qty,
                'unit_price'   => $unitPrice,
                'subtotal'     => $subtotal,
            ];

            $itemsSummary[] = $qty . 'x ' . $product['name'];
        }

        $transactionCode = 'TXN-' . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));
        $paymentMethod   = trim((string) ($payload['payment_method'] ?? 'Cash'));
        $movedBy         = (int) ($payload['moved_by'] ?? 0);
        $needsDelivery   = (bool) ($payload['needs_delivery'] ?? false);

        $db = db_connect();
        $db->transBegin();

        try {
            $inserted = $this->insert([
                'transaction_code' => $transactionCode,
                'customer_name'    => $customerName === '' ? 'Walk-in Customer' : $customerName,
                'total_amount'     => round($totalAmount, 2),
                'status'           => self::STATUS_COMPLETED,
            ]);

            if ($inserted === false) {
                throw new Exception('Failed to create order header.');
            }

            $orderId = (int) $this->getInsertID();
            if (! $orderItemModel->createBatchForOrder($orderId, $lineItems)) {
                throw new Exception('Failed to create order line items.');
            }

            foreach ($lineItems as $line) {
                if (! $productModel->reduceStock((int) $line['product_id'], (float) $line['quantity'])) {
                    throw new Exception("Failed to update stock for {$line['product_name']}.");
                }

                $movementModel->logMovement([
                    'product_id'      => (int) $line['product_id'],
                    'movement_type'   => InventoryMovementModel::TYPE_OUT,
                    'quantity'        => (float) $line['quantity'],
                    'reference_type'  => 'order',
                    'reference_id'    => $orderId,
                    'notes'           => 'Stock OUT for order ' . $transactionCode,
                    'moved_by'        => $movedBy ?: null,
                ]);
            }

            $salesModel->recordFromOrder($transactionCode, $itemsSummary, round($totalAmount, 2));
            $paymentModel->recordForOrder($orderId, round($totalAmount, 2), $paymentMethod, PaymentModel::STATUS_PAID);

            if ($needsDelivery) {
                $deliveryModel->insert([
                    'order_id'   => $orderId,
                    'rider_name' => $payload['rider_name'] ?? null,
                    'route_note' => $payload['route_note'] ?? 'Auto-created at checkout',
                    'eta_at'     => $payload['eta_at'] ?? null,
                    'status'     => DeliveryModel::STATUS_SCHEDULED,
                    'notes'      => 'Generated from checkout transaction.',
                ]);
            }
        } catch (Exception $e) {
            $db->transRollback();
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return ['ok' => false, 'message' => 'Transaction failed while saving checkout.'];
        }

        $db->transCommit();

        return [
            'ok'               => true,
            'transaction_code' => $transactionCode,
            'total_amount'     => round($totalAmount, 2),
        ];
    }
}