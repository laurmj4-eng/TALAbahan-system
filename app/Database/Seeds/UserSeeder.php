<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Get credentials from .env
        $adminEmail = env('ADMIN_EMAIL');
        $adminPass  = env('ADMIN_PASSWORD');

        $staffEmail = env('STAFF_EMAIL');
        $staffPass  = env('STAFF_PASSWORD');

        // 2. Prepare multiple users (Batch)
        $data = [
            [
                'username'   => 'admin_user',
                'email'      => $adminEmail,
                'password'   => password_hash($adminPass, PASSWORD_DEFAULT),
                'role'       => 'admin', // Important: helps CI4 redirect to Admin Dashboard
              
            ],
            [
                'username'   => 'staff_member',
                'email'      => $staffEmail,
                'password'   => password_hash($staffPass, PASSWORD_DEFAULT),
                'role'       => 'staff', // Important: helps CI4 redirect to Staff Dashboard
              
            ],
        ];

        // 3. Clear the table first (Optional: avoids "Duplicate Entry" errors if you run it twice)
        $this->db->table('users')->emptyTable();

        // 4. Insert all users at once
        $this->db->table('users')->insertBatch($data);
    }
}