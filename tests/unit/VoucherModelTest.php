<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\VoucherModel;

class VoucherModelTest extends CIUnitTestCase
{
    protected VoucherModel $voucherModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->voucherModel = new VoucherModel();
    }

    public function testComputeDiscountFixedType()
    {
        $voucher = [
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'max_discount' => null,
        ];

        $result = $this->voucherModel->computeDiscount($voucher, 200.0);
        $this->assertEquals(50.0, $result);
    }

    public function testComputeDiscountPercentType()
    {
        $voucher = [
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_discount' => null,
        ];

        $result = $this->voucherModel->computeDiscount($voucher, 200.0);
        $this->assertEquals(20.0, $result);
    }

    public function testComputeDiscountRespectsMaxDiscount()
    {
        $voucher = [
            'discount_type' => 'percent',
            'discount_value' => 50,
            'max_discount' => 25.0,
        ];

        $result = $this->voucherModel->computeDiscount($voucher, 200.0);
        $this->assertEquals(25.0, $result);
    }

    public function testComputeDiscountFixedWithMax()
    {
        $voucher = [
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'max_discount' => 75.0,
        ];

        $result = $this->voucherModel->computeDiscount($voucher, 200.0);
        $this->assertEquals(75.0, $result);
    }

    public function testComputeDiscountNeverNegative()
    {
        $voucher = [
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'max_discount' => null,
        ];

        $result = $this->voucherModel->computeDiscount($voucher, 200.0);
        $this->assertGreaterThanOrEqual(0.0, $result);
    }

    public function testPickBestByScopeReturnsCorrectStructure()
    {
        $vouchers = [
            [
                'id' => 1,
                'scope' => 'platform',
                'discount_type' => 'fixed',
                'discount_value' => 10,
                'max_discount' => null,
            ],
            [
                'id' => 2,
                'scope' => 'shop',
                'discount_type' => 'percent',
                'discount_value' => 5,
                'max_discount' => null,
            ],
        ];

        $result = $this->voucherModel->pickBestByScope($vouchers, 100.0);

        $this->assertArrayHasKey('platform', $result);
        $this->assertArrayHasKey('shop', $result);
        $this->assertNotNull($result['platform']);
        $this->assertNotNull($result['shop']);
        $this->assertEquals(1, $result['platform']['id']);
        $this->assertEquals(2, $result['shop']['id']);
    }

    public function testPickBestByScopePicksHighestDiscount()
    {
        $vouchers = [
            [
                'id' => 1,
                'scope' => 'platform',
                'discount_type' => 'fixed',
                'discount_value' => 10,
                'max_discount' => null,
            ],
            [
                'id' => 2,
                'scope' => 'platform',
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'max_discount' => null,
            ],
        ];

        $result = $this->voucherModel->pickBestByScope($vouchers, 100.0);

        $this->assertEquals(2, $result['platform']['id']);
    }

    public function testPickBestByScopeSkipsInvalidScope()
    {
        $vouchers = [
            [
                'id' => 1,
                'scope' => 'invalid_scope',
                'discount_type' => 'fixed',
                'discount_value' => 10,
                'max_discount' => null,
            ],
        ];

        $result = $this->voucherModel->pickBestByScope($vouchers, 100.0);

        $this->assertNull($result['platform']);
        $this->assertNull($result['shop']);
    }
}
