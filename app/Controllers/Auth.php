<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        // Load helpers
        helper(['url', 'form']); 
        
        // If already logged in, redirect them to their respective dashboard
        if (session()->get('isLoggedIn')) {
            return $this->_redirectByRole(session()->get('role'));
        }

        return view('auth/login');
    }

    public function verify()
    {
        // 1. Get POST data
        $email    = strtolower(trim((string)$this->request->getPost('email')));
        $password = (string)$this->request->getPost('password'); 
        $remember = $this->request->getPost('remember'); 
        $name     = trim((string)$this->request->getPost('name'));      
        $provider = trim((string)$this->request->getPost('provider')); 
        
        // 2. Connect to database
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        // 3. Handle GOOGLE Logins
        if ($provider === 'google') {
            if (!$user) {
                // Auto-register Google User
                $username = !empty($name) ? $name : explode('@', $email)[0];
                $newUserData =[
                    'username' => $username,
                    'email'    => $email,
                    'role'     => 'customer' // Default role for new signups
                ];
                $db->table('users')->insert($newUserData);
                $user = $db->table('users')->where('email', $email)->get()->getRowArray();
            }
        } 
        // 4. Handle NORMAL Logins (Email & Password)
        else {
            // Check if user exists and verify the hashed password
            if (!$user || !password_verify($password, $user['password'])) {
                return $this->response->setJSON([
                    'status'  => 'error', 
                    'message' => 'Invalid Email or Password.'
                ]);
            }
        }

        // 5. Set Session and Redirect
        if ($user) {
            $sessionData =[
                'user_id'    => $user['id'],
                'username'   => $user['username'],
                'email'      => $user['email'],
                'role'       => strtolower($user['role']),
                'isLoggedIn' => true,
            ];
            session()->set($sessionData);

            // 6. Determine Redirect URL based on Role
            $role = strtolower($user['role']);
            if ($role === 'admin') {
                $redirectUrl = base_url('admin/dashboard');
            } elseif ($role === 'staff') {
                $redirectUrl = base_url('staff/dashboard');
            } else {
                // Points to the new customer subfolder route
                $redirectUrl = base_url('customer/dashboard'); 
            }

            return $this->response->setJSON([
                'status'       => 'success', 
                'redirect'     => $redirectUrl,
                'trust_device' => ($remember === 'true') 
            ]);
        }
    }

    /**
     * Shows the Registration Page
     */
    public function register()
    {
        return view('auth/register'); 
    }

    /**
     * Processes the Form and Saves the Customer
     */
    public function createAccount()
    {
        $userModel = new UserModel();

        // FIX: Just pass the RAW password here! 
        // Your UserModel's `beforeInsert` callback will hash it automatically!
        $data =[
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), // <-- CHANGED THIS LINE
            'role'     => 'customer' 
        ];

        $userModel->insert($data);

        return redirect()->to('/login')->with('success', 'Account created successfully! Please login.');
    }

    /**
     * Helper to redirect based on role (used in index)
     */
    private function _redirectByRole($role)
    {
        if ($role === 'admin') return redirect()->to(base_url('admin/dashboard'));
        if ($role === 'staff') return redirect()->to(base_url('staff/dashboard'));
        return redirect()->to(base_url('customer/dashboard'));
    }

    /**
     * Destroys session and clears cache for security
     */
    public function logout()
    {
        session()->destroy();

        // Clear headers to prevent "Back" button from showing sensitive data
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return redirect()->to(base_url('login')); 
    }
}