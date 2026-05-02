<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(OrderSeeder::class);
        // Run separately when you want large randomized dashboard data:
        // php spark db:seed SalesReportStressSeeder
    }
}
