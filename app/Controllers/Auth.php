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
            $isTrustedDevice = $this->request->getPost('is_trusted_device') === 'true';

            // 3. Sanitize email into a PSR-16-compliant cache key.
            // Raw emails contain '@' and '.' which are reserved characters in PSR-16,
            // causing: InvalidArgumentException: Cache key contains reserved characters {}()/\@:
            // md5() produces a safe 32-char lowercase hex string with no reserved characters.
            $cacheKey = 'google_login_' . md5($email);

            // Rate-limit login attempts per email (max 10 per 5 minutes for all providers)
            $attempts = cache($cacheKey) ?? 0;
            if ($attempts >= 10) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Too many login attempts. Please try again later.',
                    'token'   => csrf_hash()
                ])->setStatusCode(429);
            }

            // Also rate-limit by IP address to prevent distributed brute force
            $ipKey = 'login_ip_' . md5($this->request->getIPAddress());
            $ipAttempts = cache($ipKey) ?? 0;
            if ($ipAttempts >= 20) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Too many login attempts from this IP. Please try again later.',
                    'token'   => csrf_hash()
                ])->setStatusCode(429);
            }
            cache()->save($ipKey, $ipAttempts + 1, 300);

            // Increment email-based attempt counter for all providers
            cache()->save($cacheKey, $attempts + 1, 300);

            // 4. Connect to database
            $userModel = new UserModel();
            $user = $userModel->where('email', $email)->first();

            // 2. Verify reCAPTCHA (Server-side)
            if ($provider !== 'google') {
                $skipRecaptcha = false;
                
                // If the user is an admin and the device is trusted, skip reCAPTCHA
                if ($user && strtolower($user['role']) === 'admin' && $isTrustedDevice) {
                    $skipRecaptcha = true;
                }

                if (!$skipRecaptcha) {
                    $captcha = verify_recaptcha_response($recaptchaResponse);
                    if (!$captcha['ok']) {
                        return recaptcha_json_error($captcha);
                    }
                }
            }
            
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
                // Reset login attempt counter on successful login
                cache()->delete($cacheKey);

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

    public function mobileLogin()
    {
        return view('mobile_login');
    }

    public function mobileCallback()
    {
        try {
            $email = strtolower(trim((string)$this->request->getGet('email')));
            $name  = trim((string)$this->request->getGet('name'));
            
            if (empty($email)) {
                return redirect()->to('/login')->with('error', 'Google login failed (no email).');
            }

            $userModel = new \App\Models\UserModel();
            $user = $userModel->where('email', $email)->first();

            if (!$user) {
                // Generate a unique username
                $baseUsername = !empty($name) ? $name : explode('@', $email)[0];
                $username = $baseUsername;
                
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

            if ($user) {
                $session = session();
                $sessionData = [
                    'user_id'    => $user['id'],
                    'username'   => $user['username'],
                    'email'      => $user['email'],
                    'role'       => strtolower($user['role']),
                    'isLoggedIn' => true,
                ];
                $session->set($sessionData);
                session_write_close();

                $role = strtolower($user['role']);
                $redirectUrl = base_url($this->_getRedirectUrl($role));
                
                // Return simple HTML page that sets localStorage and redirects to avoid Inertia layout flashing issues on reload
                return $this->response->setBody('
                    <html><head><title>Logging in...</title></head><body>
                    <script>
                        localStorage.setItem("isLoggedIn", "true");
                        localStorage.setItem("userRole", "' . $role . '");
                        localStorage.setItem("username", "' . $user['username'] . '");
                        window.location.href = "' . $redirectUrl . '";
                    </script>
                    </body></html>
                ');
            }

            throw new \Exception("User authentication failed.");

        } catch (\Exception $e) {
            log_message('error', '[Auth::mobileCallback] ' . $e->getMessage());
            return redirect()->to('/login')->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    public function createAccount()
    {
        $captcha = verify_recaptcha_response($this->request->getPost('g-recaptcha-response'));
        if (!$captcha['ok']) {
            return redirect()->back()->with('error', $captcha['message'])->withInput();
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
            $captcha = verify_recaptcha_response($this->request->getPost('g-recaptcha-response'));
            if (!$captcha['ok']) {
                return recaptcha_json_error($captcha);
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