<?php
/**
 * Asset helper function to get versioned asset URLs from Vite manifest
 */
if (!function_exists('vite_asset')) {
    function vite_asset($path) {
        // Development mode: serve from Vite dev server
        if (ENVIRONMENT === 'development') {
            return "http://localhost:5173/{$path}";
        }

        // Production mode: FCPATH already points to public/, so manifest is at public/build/manifest.json
        $manifestPath = FCPATH . 'build/manifest.json';

        if (!file_exists($manifestPath)) {
            log_message('error', '[Vite] Manifest file not found at: ' . $manifestPath);
            return base_url("build/{$path}");
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (!isset($manifest[$path])) {
            log_message('warning', '[Vite] Asset not found in manifest: ' . $path);
            return base_url("build/{$path}");
        }

        return base_url('build/' . $manifest[$path]['file']);
    }
}

if (!function_exists('vite_css')) {
    function vite_css($path) {
        if (ENVIRONMENT === 'development') {
            return null; // CSS is injected by Vite HMR in dev
        }

        $manifestPath = FCPATH . 'build/manifest.json';
        if (!file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (!isset($manifest[$path]['css'])) {
            return null;
        }

        // Return array of CSS file URLs
        return array_map(fn($css) => base_url('build/' . $css), $manifest[$path]['css']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TALAbahan System</title>
    
    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- reCAPTCHA v2: loaded by Vue composable (recaptchaLoader.js) -->
    <script>
        window.BASE_URL = "<?= base_url() ?>";
        window.CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
        window.CSRF_HASH = "<?= csrf_hash() ?>";
        <?php $recaptcha = config('Recaptcha'); ?>
        window.RECAPTCHA_ENABLED = <?= json_encode($recaptcha->isEnabled()) ?>;
        window.RECAPTCHA_SITE_KEY = <?= json_encode($recaptcha->siteKey) ?>;
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

    <?php if (ENVIRONMENT === 'development'): ?>
        <!-- Development: Load from Vite dev server -->
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/resources/js/main.js"></script>
    <?php else: ?>
        <!-- Production: Load versioned CSS and JS from Vite manifest -->
        <?php $cssList = vite_css('resources/js/main.js'); ?>
        <?php if ($cssList): foreach ($cssList as $cssUrl): ?>
        <link rel="stylesheet" href="<?= esc($cssUrl) ?>">
        <?php endforeach; endif; ?>
        <script type="module" src="<?= esc(vite_asset('resources/js/main.js')) ?>"></script>
    <?php endif; ?>
</head>
<body class="bg-slate-950 text-white">
    <?php echo inertia_div($page); ?>
</body>
</html>
