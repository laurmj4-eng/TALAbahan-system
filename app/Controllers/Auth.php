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

        return inertia('LoginPage');
    }

    public function verify()
    {
        // CORS headers are handled in BaseController::initController
        // but we can ensure they are set correctly here too if needed.
        
        try {
            // 1. Get POST data
            $email    = strtolower(trim((string)$this->request->getPost('email')));
            $password = (string)$this->request->getPost('password'); 
            $remember = $this->request->getPost('remember'); 
            $name     = trim((string)$this->request->getPost('name'));      
            $provider = trim((string)$this->request->getPost('provider')); 
            $recaptchaResponse = $this->request->getPost('g-recaptcha-response');

            // 2. Verify reCAPTCHA (Server-side) - Using CURL for better compatibility on InfinityFree
            if ($provider !== 'google') {
                if (empty($recaptchaResponse)) {
                    // return $this->response->setJSON([
                    //     'status'  => 'error',
                    //     'message' => 'Please complete the reCAPTCHA verification.',
                    //     'token'   => csrf_hash()
                    // ])->setStatusCode(400);
                }

                $secret = env('RECAPTCHA_SECRET_KEY'); 
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'secret'   => $secret,
                    'response' => $recaptchaResponse
                ]));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Often needed on InfinityFree
                $verify = curl_exec($ch);
                curl_close($ch);
                
                if ($verify !== false) {
                    $captchaData = json_decode($verify);
                    if (!$captchaData || !$captchaData->success) {
                        // return $this->response->setJSON([
                        //     'status'  => 'error',
                        //     'message' => 'reCAPTCHA verification failed. Please try again.',
                        //     'token'   => csrf_hash()
                        // ])->setStatusCode(400);
                    }
                }
            }
            
            // 3. Connect to database
            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            // 4. Handle GOOGLE Logins
            if ($provider === 'google') {
                if (!$user) {
                    // Generate a unique username
                    $baseUsername = !empty($name) ? $name : explode('@', $email)[0];
                    $username = $baseUsername;
                    
                    // Keep checking until we find a username that isn't taken
                    $count = 1;
                    while ($userModel->where('username', $username)->first()) {
                        $username = $baseUsername . $count;
                        $count++;
                    }

                    $newUserData = [
                        'username' => $username,
                        'email'    => $email,
                        'password' => bin2hex(random_bytes(16)),
                        'role'     => 'customer'
                    ];
                    
                    if (!$userModel->insert($newUserData)) {
                        throw new \Exception("Failed to create Google account: " . implode(', ', $userModel->errors()));
                    }
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
                    ])->setStatusCode(401);
                }
            }

            // 6. Set Session and Redirect
            if ($user) {
                // Ensure session is started
                $session = session();
                
                $sessionData =[
                    'user_id'    => $user['id'],
                    'username'   => $user['username'],
                    'email'      => $user['email'],
                    'role'       => strtolower($user['role']),
                    'isLoggedIn' => true,
                ];
                $session->set($sessionData);
                
                // Save session explicitly to ensure persistence before redirect response
                session_write_close();

                $role = strtolower($user['role']);
                $redirectUrl = $this->_getRedirectUrl($role);

                return $this->response->setJSON([
                    'status'       => 'success', 
                    'message'      => 'Login successful.',
                    'role'         => $role,
                    'username'     => $user['username'],
                    'data'         => [
                        'redirect'     => $redirectUrl,
                        'trust_device' => ($remember === 'true'),
                    ],
                    'redirect'     => $redirectUrl,
                    'trust_device' => ($remember === 'true'),
                    'token'        => csrf_hash()
                ])->setStatusCode(200);
            }

            throw new \Exception("User authentication failed.");

        } catch (\Exception $e) {
            log_message('error', '[Auth::verify] ' . $e->getMessage());
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'System Error: ' . $e->getMessage(),
                'token'   => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    public function register()
    {
        helper(['form']);
        return inertia('RegisterPage'); 
    }

    public function createAccount()
    {
        // 1. Verify reCAPTCHA (Server-side) - Using CURL for better compatibility on InfinityFree
        $recaptchaResponse = $this->request->getPost('g-recaptcha-response');
        if (!empty($recaptchaResponse)) {
            $secret = env('RECAPTCHA_SECRET_KEY');
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'secret'   => $secret,
                'response' => $recaptchaResponse
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            $verify = curl_exec($ch);
            curl_close($ch);
            
            if ($verify !== false) {
                $captchaData = json_decode($verify);
                if (!$captchaData || !$captchaData->success) {
                    return redirect()->back()->with('error', 'reCAPTCHA verification failed. Please try again.')->withInput();
                }
            }
        }

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
            $errors = $userModel->errors();
            $errorMessage = !empty($errors) ? implode(' ', $errors) : 'Registration failed.';
            return redirect()->back()->with('error', $errorMessage)->withInput();
        }
    }

    public function createAccountApi()
    {
        try {
            // 1. Verify reCAPTCHA (Server-side)
            $recaptchaResponse = $this->request->getPost('g-recaptcha-response');
            if (empty($recaptchaResponse)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Please complete the reCAPTCHA verification.',
                    'token'   => csrf_hash()
                ])->setStatusCode(400);
            }

            $secret = env('RECAPTCHA_SECRET_KEY');
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'secret'   => $secret,
                'response' => $recaptchaResponse
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            $verify = curl_exec($ch);
            curl_close($ch);
            
            if ($verify !== false) {
                $captchaData = json_decode($verify);
                if (!$captchaData || !$captchaData->success) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'reCAPTCHA verification failed. Please try again.',
                        'token'   => csrf_hash()
                    ])->setStatusCode(400);
                }
            }

            $userModel = new UserModel();

            $data = [
                'username' => trim((string)$this->request->getPost('username')),
                'email'    => strtolower(trim((string)$this->request->getPost('email'))),
                'password' => (string)$this->request->getPost('password'), 
                'role'     => 'customer' 
            ];

            if ($userModel->insert($data)) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Account created successfully!',
                    'token'   => csrf_hash()
                ])->setStatusCode(200);
            } else {
                $errors = $userModel->errors();
                $errorMessage = !empty($errors) ? implode(' ', $errors) : 'Registration failed.';
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => $errorMessage,
                    'token'   => csrf_hash()
                ])->setStatusCode(400);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'System Error: ' . $e->getMessage(),
                'token'   => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    private function _getRedirectUrl($role)
    {
        if ($role === 'admin') return 'admin/dashboard';
        if ($role === 'staff') return 'staff/dashboard';
        return 'customer/dashboard';
    }

    private function _redirectByRole($role)
    {
        return redirect()->to(base_url($this->_getRedirectUrl(strtolower($role))));
    }

    public function logout()
    {
        session()->destroy();
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        return redirect()->to(base_url('login')); 
    }
}