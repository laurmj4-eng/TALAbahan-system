<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Mj Pogi Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh;
        }
        .register-container {
            background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%; max-width: 400px; text-align: center;
        }
        .register-container h2 { margin-top: 0; color: #1e293b; font-size: 1.8rem; }
        .register-container p { color: #64748b; margin-bottom: 25px; font-size: 0.95rem;}
        
        .form-group { text-align: left; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.85rem; color: #475569; }
        .form-group input { 
            width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; 
            font-family: inherit; font-size: 1rem; box-sizing: border-box; transition: 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        
        .btn-submit {
            width: 100%; padding: 14px; background: #6366f1; color: white; border: none; 
            border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; transition: 0.3s;
        }
        .btn-submit:hover { background: #4f46e5; transform: translateY(-2px); }
        
        .login-link { margin-top: 20px; display: block; color: #64748b; font-size: 0.9rem; text-decoration: none; }
        .login-link span { color: #6366f1; font-weight: 600; }
        .login-link:hover span { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>Create an Account</h2>
        <p>Join the Mj Portal as a Customer.</p>

        <!-- Form points to the route we created in Step 1 -->
        <form action="<?= site_url('auth/create_account') ?>" method="post">
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="e.g. johndoe" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a strong password" required minlength="6">
            </div>

            <button type="submit" class="btn-submit">Sign Up Now</button>
        </form>

        <a href="<?= site_url('login') ?>" class="login-link">Already have an account? <span>Login here</span></a>
    </div>

</body>
</html>