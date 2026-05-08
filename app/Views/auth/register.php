<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Mj AI Chatbot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<div class="login-container">
    <img src="<?= base_url('images/pic3.jpg') ?>" alt="Logo" class="form-logo">
    <h2>Create Account</h2>
    
    <form action="<?= site_url('auth/create_account') ?>" method="post">
        <?= csrf_field() ?>
        
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required minlength="6">
        
        <!-- reCAPTCHA Container -->
        <div style="margin-bottom: 20px; display: flex !important; justify-content: center !important; width: 100% !important; min-height: 80px;">
            <div class="g-recaptcha" data-sitekey="<?= env('RECAPTCHA_SITE_KEY') ?>" style="margin: 0 auto !important; display: block !important;"></div>
        </div>
        
        <button type="submit">Sign Up Now</button>
    </form>

    <div class="links">
        <a href="<?= site_url('login') ?>">Already have an account? Login here</a>
    </div>
</div>

</body>
</html>
