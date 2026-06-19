<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\OrderService;

class OrderServiceTest extends CIUnitTestCase
{
    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    public function testUpdateOrderStatusWithInvalidStatus()
    {
        $result = $this->orderService->updateOrderStatus(1, 'InvalidStatus');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Invalid order status', $result['message']);
    }

    public function testUpdateOrderStatusWithNonExistentOrder()
    {
        $result = $this->orderService->updateOrderStatus(99999, 'Processing');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Order not found', $result['message']);
    }

    public function testCancelOrderWithNonExistentOrder()
    {
        $result = $this->orderService->cancelOrder(99999, 'Test cancel');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Order not found', $result['message']);
    }

    public function testCancelOrderWithWrongCustomer()
    {
        $result = $this->orderService->cancelOrder(1, 'Test', 'wrong_customer');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Access denied', $result['message']);
    }

    public function testGetOrderWithDetailsNonExistent()
    {
        $result = $this->orderService->getOrderWithDetails(99999);
        $this->assertNull($result);
    }

    public function testUpdateTrackingNonExistentOrder()
    {
        $result = $this->orderService->updateTracking(99999, 'TRACK-001', 'J&T');
        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Order not found', $result['message']);
    }

    public function testValidStatuses()
    {
        $statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled', 'Refunded'];
        foreach ($statuses as $status) {
            $result = $this->orderService->updateOrderStatus(99999, $status);
            $this->assertFalse($result['ok']);
            $this->assertStringContainsString('Order not found', $result['message']);
        }
    }
}
