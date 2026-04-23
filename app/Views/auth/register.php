<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Mj AI Chatbot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

<!-- TOP RIGHT LOGO -->
<img src="<?= base_url('images/pic3.jpg') ?>" alt="Logo" class="top-right-logo">

<div class="login-container">
    <h2>Create Account</h2>
    
    <form action="<?= site_url('auth/create_account') ?>" method="post">
        <?= csrf_field() ?>
        
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required minlength="6">
        
        <button type="submit">Sign Up Now</button>
    </form>

    <div class="links">
        <a href="<?= site_url('login') ?>">Already have an account? Login here</a>
    </div>
</div>

</body>
</html>
