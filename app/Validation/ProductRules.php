<?php

namespace App\Validation;

class ProductRules
{
    /**
     * Custom validation rule to check if selling price is greater than cost price.
     *
     * @param mixed       $value
     * @param string|null $fields
     * @param array       $data
     * @return bool
     */
    public function validate_price_gt_cost($value, ?string $fields = null, array $data = []): bool
    {
        if (!isset($data['selling_price']) || !isset($data['cost_price'])) {
            return true; // Let other rules handle required fields
        }

        $sellingPrice = (float) $data['selling_price'];
        $costPrice = (float) $data['cost_price'];

        return $sellingPrice > $costPrice;
    }
}
