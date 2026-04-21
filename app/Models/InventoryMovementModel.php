<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryMovementModel extends Model
{
    public const TYPE_IN         = 'IN';
    public const TYPE_OUT        = 'OUT';
    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    public const TYPE_WASTAGE    = 'WASTAGE';

    protected $table            = 'inventory_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'product_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'moved_by',
    ];

    public function logMovement(array $data): bool
    {
        return $this->insert($data) !== false;
    }
}
