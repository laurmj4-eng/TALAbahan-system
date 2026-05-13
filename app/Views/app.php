<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TALAbahan System</title>
    
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
    
    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
        window.CSRF_HASH = "<?= csrf_hash() ?>";
        window.RECAPTCHA_SITE_KEY = "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI";
        window.FIREBASE_CONFIG = {
            apiKey: "AIzaSyCqr4BdFF2Xb0oqaeDpW_DWeu_XmUFQ8JA",
            authDomain: "seafood-6844f.firebaseapp.com",
            projectId: "seafood-6844f",
            storageBucket: "seafood-6844f.firebasestorage.app",
            messagingSenderId: "1072715877925",
            appId: "1:1072715877925:web:682e2b30e7e540f154b9cc",
            measurementId: "G-9V4E950D0E"
        };
    </script>

    <?php if (ENVIRONMENT === 'development'): ?>
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/resources/js/main.js"></script>
    <?php else: ?>
        <link rel="stylesheet" href="<?= base_url('build/assets/index.css') ?>">
        <script type="module" src="<?= base_url('build/assets/index.js') ?>"></script>
    <?php endif; ?>
</head>
<body class="bg-slate-950 text-white">
    <?php echo inertia_div(); ?>
</body>
</html>
