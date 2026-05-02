<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    
    // Allowed fields for the database
    protected $allowedFields = [
        'username', 
        'email', 
        'password', 
        'role', 
        'prompt_count', 
        'last_reset'
    ];

    protected $returnType = 'array';

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
        'email'    => 'required|valid_email|max_length[100]|is_unique[users.email]',
        'password' => 'permit_empty|min_length[6]|max_length[255]',
        'role'     => 'required|in_list[admin,staff,customer]',
    ];

    // CI4 Callbacks: These run automatically before saving to the database
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Automatically hashes the password before it is saved.
     * If the password field is empty (common during profile updates), 
     * it removes the field so the existing password is not overwritten.
     */
    protected function hashPassword(array $data)
    {
        // 1. Check if the password key exists in the incoming data
        if (!isset($data['data']['password'])) {
            return $data;
        }

        // 2. If the password is empty or just whitespace, remove it from the update
        if (empty(trim($data['data']['password']))) {
            unset($data['data']['password']);
            return $data;
        }

        // 3. Hash the plain-text password securely
        // This will turn "mypassword123" into a 60-character string starting with $2y$
        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        
        return $data;
    }
}