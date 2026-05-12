<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TALAbahan System</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
        window.CSRF_HASH = "<?= csrf_hash() ?>";
        window.RECAPTCHA_SITE_KEY = "<?= env('RECAPTCHA_SITE_KEY') ?>";
        window.FIREBASE_CONFIG = {
            apiKey: "<?= env('FIREBASE_API_KEY') ?>",
            authDomain: "<?= env('FIREBASE_AUTH_DOMAIN') ?>",
            projectId: "<?= env('FIREBASE_PROJECT_ID') ?>",
            storageBucket: "<?= env('FIREBASE_STORAGE_BUCKET') ?>",
            messagingSenderId: "<?= env('FIREBASE_MESSAGING_SENDER_ID') ?>",
            appId: "<?= env('FIREBASE_APP_ID') ?>",
            measurementId: "<?= env('FIREBASE_MEASUREMENT_ID') ?>"
        };
    </script>
    <!-- Vite Assets -->
    <?php if (ENVIRONMENT === 'development'): ?>
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/resources/js/main.js"></script>
    <?php else: ?>
        <script type="module" src="<?= base_url('dist/assets/app.js') ?>"></script>
        <link rel="stylesheet" href="<?= base_url('dist/assets/app.css') ?>">
    <?php endif; ?>
</head>
<body class="bg-slate-950 text-white">
    <div id="app"></div>
</body>
</html>
