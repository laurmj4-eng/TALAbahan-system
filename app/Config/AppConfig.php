<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppConfig extends BaseConfig
{
    public const ORDER_REMORSE_WINDOW_MINUTES = 60;
    public const LOW_STOCK_THRESHOLD = 10;
    public const DEFAULT_SHIPPING_FEE = 49;
    
    public array $paymentMethods = [
        'COD' => 'Cash on Delivery',
        'GCASH' => 'GCash'
    ];

    public array $orderStatuses = [
        'Pending' => 'Pending',
        'Processing' => 'Processing',
        'Shipped' => 'Shipped',
        'Completed' => 'Completed',
        'Cancelled' => 'Cancelled',
        'Refunded' => 'Refunded'
    ];

    public array $refundStatuses = [
        'Pending' => 'Pending',
        'Under Review' => 'Under Review',
        'Approved' => 'Approved',
        'Rejected' => 'Rejected'
    ];

    public array $userRoles = [
        'admin' => 'Administrator',
        'staff' => 'Staff',
        'customer' => 'Customer'
    ];
}
