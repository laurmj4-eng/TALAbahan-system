<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\CheckoutService;

class CheckoutServiceTest extends CIUnitTestCase
{
    protected CheckoutService $checkoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutService = new CheckoutService();
    }

    public function testBuildCheckoutQuoteWithEmptyCart()
    {
        $result = $this->checkoutService->buildCheckoutQuote(
            ['items' => []],
            'testuser'
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Cart is empty', $result['message']);
    }

    public function testBuildCheckoutQuoteWithNoItems()
    {
        $result = $this->checkoutService->buildCheckoutQuote(
            [],
            'testuser'
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Cart is empty', $result['message']);
    }

    public function testBuildCheckoutQuoteWithInvalidPhone()
    {
        $result = $this->checkoutService->buildCheckoutQuote(
            [
                'items' => [['id' => 1, 'quantity' => 1]],
                'shipping_details' => [
                    'barangay' => 'Test Barangay',
                    'phone' => '123',
                    'name' => 'Test User',
                ],
                'payment_method' => 'COD',
            ],
            'testuser'
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Phone number', $result['message']);
    }

    public function testBuildCheckoutQuoteWithInvalidPaymentMethod()
    {
        $result = $this->checkoutService->buildCheckoutQuote(
            [
                'items' => [['id' => 1, 'quantity' => 1]],
                'shipping_details' => [
                    'barangay' => 'Test Barangay',
                    'phone' => '09123456789',
                    'name' => 'Test User',
                ],
                'payment_method' => 'BITCOIN',
            ],
            'testuser'
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Unsupported payment method', $result['message']);
    }

    public function testBuildCheckoutQuoteWithEmptyBarangay()
    {
        $result = $this->checkoutService->buildCheckoutQuote(
            [
                'items' => [['id' => 1, 'quantity' => 1]],
                'shipping_details' => [
                    'barangay' => '',
                    'phone' => '09123456789',
                    'name' => 'Test User',
                ],
                'payment_method' => 'COD',
            ],
            'testuser'
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Shipping location required', $result['message']);
    }

    public function testBuildCheckoutQuoteWithInvalidItemQuantity()
    {
        $result = $this->checkoutService->buildCheckoutQuote(
            [
                'items' => [['id' => 1, 'quantity' => 0]],
                'shipping_details' => [
                    'barangay' => 'Test Barangay',
                    'phone' => '09123456789',
                    'name' => 'Test User',
                ],
                'payment_method' => 'COD',
            ],
            'testuser'
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Invalid product or quantity', $result['message']);
    }
}
