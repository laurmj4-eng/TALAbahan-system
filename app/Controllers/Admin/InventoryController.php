<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\InventoryMovementModel;

class InventoryController extends BaseController
{
    public function movements()
    {
        if (! db_connect()->tableExists('inventory_movements')) {
            return view('admin/movements_view', [
                'movements' => [],
                'error'     => 'Inventory movements table is missing. Run: php spark migrate',
            ]);
        }

        $model = new InventoryMovementModel();
        $q = trim((string) $this->request->getGet('q'));
        $type = trim((string) $this->request->getGet('type'));
        $from = trim((string) $this->request->getGet('from'));
        $to = trim((string) $this->request->getGet('to'));
        $builder = $model->orderBy('created_at', 'DESC');
        if ($q !== '') {
            $builder = $builder->groupStart()->like('notes', $q)->orLike('reference_type', $q)->groupEnd();
        }
        if ($type !== '') {
            $builder = $builder->where('movement_type', $type);
        }
        if ($from !== '') {
            $builder = $builder->where('DATE(created_at) >=', $from);
        }
        if ($to !== '') {
            $builder = $builder->where('DATE(created_at) <=', $to);
        }
        return view('admin/movements_view', [
            'movements' => $builder->paginate(15),
            'pager'     => $model->pager,
            'filters'   => compact('q', 'type', 'from', 'to'),
        ]);
    }
}
