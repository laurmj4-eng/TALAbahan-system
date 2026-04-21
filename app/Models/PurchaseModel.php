<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class PurchaseModel extends Model
{
    public const STATUS_DRAFT    = 'Draft';
    public const STATUS_RECEIVED = 'Received';

    protected $table            = 'purchases';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'reference_no',
        'supplier_name',
        'purchase_date',
        'total_cost',
        'status',
        'notes',
    ];

    protected $validationRules = [
        'reference_no'  => 'required|max_length[50]',
        'supplier_name' => 'required|min_length[2]|max_length[120]',
        'purchase_date' => 'required|valid_date[Y-m-d]',
        'total_cost'    => 'required|decimal|greater_than_equal_to[0]',
        'status'        => 'required|in_list[Draft,Received]',
    ];

    /**
     * Creates purchase + purchase items + stock IN movements in one transaction.
     */
    public function createReceivedPurchase(array $payload): array
    {
        $productModel    = new ProductModel();
        $purchaseItem    = new PurchaseItemModel();
        $movementModel   = new InventoryMovementModel();
        $supplierName    = trim((string) ($payload['supplier_name'] ?? 'Unknown Supplier'));
        $items           = $payload['items'] ?? [];
        $movedBy         = (int) ($payload['moved_by'] ?? 0);

        if ($items === []) {
            return ['ok' => false, 'message' => 'Purchase items are required.'];
        }

        $preparedItems = [];
        $totalCost     = 0.0;

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $qty       = (float) ($item['quantity'] ?? 0);
            $unitCost  = (float) ($item['unit_cost'] ?? 0);
            if ($productId <= 0 || $qty <= 0 || $unitCost < 0) {
                return ['ok' => false, 'message' => 'Invalid purchase item payload.'];
            }

            $product = $productModel->find($productId);
            if (! $product) {
                return ['ok' => false, 'message' => "Product #{$productId} was not found."];
            }

            $subtotal    = round($qty * $unitCost, 2);
            $totalCost  += $subtotal;
            $preparedItems[] = [
                'product_id'   => $productId,
                'product_name' => $product['name'],
                'unit'         => $product['unit'] ?? 'kg',
                'quantity'     => $qty,
                'unit_cost'    => $unitCost,
                'subtotal'     => $subtotal,
            ];
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $referenceNo = $payload['reference_no'] ?? ('PO-' . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4)));
            $inserted = $this->insert([
                'reference_no'  => $referenceNo,
                'supplier_name' => $supplierName === '' ? 'Unknown Supplier' : $supplierName,
                'purchase_date' => $payload['purchase_date'] ?? date('Y-m-d'),
                'total_cost'    => round($totalCost, 2),
                'status'        => self::STATUS_RECEIVED,
                'notes'         => $payload['notes'] ?? null,
            ]);

            if ($inserted === false) {
                throw new Exception('Failed to create purchase header.');
            }

            $purchaseId = (int) $this->getInsertID();
            foreach ($preparedItems as &$row) {
                $row['purchase_id'] = $purchaseId;
            }
            unset($row);

            if ($purchaseItem->insertBatch($preparedItems) === false) {
                throw new Exception('Failed to create purchase items.');
            }

            foreach ($preparedItems as $line) {
                $product = $productModel->find((int) $line['product_id']);
                $newStock = round(((float) $product['current_stock']) + (float) $line['quantity'], 2);
                $productModel->update((int) $line['product_id'], [
                    'current_stock' => $newStock,
                    'cost_price'    => (float) $line['unit_cost'],
                ]);

                $movementModel->logMovement([
                    'product_id'      => (int) $line['product_id'],
                    'movement_type'   => InventoryMovementModel::TYPE_IN,
                    'quantity'        => (float) $line['quantity'],
                    'reference_type'  => 'purchase',
                    'reference_id'    => $purchaseId,
                    'notes'           => 'Inbound from purchase ' . $referenceNo,
                    'moved_by'        => $movedBy ?: null,
                ]);
            }
        } catch (Exception $e) {
            $db->transRollback();
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return ['ok' => false, 'message' => 'Purchase transaction failed.'];
        }

        $db->transCommit();

        return [
            'ok'           => true,
            'purchase_id'  => $purchaseId,
            'reference_no' => $referenceNo,
            'total_cost'   => round($totalCost, 2),
        ];
    }
}
