<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    
    // All allowed fields, including 'role' which is necessary for 'customer'
    protected $allowedFields = ['username', 'email', 'password', 'role', 'prompt_count', 'last_reset'];

    // Callbacks to ensure passwords hash correctly when registering or editing!
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        // FIX: Check if password is not set OR is completely empty
        if (!isset($data['data']['password']) || empty(trim($data['data']['password']))) {
            
            // If it is empty (e.g., during a profile update), remove it from the data array
            // so we don't accidentally overwrite the customer's existing password with an empty hash!
            if (isset($data['data']['password'])) {
                unset($data['data']['password']);
            }
            
            return $data;
        }

        // If a password was provided, hash it securely
        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        
        return $data;
    }
}