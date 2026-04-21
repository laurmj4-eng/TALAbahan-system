<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class CategoriesController extends BaseController
{
    private function hasTable(string $table): bool
    {
        return db_connect()->tableExists($table);
    }

    public function index()
    {
        if (! $this->hasTable('categories')) {
            return view('admin/categories_view', [
                'categories' => [],
                'error'      => 'Categories table is missing. Run: php spark migrate',
            ]);
        }

        $model = new CategoryModel();
        $q = trim((string) $this->request->getGet('q'));
        $builder = $model->orderBy('name', 'ASC');
        if ($q !== '') {
            $builder = $builder->like('name', $q);
        }
        return view('admin/categories_view', [
            'categories' => $builder->paginate(10),
            'pager'      => $model->pager,
            'filters'    => ['q' => $q],
        ]);
    }

    public function store()
    {
        if (! $this->hasTable('categories')) {
            return redirect()->to('/admin/categories')->with('error', 'Categories table is missing. Run: php spark migrate');
        }

        $model = new CategoryModel();
        $data  = [
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
            'is_active'   => $this->request->getPost('is_active') === null ? 1 : (int) $this->request->getPost('is_active'),
        ];

        if (! $model->insert($data)) {
            return redirect()->back()->with('errors', $model->errors())->withInput();
        }

        return redirect()->to('/admin/categories')->with('msg', 'Category created.');
    }
}
