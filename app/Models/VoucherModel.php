<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherModel extends Model
{
    protected $table = 'vouchers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'code',
        'name',
        'scope',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_order_amount',
        'payment_method_limit',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    public function getEligibleForQuote(float $subtotal, string $paymentMethod): array
    {
        $now = date('Y-m-d H:i:s');
        $vouchers = $this->where('is_active', 1)
            ->groupStart()
                ->where('starts_at IS NULL', null, false)
                ->orWhere('starts_at <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('ends_at IS NULL', null, false)
                ->orWhere('ends_at >=', $now)
            ->groupEnd()
            ->where('min_order_amount <=', $subtotal)
            ->findAll();

        return array_values(array_filter($vouchers, static function (array $voucher) use ($paymentMethod): bool {
            $limit = strtoupper((string) ($voucher['payment_method_limit'] ?? ''));
            return $limit === '' || $limit === strtoupper($paymentMethod);
        }));
    }

    public function computeDiscount(array $voucher, float $amount): float
    {
        $discount = 0.0;
        if (($voucher['discount_type'] ?? 'fixed') === 'percent') {
            $discount = $amount * ((float) $voucher['discount_value'] / 100);
        } else {
            $discount = (float) $voucher['discount_value'];
        }

        $max = (float) ($voucher['max_discount'] ?? 0);
        if ($max > 0) {
            $discount = min($discount, $max);
        }

        return max(0.0, round($discount, 2));
    }

    public function pickBestByScope(array $vouchers, float $subtotal): array
    {
        $best = [
            'platform' => null,
            'shop' => null,
        ];

        foreach ($vouchers as $voucher) {
            $scope = strtolower((string) ($voucher['scope'] ?? 'platform'));
            if (! in_array($scope, ['platform', 'shop'], true)) {
                continue;
            }

            $voucher['computed_discount'] = $this->computeDiscount($voucher, $subtotal);
            if (! isset($best[$scope]) || $best[$scope] === null) {
                $best[$scope] = $voucher;
                continue;
            }

            if ($voucher['computed_discount'] > ($best[$scope]['computed_discount'] ?? 0)) {
                $best[$scope] = $voucher;
            }
        }

        return $best;
    }
}
