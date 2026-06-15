<?php
/**
 * Vite asset helper functions.
 *
 * Dev mode  → public/hot exists (Vite dev server running on localhost:5173)
 * Prod mode → public/hot absent  (compiled manifest at public/build/manifest.json)
 *
 * NEVER rely solely on CI_ENVIRONMENT / ENVIRONMENT because the constant
 * is locked in at PHP bootstrap and can be wrong during Docker image builds.
 */

/**
 * Returns true when the Vite dev server is active (public/hot file present).
 */
if (!function_exists('vite_is_dev')) {
    function vite_is_dev(): bool {
        return file_exists(FCPATH . 'hot');
    }
}

/**
 * Reads the compiled manifest and returns the hashed file URL.
 */
if (!function_exists('vite_asset')) {
    function vite_asset(string $path): string {
        if (vite_is_dev()) {
            return "http://localhost:5173/{$path}";
        }

        static $manifest = null;
        if ($manifest === null) {
            $manifestPath = FCPATH . 'build/manifest.json';
            if (!file_exists($manifestPath)) {
                log_message('error', '[Vite] Manifest not found at: ' . $manifestPath);
                return base_url("build/{$path}");
            }
            $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
        }

        if (!isset($manifest[$path])) {
            log_message('warning', '[Vite] Asset not in manifest: ' . $path);
            return base_url("build/{$path}");
        }

        return base_url('build/' . $manifest[$path]['file']);
    }
}

/**
 * Returns an array of CSS URLs for the given entry (prod only).
 */
if (!function_exists('vite_css')) {
    function vite_css(string $path): ?array {
        if (vite_is_dev()) {
            return null; // CSS is injected by Vite HMR in dev
        }

        static $manifest = null;
        if ($manifest === null) {
            $manifestPath = FCPATH . 'build/manifest.json';
            if (!file_exists($manifestPath)) {
                return null;
            }
            $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
        }

        if (empty($manifest[$path]['css'])) {
            return null;
        }

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
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    
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

    <?php if (vite_is_dev()): ?>
        <!-- Development: Load from Vite dev server (public/hot exists) -->
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
