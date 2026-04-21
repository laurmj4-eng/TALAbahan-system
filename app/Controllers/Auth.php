<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        helper(['url', 'form']); 
        
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
        $recaptchaResponse = $this->request->getPost('g-recaptcha-response');

        // 2. Verify reCAPTCHA (Server-side)
        // LOGIC: Only verify if the provider is NOT google AND the recaptcha response is NOT empty.
        // If $recaptchaResponse is empty, it means your frontend hidden it because the device is trusted.
        // NOTE: Skip reCAPTCHA in development since the secret key is not configured.
        if ($provider !== 'google' && !empty($recaptchaResponse) && ENVIRONMENT !== 'development') {
            
            // IMPORTANT: Replace this with your ACTUAL Google Secret Key
            $secret = '6LcVGI0sAAAAAKG...REPLACE_THIS_WITH_YOUR_KEY...'; 
            
            $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$recaptchaResponse}");
            $captchaData = json_decode($verify);

            if (!$captchaData->success) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'reCAPTCHA verification failed. Please try again.',
                    'token'   => csrf_hash() // Send new token for next attempt
                ]);
            }
        }
        
        // 3. Connect to database
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        // 4. Handle GOOGLE Logins
        if ($provider === 'google') {
            if (!$user) {
                $username = !empty($name) ? $name : explode('@', $email)[0];
                $newUserData = [
                    'username' => $username,
                    'email'    => $email,
                    // Keep schema constraint happy; this is never used for Google sign-in.
                    'password' => bin2hex(random_bytes(16)),
                    'role'     => 'customer'
                ];
                $userModel->insert($newUserData);
                $user = $userModel->where('email', $email)->first();
            }
        } 
        // 5. Handle NORMAL Logins
        else {
            if (!$user || !password_verify($password, $user['password'])) {
                return $this->response->setJSON([
                    'status'  => 'error', 
                    'message' => 'Invalid Email or Password.',
                    'token'   => csrf_hash() 
                ]);
            }
        }

        // 6. Set Session and Redirect
        if ($user) {
            $sessionData =[
                'user_id'    => $user['id'],
                'username'   => $user['username'],
                'email'      => $user['email'],
                'role'       => strtolower($user['role']),
                'isLoggedIn' => true,
            ];
            session()->set($sessionData);

            $role = strtolower($user['role']);
            $redirectUrl = $this->_getRedirectUrl($role);

            return $this->response->setJSON([
                'status'       => 'success', 
                'redirect'     => $redirectUrl,
                'trust_device' => ($remember === 'true') 
            ]);
        }
    }

    public function register()
    {
        helper(['form']);
        return view('auth/register'); 
    }

    public function createAccount()
    {
        $userModel = new UserModel();

        $data =[
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), 
            'role'     => 'customer' 
        ];

        if($userModel->insert($data)) {
            return redirect()->to('/login')->with('success', 'Account created successfully!');
        } else {
            return redirect()->back()->with('error', 'Registration failed.')->withInput();
        }
    }

    private function _getRedirectUrl($role)
    {
        if ($role === 'admin') return base_url('admin/dashboard');
        if ($role === 'staff') return base_url('staff/dashboard');
        return base_url('customer/dashboard');
    }

    private function _redirectByRole($role)
    {
        return redirect()->to($this->_getRedirectUrl(strtolower($role)));
    }

    public function logout()
    {
        session()->destroy();
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        return redirect()->to(base_url('login')); 
    }
}