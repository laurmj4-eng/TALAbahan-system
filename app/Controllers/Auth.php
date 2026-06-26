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
                'message' => 'An unexpected error occurred. Please try again later.',
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
                $fallbackUrl = base_url($this->_getRedirectUrl($role));
                $appReturn = $this->request->getGet('app_return');

                if (!$appReturn) {
                    // Called from app WebView (via intent:// return). Redirect directly to dashboard.
                    return $this->response->setBody('
                        <!DOCTYPE html>
                        <html lang="en">
                        <head><meta charset="UTF-8"><title>Redirecting...</title>
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <style>body{background:#0f172a;color:white;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center;padding:1rem}p{font-size:.9375rem;color:rgba(255,255,255,.8)}</style>
                        </head><body><p>Sign-in successful. Opening dashboard...</p>
                        <script>
                            localStorage.removeItem("googleSignInInProgress");
                            localStorage.setItem("isLoggedIn","true");
                            localStorage.setItem("userRole","' . $role . '");
                            localStorage.setItem("username","' . $user['username'] . '");
                            window.location.href = "' . $fallbackUrl . '";
                        </script>
                        </body></html>
                    ');
                }

                // Called from Chrome (after Google auth). Return HTML with talabahan:// deep link.
                $deepLinkData = 'talabahan://auth?redirect=' . urlencode(base_url('auth/mobile-callback?email=' . urlencode($email) . '&name=' . urlencode($name)));
                return $this->response->setBody('
                    <!DOCTYPE html>
                    <html lang="en">
                    <head><meta charset="UTF-8"><title>Sign in successful!</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>
                        *{box-sizing:border-box;margin:0;padding:0}
                        body{background:#0f172a;color:white;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center;padding:1rem}
                        .c{max-width:360px;width:100%}
                        .icon{width:56px;height:56px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:28px}
                        h1{font-size:1.25rem;margin-bottom:8px}
                        p{color:rgba(255,255,255,.7);font-size:.875rem;line-height:1.5;margin-bottom:24px}
                        .btn{display:block;width:100%;padding:14px 24px;background:#3b82f6;color:white;border:none;border-radius:12px;font-weight:600;font-size:1rem;cursor:pointer;transition:background .15s;text-decoration:none}
                        .btn:hover{background:#2563eb}
                        .btn-secondary{margin-top:12px;display:block;width:100%;padding:12px 24px;background:transparent;color:rgba(255,255,255,.6);border:1px solid rgba(255,255,255,.15);border-radius:12px;font-weight:500;font-size:.875rem;cursor:pointer;transition:background .15s;text-decoration:none}
                        .btn-secondary:hover{background:rgba(255,255,255,.05);color:white}
                    </style>
                    </head><body>
                    <div class="c">
                        <div class="icon">✓</div>
                        <h1>Signed in as ' . htmlspecialchars($user['username']) . '</h1>
                        <p>Tap the button below to return to TALAbahan.</p>
                        <button class="btn" onclick="var dl=this.getAttribute(\'data-href\');window.location.href=dl;setTimeout(function(){window.close()},500);" data-href="' . $deepLinkData . '">Return to App</button>
                        <a class="btn-secondary" href="' . $fallbackUrl . '">Continue on Web</a>
                    </div>
                    <script>
                        localStorage.setItem("isLoggedIn","true");
                        localStorage.setItem("userRole","' . $role . '");
                        localStorage.setItem("username","' . $user['username'] . '");
                    </script>
                    </body></html>
                ');
            }

            throw new \Exception("User authentication failed.");

        } catch (\Exception $e) {
            log_message('error', '[Auth::mobileCallback] ' . $e->getMessage());
            return redirect()->to('/login')->with('error', 'An unexpected error occurred. Please try again later.');
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
                'message' => 'An unexpected error occurred. Please try again later.',
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