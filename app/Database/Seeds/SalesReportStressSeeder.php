<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class SalesReportStressSeeder extends Seeder
{
    public function run()
    {
        // Reset order data so each run has a fresh 30-day dataset.
        $this->db->table('order_items')->truncate();
        $this->db->table('orders')->truncate();

        $customerNames = [
            'Juan Dela Cruz',
            'Maria Santos',
            'Pedro Reyes',
            'Ana Lopez',
            'Ramon Garcia',
            'Liza Manalo',
            'Paolo Mendoza',
            'Karen Bautista',
        ];

        $catalog = [
            ['name' => 'Shrimp',     'unit' => 'kg', 'min' => 240.00, 'max' => 290.00],
            ['name' => 'Tilapia',    'unit' => 'kg', 'min' => 95.00,  'max' => 130.00],
            ['name' => 'Bangus',     'unit' => 'kg', 'min' => 120.00, 'max' => 160.00],
            ['name' => 'Galunggong', 'unit' => 'kg', 'min' => 95.00,  'max' => 125.00],
            ['name' => 'Salmon',     'unit' => 'kg', 'min' => 380.00, 'max' => 500.00],
            ['name' => 'Pusit',      'unit' => 'kg', 'min' => 130.00, 'max' => 180.00],
        ];

        $orders = [];
        $items  = [];

        $now = Time::now();
        $transactionCounter = 1;

        for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
            $day = $now->subDays($dayOffset);
            $ordersToday = random_int(6, 16);

            for ($i = 0; $i < $ordersToday; $i++) {
                $hour   = random_int(7, 18);
                $minute = random_int(0, 59);
                $second = random_int(0, 59);

                $createdAt = Time::parse($day->toDateString() . sprintf(' %02d:%02d:%02d', $hour, $minute, $second));
                $status    = random_int(1, 100) <= 82 ? 'Completed' : 'Pending';

                $lineItemCount = random_int(1, 4);
                $orderItems    = [];
                $totalAmount   = 0.0;

                for ($j = 0; $j < $lineItemCount; $j++) {
                    $product  = $catalog[array_rand($catalog)];
                    $price    = $this->randomMoney($product['min'], $product['max']);
                    $quantity = $this->randomMoney(0.5, 6.0);
                    $subtotal = round($price * $quantity, 2);

                    $orderItems[] = [
                        'product_id'   => null,
                        'product_name' => $product['name'],
                        'unit'         => $product['unit'],
                        'quantity'     => $quantity,
                        'unit_price'   => $price,
                        'subtotal'     => $subtotal,
                    ];

                    $totalAmount += $subtotal;
                }

                $transactionCode = sprintf('STRESS-%s-%04d', $day->toDateString(), $transactionCounter++);

                $orders[] = [
                    'transaction_code' => $transactionCode,
                    'customer_name'    => $customerNames[array_rand($customerNames)],
                    'total_amount'     => round($totalAmount, 2),
                    'status'           => $status,
                    'notes'            => 'Auto-generated stress test order',
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ];

                $items[$transactionCode] = $orderItems;
            }
        }

        if ($orders === []) {
            return;
        }

        $this->db->table('orders')->insertBatch($orders);

        $insertedOrders = $this->db->table('orders')
            ->select('id, transaction_code')
            ->whereIn('transaction_code', array_column($orders, 'transaction_code'))
            ->get()
            ->getResultArray();

        $orderIdByCode = [];
        foreach ($insertedOrders as $row) {
            $orderIdByCode[$row['transaction_code']] = (int) $row['id'];
        }

        $flatItems = [];
        foreach ($items as $transactionCode => $orderItems) {
            if (! isset($orderIdByCode[$transactionCode])) {
                continue;
            }

            foreach ($orderItems as $line) {
                $line['order_id'] = $orderIdByCode[$transactionCode];
                $flatItems[]      = $line;
            }
        }

        if ($flatItems !== []) {
            $this->db->table('order_items')->insertBatch($flatItems);
        }
    }

    private function randomMoney(float $min, float $max): float
    {
        return round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), 2);
    }
}
