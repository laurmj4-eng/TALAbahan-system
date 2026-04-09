<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function index()
    {
        // Load helpers
        helper(['url', 'form']); // Cookie helper removed, we will use modern LocalStorage
        return view('auth/login');
    }

  public function verify()
    {
        // 1. Get POST data
        $email    = strtolower(trim((string)$this->request->getPost('email')));
        $remember = $this->request->getPost('remember'); 
        $name     = trim((string)$this->request->getPost('name'));      // From Google
        $provider = trim((string)$this->request->getPost('provider')); // 'google' or 'email'
        
        // 2. Connect to database
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        // --- NEW: AUTO-REGISTER GOOGLE USERS AS CUSTOMERS ---
        // If they are not in the DB, but they used Google to login, create their account!
        if (!$user && $provider === 'google') {
            
            // If Google didn't provide a name, just use the first part of their email
            $username = !empty($name) ? $name : explode('@', $email)[0];

            $newUserData = [
                'username' => $username,
                'email'    => $email,
                'role'     => 'customer' // Automatically make them a customer
            ];

            // Insert into DB and then fetch the newly created user
            $db->table('users')->insert($newUserData);
            $user = $db->table('users')->where('email', $email)->get()->getRowArray();
        }

        // 3. Set Session and Redirect if user exists (or was just created)
        if ($user) {
            $sessionData = [
                'user_id'    => $user['id'],
                'username'   => $user['username'],
                'email'      => $user['email'],
                'role'       => strtolower($user['role']),
                'isLoggedIn' => true,
            ];
            session()->set($sessionData);

            // 4. Determine Redirect URL based on Role
            $role = strtolower($user['role']);
            if ($role === 'admin') {
                $redirectUrl = base_url('admin/dashboard');
            } elseif ($role === 'staff') {
                $redirectUrl = base_url('staff/dashboard');
            } else {
                // If they are a 'customer' (or any other role), send them to the main customer area
                $redirectUrl = base_url('dashboard'); // CHANGE THIS to your actual customer page
            }

            return $this->response->setJSON([
                'status'       => 'success', 
                'redirect'     => $redirectUrl,
                'trust_device' => ($remember === 'true') 
            ]);
        }

        // 5. If normal email login and not in DB
        return $this->response->setJSON([
            'status'   => 'error', 
            'redirect' => base_url('dashboard')
        ]);
    }

    public function logout()
    {
        session()->destroy();

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return redirect()->to(base_url('login')); 
    }
}