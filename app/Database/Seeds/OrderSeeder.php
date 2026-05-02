<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Reset receipt data so the seeder is repeatable.
        $this->db->table('order_items')->truncate();
        $this->db->table('orders')->truncate();

        $orders = [
            [
                'transaction_code' => 'TXN-20260421-0001',
                'customer_name'    => 'Juan Dela Cruz',
                'total_amount'     => 650.00,
                'status'           => 'Completed',
                'notes'            => 'Walk-in customer',
                'created_at'       => Time::parse('2026-04-21 08:30:00'),
                'updated_at'       => Time::parse('2026-04-21 08:45:00'),
            ],
            [
                'transaction_code' => 'TXN-20260421-0002',
                'customer_name'    => 'Maria Santos',
                'total_amount'     => 1020.00,
                'status'           => 'Completed',
                'notes'            => 'Includes shrimp and tilapia',
                'created_at'       => Time::parse('2026-04-21 10:15:00'),
                'updated_at'       => Time::parse('2026-04-21 10:20:00'),
            ],
            [
                'transaction_code' => 'TXN-20260421-0003',
                'customer_name'    => 'Pedro Reyes',
                'total_amount'     => 420.00,
                'status'           => 'Pending',
                'notes'            => 'For later pickup',
                'created_at'       => Time::parse('2026-04-21 11:05:00'),
                'updated_at'       => Time::parse('2026-04-21 11:05:00'),
            ],
            [
                'transaction_code' => 'TXN-20260420-0004',
                'customer_name'    => 'Ana Lopez',
                'total_amount'     => 800.00,
                'status'           => 'Completed',
                'notes'            => 'Previous day sale',
                'created_at'       => Time::parse('2026-04-20 16:40:00'),
                'updated_at'       => Time::parse('2026-04-20 16:50:00'),
            ],
            [
                'transaction_code' => 'TXN-20260401-0005',
                'customer_name'    => 'Ramon Garcia',
                'total_amount'     => 560.00,
                'status'           => 'Completed',
                'notes'            => 'Monthly history sample',
                'created_at'       => Time::parse('2026-04-01 09:10:00'),
                'updated_at'       => Time::parse('2026-04-01 09:15:00'),
            ],
        ];

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

        $items = [
            // TXN-20260421-0001
            [
                'order_id'      => $orderIdByCode['TXN-20260421-0001'],
                'product_id'    => null,
                'product_name'  => 'Bangus',
                'unit'          => 'kg',
                'quantity'      => 5.00,
                'unit_price'    => 130.00,
                'subtotal'      => 650.00,
            ],
            // TXN-20260421-0002 (multi-item receipt)
            [
                'order_id'      => $orderIdByCode['TXN-20260421-0002'],
                'product_id'    => null,
                'product_name'  => 'Shrimp',
                'unit'          => 'kg',
                'quantity'      => 2.00,
                'unit_price'    => 260.00,
                'subtotal'      => 520.00,
            ],
            [
                'order_id'      => $orderIdByCode['TXN-20260421-0002'],
                'product_id'    => null,
                'product_name'  => 'Tilapia',
                'unit'          => 'kg',
                'quantity'      => 5.00,
                'unit_price'    => 100.00,
                'subtotal'      => 500.00,
            ],
            // TXN-20260421-0003
            [
                'order_id'      => $orderIdByCode['TXN-20260421-0003'],
                'product_id'    => null,
                'product_name'  => 'Galunggong',
                'unit'          => 'kg',
                'quantity'      => 3.50,
                'unit_price'    => 120.00,
                'subtotal'      => 420.00,
            ],
            // TXN-20260420-0004
            [
                'order_id'      => $orderIdByCode['TXN-20260420-0004'],
                'product_id'    => null,
                'product_name'  => 'Salmon',
                'unit'          => 'kg',
                'quantity'      => 2.00,
                'unit_price'    => 400.00,
                'subtotal'      => 800.00,
            ],
            // TXN-20260401-0005
            [
                'order_id'      => $orderIdByCode['TXN-20260401-0005'],
                'product_id'    => null,
                'product_name'  => 'Pusit',
                'unit'          => 'kg',
                'quantity'      => 4.00,
                'unit_price'    => 140.00,
                'subtotal'      => 560.00,
            ],
        ];

        $this->db->table('order_items')->insertBatch($items);
    }
}
