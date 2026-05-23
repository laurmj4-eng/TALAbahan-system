# Cancel Order Update: Allow Pending and Unpaid Cancellations

## Summary

This change updates the customer cancellation flow so orders with status `Pending` or `Unpaid` can now be cancelled. Previously the backend incorrectly blocked `Pending` orders and returned a `400 Bad Request` with the code `CANCELLATION_NOT_ALLOWED`.

## What changed

1. Backend cancel endpoint now allows cancellations when order status is `Pending` or `Unpaid`.
2. The order is still updated to `Cancelled` in the database during the cancellation transaction.
3. Frontend order action logic now shows the Cancel button for `Pending` and `Unpaid` orders.
4. The order lifecycle state now reports `can_cancel` correctly for `Pending` and `Unpaid`.

## Updated files

- `app/Controllers/Customer/Orders.php`
- `app/Views/customer/order_items.php`

## Controller changes

### `app/Controllers/Customer/Orders.php`

#### `cancelOrder()` eligibility check

```php
// Step 2: Verify order is eligible for cancellation in the current state
$eligibleStatuses = ['Pending', 'Unpaid'];
if (! in_array($order['status'], $eligibleStatuses, true)) {
    log_message('warning', "Cannot cancel - Wrong status - Order Status: {$order['status']}");
    return $this->response
        ->setJSON([
            'status' => 'error',
            'message' => "Cannot cancel order with status '{$order['status']}'",
            'code' => 'CANCELLATION_NOT_ALLOWED',
            'current_status' => $order['status'],
            'support_message' => 'Please contact seller support if you need to cancel this order'
        ])
        ->setStatusCode(400);
}

// No time restrictions - customers can cancel Pending or Unpaid orders anytime
log_message('info', "Cancellation eligibility verified - Order {$orderId} can be cancelled");
```

#### `validateCancelEligibility()`

```php
// ===== FAST ELIGIBILITY CHECK =====
// Customers can cancel Pending or Unpaid orders anytime without time restrictions
$canCancel = in_array($order['status'], ['Pending', 'Unpaid'], true);
```

#### `buildLifecycleState()`

```php
return [
    'stage_key' => $stageKey,
    'payment_deadline_at' => date('Y-m-d H:i:s', $paymentDeadlineTs),
    'cancel_deadline_at' => date('Y-m-d H:i:s', $cancelDeadlineTs),
    'refund_deadline_at' => $refundDeadlineTs ? date('Y-m-d H:i:s', $refundDeadlineTs) : null,
    'actions' => [
        'can_pay_now' => ($status === OrderModel::STATUS_PENDING || $status === 'Unpaid')
            && $paymentMethod !== 'COD'
            && in_array($paymentStatus, ['unpaid', 'failed', 'pending_confirmation'], true),
        'can_cancel' => in_array($status, [OrderModel::STATUS_PENDING, 'Unpaid'], true),
        'can_track' => $status === OrderModel::STATUS_SHIPPED && trim((string) ($order['tracking_number'] ?? '')) !== '',
        'can_review' => $status === OrderModel::STATUS_COMPLETED && ! $hasReview,
        'can_refund_request' => $status === OrderModel::STATUS_COMPLETED
            && ! $hasOpenRefund
            && ($refundDeadlineTs === null || $now <= $refundDeadlineTs),
        'can_contact_seller' => in_array($status, [OrderModel::STATUS_PROCESSING, OrderModel::STATUS_SHIPPED], true),
    ],
];
```

## Frontend change

### `app/Views/customer/order_items.php`

```php
<?php if (in_array($o['status'], ['Pending', 'Unpaid'], true)): ?>
    <button class="btn-action btn-cancel" onclick="cancelOrder(<?= $o['id'] ?>)">
        <i class="fas fa-times"></i> Cancel
    </button>
<?php endif; ?>
```

## Result

- Customers can now cancel orders when status is `Pending` or `Unpaid`.
- Cancellation still updates the order status to `Cancelled` and returns `200 OK` on success.
- The Cancel button is visible for eligible orders in the customer order list.
