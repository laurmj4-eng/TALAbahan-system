<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Inertia\Inertia;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->validation = \Config\Services::validation();
        $this->userModel = model('App\Models\UserModel');
    }

    /**
     * Display the login page
     */
    public function loginPage()
    {
        return Inertia::render('Pages/Auth/Login');
    }

    /**
     * Handle login form submission
     * 
     * Validates reCAPTCHA, credentials, and establishes user session
     */
    public function login()
    {
        // Only accept POST requests
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        // Get form data
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $recaptchaToken = $this->request->getPost('recaptcha_response');

        // Validate reCAPTCHA
        if (!$this->verifyRecaptcha($recaptchaToken)) {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => 'reCAPTCHA verification failed',
                'errors' => [
                    'recaptcha_response' => 'Please complete the reCAPTCHA verification and try again',
                ],
            ]);
        }

        // Validate input
        if (!$this->validateInput($email, $password)) {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => 'Validation failed',
                'errors' => $this->validation->getErrors(),
            ]);
        }

        // Check credentials
        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user->password)) {
            // Don't reveal if email exists for security
            return $this->response->setStatusCode(401)->setJSON([
                'message' => 'Invalid email or password',
                'errors' => [
                    'email' => 'Invalid email or password',
                ],
            ]);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return $this->response->setStatusCode(403)->setJSON([
                'message' => 'Account is not active',
                'errors' => [
                    'email' => 'Your account has been deactivated. Contact support.',
                ],
            ]);
        }

        // Establish session
        session()->set([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'is_logged_in' => true,
        ]);

        // Update last login time
        $this->userModel->update($user->id, [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        // Log successful login
        log_message('info', "User {$user->email} logged in successfully");

        // Redirect to dashboard
        return redirect()->to('/dashboard')->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle logout
     */
    public function logout(): RedirectResponse
    {
        $email = session()->get('email');
        
        session()->destroy();
        
        log_message('info', "User {$email} logged out");

        return redirect()->to('/login')->with('success', 'You have been logged out successfully');
    }

    /**
     * Verify reCAPTCHA v2 token with Google
     * 
     * @param string $token The reCAPTCHA token from frontend
     * @return bool True if verification successful, false otherwise
     */
    private function verifyRecaptcha(string $token): bool
    {
        // Get secret key from environment
        $secretKey = env('RECAPTCHA_SECRET_KEY');

        if (empty($token) || empty($secretKey)) {
            log_message('error', 'reCAPTCHA token or secret key missing');
            return false;
        }

        try {
            // Verify with Google reCAPTCHA API
            $client = \Config\Services::curlrequest();
            
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => $secretKey,
                    'response' => $token,
                ],
                'timeout' => 5,
            ]);

            $body = json_decode($response->getBody(), true);

            // For reCAPTCHA v2 checkbox, we just need success = true
            // v3 would check the score, but v2 is binary
            if (!isset($body['success'])) {
                log_message('error', 'Invalid reCAPTCHA response format');
                return false;
            }

            if (!$body['success']) {
                log_message('warning', 'reCAPTCHA verification failed: ' . json_encode($body));
                return false;
            }

            return true;

        } catch (\Exception $e) {
            log_message('error', 'reCAPTCHA verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate email and password input
     */
    private function validateInput(string $email, string $password): bool
    {
        return $this->validation->setRules([
            'email' => [
                'rules' => 'required|valid_email|max_length[255]',
                'errors' => [
                    'required' => 'Email address is required',
                    'valid_email' => 'Please enter a valid email address',
                    'max_length' => 'Email must not exceed 255 characters',
                ],
            ],
            'password' => [
                'rules' => 'required|min_length[6]|max_length[255]',
                'errors' => [
                    'required' => 'Password is required',
                    'min_length' => 'Password must be at least 6 characters',
                    'max_length' => 'Password must not exceed 255 characters',
                ],
            ],
        ])->setData([
            'email' => $email,
            'password' => $password,
        ])->run();
    }

    /**
     * Display registration page
     */
    public function registerPage()
    {
        return Inertia::render('Pages/Auth/Register');
    }

    /**
     * Handle user registration
     */
    public function register()
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirmation');

        // Validate input
        if (!$this->validation->setRules([
            'name' => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirmation' => 'required|matches[password]',
        ])->run()) {
            return back()->withInput()->with('errors', $this->validation->getErrors());
        }

        // Create user
        try {
            $userId = $this->userModel->insert([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            log_message('info', "New user registered: {$email}");

            // Auto-login after registration
            session()->set([
                'user_id' => $userId,
                'email' => $email,
                'name' => $name,
                'is_logged_in' => true,
            ]);

            return redirect()->to('/dashboard')->with('success', 'Welcome to TALAbahan System!');

        } catch (\Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during registration. Please try again.');
        }
    }

    /**
     * Display password reset request page
     */
    public function showPasswordRequestForm()
    {
        return Inertia::render('Pages/Auth/ForgotPassword');
    }

    /**
     * Handle password reset request
     */
    public function requestPasswordReset()
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $email = $this->request->getPost('email');

        // Validate email
        if (!$this->validation->setRules([
            'email' => 'required|valid_email',
        ])->run()) {
            return back()->withInput()->with('errors', $this->validation->getErrors());
        }

        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token (you'd use a password_resets table)
            // This is pseudocode - implement based on your DB structure
            $this->userModel->update($user->id, [
                'reset_token' => $resetToken,
                'reset_token_expires' => $tokenExpiry,
            ]);

            // Send email with reset link
            $this->sendPasswordResetEmail($user, $resetToken);

            log_message('info', "Password reset requested for: {$email}");
        }

        // Always show success message for security (don't reveal if email exists)
        return redirect()->back()->with('success', 'If an account exists with that email, you will receive password reset instructions shortly.');
    }

    /**
     * Send password reset email
     */
    private function sendPasswordResetEmail($user, string $resetToken): void
    {
        $resetLink = base_url("password/reset/{$resetToken}");
        
        $email = \Config\Services::email();
        $email->setFrom('noreply@talabahan.com', 'TALAbahan System');
        $email->setTo($user->email);
        $email->setSubject('Password Reset Request');
        
        $message = "
            <h2>Password Reset Request</h2>
            <p>You requested a password reset for your TALAbahan System account.</p>
            <p><a href='{$resetLink}'>Click here to reset your password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
        ";
        
        $email->setMessage($message);
        $email->send();
    }
}
