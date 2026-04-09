<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function index()
    {
        // Load helpers
        helper(['url', 'form']); 
        return view('auth/login');
    }

    public function verify()
    {
        // 1. Get POST data
        $email    = strtolower(trim((string)$this->request->getPost('email')));
        $password = (string)$this->request->getPost('password'); // THIS WAS MISSING IN YOUR CODE!
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
                $newUserData = [
                    'username' => $username,
                    'email'    => $email,
                    'role'     => 'customer' 
                ];
                $db->table('users')->insert($newUserData);
                $user = $db->table('users')->where('email', $email)->get()->getRowArray();
            }
        } 
        // 4. Handle NORMAL Logins (Customers typing Email & Password)
        else {
            // FIX: Check if user exists AND if the password matches the hash in the database!
            if (!$user || !password_verify($password, $user['password'])) {
                return $this->response->setJSON([
                    'status'  => 'error', 
                    'message' => 'Invalid Email or Password.'
                ]);
            }
        }

        // 5. Set Session and Redirect if user exists and password is correct
        if ($user) {
            $sessionData = [
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
                // Customer dashboard
                $redirectUrl = base_url('dashboard'); 
            }

            return $this->response->setJSON([
                'status'       => 'success', 
                'redirect'     => $redirectUrl,
                'trust_device' => ($remember === 'true') 
            ]);
        }
    }

    // --- THESE WERE MISSING IN YOUR CODE! THEY ARE REQUIRED FOR REGISTRATION ---

    // Shows the Registration Page
    public function register()
    {
        return view('auth/register'); 
    }

    // Processes the Form and Saves the Customer
    public function createAccount()
    {
        $userModel = new \App\Models\UserModel();

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role'     => 'customer' 
        ];

        $userModel->insert($data);

        return redirect()->to('/login')->with('success', 'Account created successfully! Please login.');
    }

    // --------------------------------------------------------------------------

    public function logout()
    {
        session()->destroy();

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return redirect()->to(base_url('login')); 
    }
}