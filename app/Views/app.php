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
            $manifestPath = FCPATH . 'public/build/manifest.json';
            if (!file_exists($manifestPath)) {
                $manifestPath = FCPATH . 'build/manifest.json';
            }
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
            $manifestPath = FCPATH . 'public/build/manifest.json';
            if (!file_exists($manifestPath)) {
                $manifestPath = FCPATH . 'build/manifest.json';
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>TALAbahan System</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <meta name="theme-color" content="#020617">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TALAbahan">
    <link rel="apple-touch-icon" href="<?= base_url('images/seafood.png') ?>">
    
    <!-- Leaflet: loaded dynamically by pages that need maps (not globally) -->

    <!-- reCAPTCHA v2: loaded by Vue composable (recaptchaLoader.js) -->
    <script src="<?= base_url('app-config.js') ?>"></script>

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
    <div id="boot-splash" style="position:fixed;inset:0;z-index:99999;background:#020617;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:opacity .4s ease">
      <img src="<?= base_url('images/seafood.png') ?>" alt="TALAbahan" style="width:80px;height:80px;border-radius:20px;object-fit:cover;margin-bottom:20px;animation:pulse 2s ease-in-out infinite">
      <div style="color:#94a3b8;font-size:14px;font-family:-apple-system,BlinkMacSystemFont,sans-serif;text-align:center;line-height:1.6">
        <div id="boot-text">Connecting to TALAbahan Secure Servers...</div>
        <div id="boot-dots" style="margin-top:8px;letter-spacing:4px;color:#475569"></div>
      </div>
    </div>
    <style>
      @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(.96)}}
      @keyframes fadeOut{to{opacity:0;pointer-events:none}}
      @media (display-mode: standalone), (max-width: 768px) {
        #app > div:first-child { padding-top: env(safe-area-inset-top, 0px); }
      }
    </style>
    <script>
    (function(){
      var splash=document.getElementById('boot-splash');
      var dotEl=document.getElementById('boot-dots');
      var dotCount=0;
      var dotTimer=setInterval(function(){dotCount=(dotCount%3)+1;dotEl.textContent='.'.repeat(dotCount)},500);
      var loaded=false;
      function hideSplash(){
        if(loaded)return;loaded=true;
        clearInterval(dotTimer);
        splash.style.opacity='0';
        setTimeout(function(){splash.remove()},500);
      }
      window.addEventListener('load',function(){
        setTimeout(hideSplash,600);
      });
      setTimeout(function(){
        document.getElementById('boot-text').textContent='Server is waking up, please wait...';
      },5000);
      setTimeout(function(){
        if(!loaded)document.getElementById('boot-text').textContent='Almost ready...';
      },15000);
    })();
    </script>
    <?php echo inertia_div($page); ?>
    <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch(() => {});
      });
    }
    </script>
</body>
</html>
