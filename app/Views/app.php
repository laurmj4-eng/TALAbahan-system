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
    <title>MJ Talabahan System</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <meta name="theme-color" content="#020617">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-name" content="<?= csrf_token() ?>">
    <meta name="csrf-header" content="X-CSRF-TOKEN">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MJ Talabahan">
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

    <div id="offline-screen" style="display:none;position:fixed;inset:0;z-index:99998;background:#020617;flex-direction:column;align-items:center;justify-content:center;padding:32px;padding-top:env(safe-area-inset-top,32px);padding-bottom:env(safe-area-inset-bottom,32px);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;text-align:center">
      <div style="width:72px;height:72px;border-radius:50%;background:rgba(239,68,68,.12);display:flex;align-items:center;justify-content:center;margin-bottom:24px">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 1l22 22"/><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/><path d="M10.71 5.05A16 16 0 0 1 22.56 9"/><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>
        </svg>
      </div>
      <h2 style="color:#f1f5f9;font-size:20px;font-weight:700;margin:0 0 12px;letter-spacing:-.3px">No Internet Connection</h2>
      <p style="color:#64748b;font-size:14px;line-height:1.7;margin:0 0 28px;max-width:300px">MJ Talabahan requires an active internet connection to process transactions. Please check your mobile data or Wi-Fi.</p>
      <button onclick="location.reload()" style="background:#ef4444;color:#fff;border:none;border-radius:12px;padding:14px 36px;font-size:15px;font-weight:600;cursor:pointer;letter-spacing:.2px;transition:transform .15s,background .15s" onmousedown="this.style.transform='scale(.96)'" onmouseup="this.style.transform='scale(1)'">Retry Connection</button>
    </div>
    <style>
      @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(.96)}}
      @keyframes fadeOut{to{opacity:0;pointer-events:none}}
      @media (display-mode: standalone), (max-width: 768px) {
        #app > div:first-child { padding-top: env(safe-area-inset-top, 0px); }
      }
    </style>

    <?php echo inertia_div($page); ?>
    <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch(() => {});
      });
    }
    (function(){
      var offline=document.getElementById('offline-screen');
      var app=document.getElementById('app');
      var retryTimer=null;
      var isOfflineShown=false;

      function showOffline(){
        if(isOfflineShown) return;
        isOfflineShown=true;
        offline.style.display='flex';
        if(app) app.style.display='none';
        // Auto-retry every 3 seconds
        if(!retryTimer) retryTimer=setInterval(probeNetwork, 3000);
      }
      function hideOffline(){
        isOfflineShown=false;
        offline.style.display='none';
        if(app) app.style.display='';
        if(retryTimer){clearInterval(retryTimer);retryTimer=null;}
      }

      // Real network probe — never trust navigator.onLine alone
      function probeNetwork(){
        // 1. Ask Android native bridge if available (most reliable in WebView)
        if(window.AndroidBridge && typeof window.AndroidBridge.isConnected==='function'){
          try{
            if(window.AndroidBridge.isConnected()){hideOffline();return;}
          }catch(e){}
        }
        // 2. Try loading a tiny resource with cache-busting
        var img=new Image();
        img.onload=function(){hideOffline();};
        img.onerror=function(){
          // 3. Fallback: fetch HEAD request (no-cors removed — opaque responses always resolve)
          fetch('/app-config.js?_nc='+Date.now(),{method:'HEAD',cache:'no-store'})
            .then(function(r){hideOffline();})
            .catch(function(){
              // Genuinely offline — show the screen
              showOffline();
            });
        };
        img.src='/favicon.ico?_nc='+Date.now();
      }

      // Listen for browser events but always verify with a probe
      window.addEventListener('offline',function(){probeNetwork();});
      window.addEventListener('online',function(){hideOffline();});

      // On initial load: always probe because Android WebView lies about navigator.onLine
      probeNetwork();
    })();
    </script>
</body>
</html>
