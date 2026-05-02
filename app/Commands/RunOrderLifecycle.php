<?php

namespace App\Commands;

use App\Models\OrderModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunOrderLifecycle extends BaseCommand
{
    protected $group = 'Orders';
    protected $name = 'orders:run-lifecycle';
    protected $description = 'Auto-cancel unpaid orders and auto-complete aging shipped orders.';

    public function run(array $params)
    {
        $paymentTimeoutMinutes = (int) (env('orderLifecycle.paymentTimeoutMinutes') ?: 30);
        $autoCompleteDays = (int) (env('orderLifecycle.autoCompleteDays') ?: 3);
        $now = date('Y-m-d H:i:s');

        $orderModel = new OrderModel();

        $unpaidCutoff = date('Y-m-d H:i:s', strtotime("-{$paymentTimeoutMinutes} minutes"));
        $unpaidOrders = $orderModel
            ->where('status', OrderModel::STATUS_PENDING)
            ->where('payment_method !=', 'COD')
            ->whereIn('payment_status', ['unpaid', 'failed', 'pending_confirmation'])
            ->where('created_at <=', $unpaidCutoff)
            ->findAll();

        $cancelled = 0;
        foreach ($unpaidOrders as $order) {
            if ($orderModel->update((int) $order['id'], [
                'status' => OrderModel::STATUS_CANCELLED,
                'cancel_reason' => 'Auto-cancelled due to unpaid timeout.',
            ])) {
                $cancelled++;
            }
        }

        $completedCutoff = date('Y-m-d H:i:s', strtotime("-{$autoCompleteDays} days"));
        $toComplete = $orderModel
            ->where('status', OrderModel::STATUS_SHIPPED)
            ->groupStart()
                ->where('delivered_at IS NOT NULL', null, false)
                ->orWhere('shipped_at IS NOT NULL', null, false)
            ->groupEnd()
            ->groupStart()
                ->where('delivered_at <=', $completedCutoff)
                ->orWhere('shipped_at <=', $completedCutoff)
            ->groupEnd()
            ->findAll();

        $completed = 0;
        foreach ($toComplete as $order) {
            if ($orderModel->update((int) $order['id'], [
                'status' => OrderModel::STATUS_COMPLETED,
                'delivered_at' => $order['delivered_at'] ?? $now,
                'payment_status' => strtoupper((string) ($order['payment_method'] ?? 'COD')) === 'COD'
                    ? 'paid'
                    : ($order['payment_status'] ?? 'paid'),
            ])) {
                $completed++;
            }
        }

        CLI::write("Lifecycle done. Auto-cancelled: {$cancelled}; Auto-completed: {$completed}", 'green');
    }
}
