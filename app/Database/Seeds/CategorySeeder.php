<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $this->db->table('categories')->truncate();
        $this->db->table('categories')->insertBatch([
            ['name' => 'Fish', 'description' => 'Fresh fish products', 'is_active' => 1],
            ['name' => 'Shellfish', 'description' => 'Shellfish and mollusks', 'is_active' => 1],
            ['name' => 'Crustaceans', 'description' => 'Shrimp, crab, and related', 'is_active' => 1],
            ['name' => 'Frozen', 'description' => 'Frozen seafood products', 'is_active' => 1],
        ]);
    }
}
