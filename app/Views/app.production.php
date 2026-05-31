<?php
/**
 * Asset helper function to get versioned asset URLs from Vite manifest
 */
if (!function_exists('vite_asset')) {
    function vite_asset($path) {
        $baseUrl = base_url();
        
        // Development mode: serve from Vite dev server
        if (ENVIRONMENT === 'development') {
            return "http://localhost:5173/{$path}";
        }
        
        // Production mode: read from manifest
        $manifestPath = FCPATH . 'build/manifest.json';
        if (! file_exists($manifestPath)) {
            $manifestPath = FCPATH . 'public/build/manifest.json';
        }

        if (!file_exists($manifestPath)) {
            log_message('error', '[Vite] Manifest file not found: ' . $manifestPath);
            // Fallback to static path
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

    <?php if (ENVIRONMENT === 'development'): ?>
        <!-- Development: Load from Vite dev server -->
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/resources/js/main.js"></script>
    <?php else: ?>
        <!-- Production: Load versioned assets from manifest -->
        <link rel="stylesheet" href="<?= vite_asset('resources/js/main.js') ?>">
        <script type="module" src="<?= vite_asset('resources/js/main.js') ?>"></script>
    <?php endif; ?>
</head>
<body class="bg-slate-950 text-white">
    <?php echo inertia_div($page); ?>
</body>
</html>
