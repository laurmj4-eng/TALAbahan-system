# TALAbahan System — Persistent Context Document

> **Generated:** 2026-06-28  
> **Purpose:** If a brand-new AI agent reads this file, it should instantly grasp the entire system without scanning every source file.  
> **Read this first before making any changes.**

---

## 1. Project Overview

### What is TALAbahan?

A full-stack, mobile-first **seafood commerce dashboard & backend** for a local fish stall in Bacolod/Ilog, Philippines. It functions as a **POS system**, **online ordering platform**, **inventory management**, and **AI chatbot assistant** — all accessible via a mobile Android app and desktop web browser.

### Current Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | CodeIgniter 4 (PHP 8.2) — MVC framework |
| **Frontend** | Vue 3 SPA + Inertia.js 3.x + Tailwind CSS 4 + Vite 5 |
| **Database** | MySQL 8.0 hosted on Aiven Cloud (remote, SSL-encrypted) |
| **AI Chatbot** | Node.js 20 / Express 5 server (`server.js`) → OpenRouter API |
| **Hosting** | Docker on Render.com (Apache 2.4 + PHP-FPM, port 8080) |
| **Android Shell** | Java native `WebView` wrapper (compileSdk 34, minSdk 24) |
| **CI/CD** | GitHub Actions — APK build (push to main), PHPUnit tests, Prettier lint |
| **Auth** | Firebase Auth (Google provider) + reCAPTCHA v2 + local email/password |
| **Other** | Chart.js, GSAP, Leaflet maps, PWA service worker, Axios |

### Production URLs

- **Web App:** `https://talabahan-system-1.onrender.com`
- **APK Download:** `https://github.com/laurmj4-eng/TALAbahan-system/releases/download/latest/app-debug.apk`
- **Version Manifest:** `https://talabahan-system-1.onrender.com/version.json`
- **GitHub:** `https://github.com/laurmj4-eng/TALAbahan-system`

---

## 2. Core Architecture & Folder Structure

```
TALAbahan-system/
├── app/                          # CodeIgniter 4 MVC application
│   ├── Config/                   #   Routes, App, Database, Filters, Recaptcha, etc.
│   ├── Controllers/              #   Auth.php, Home.php, BaseController.php + Admin/, Customer/, Staff/
│   ├── Database/                 #   Migrations/ (23 migrations), Seeds/
│   ├── Filters/                  #   adminGuard, customerGuard, staffGuard, apiAuth, rateLimit, etc.
│   ├── Helpers/                  #   inertia_helper.php, recaptcha_helper.php
│   ├── Libraries/                #   Inertia.php (SSR renderer), Inertia.production.php
│   ├── Models/                   #   19 models (User, Order, Product, Sales, Voucher, etc.)
│   └── Views/                    #   app.php (layout), mobile_login.php, errors/
│
├── android-shell/                # Android native project (Gradle build)
│   ├── build.gradle              #   Project-level (v8.2.0)
│   ├── settings.gradle           #   Root project "TALAbahan"
│   ├── gradle.properties         #   AndroidX, non-transitive R class
│   └── app/
│       ├── build.gradle          #   versionCode 6, compileSdk 34, minSdk 24
│       └── src/main/
│           ├── AndroidManifest.xml   #   Intent filters for talabahan:// + https deep links
│           ├── java/com/mjseafood/app/
│           │   ├── MainActivity.java      #   WebView shell, deep link handler, auto-updater
│           │   └── firebase/
│           │       └── TALAbahanMessagingService.java   #   FCM (commented out)
│           └── res/
│               ├── layout/activity_main.xml      #   WebView + ProgressBar + Chrome overlay
│               ├── values/styles.xml             #   Material Light NoActionBar
│               └── xml/                          #   network_security_config, file_paths, data_extraction
│
├── resources/js/                 # Vue 3 SPA source
│   ├── main.js                   #   Inertia + Axios boot
│   ├── App.vue                   #   Root Vue component
│   ├── Page/                     #   Vue page components per role
│   │   ├── LoginPage.vue         #   Email/password + Google OAuth
│   │   ├── RegisterPage.vue
│   │   ├── admin/                #   10 pages (Dashboard, POS, Orders, Products, etc.)
│   │   ├── staff/                #   7 pages
│   │   └── customer/             #   6 pages
│   └── composables/              #   useRecaptcha, recaptchaLoader, usePerformance
│
├── public/                       # Web server document root
│   ├── index.php                 #   CI4 front controller + CORS headers
│   ├── .htaccess                 #   Apache rewrite rules
│   ├── router.php                #   PHP dev server router
│   ├── version.json              #   {versionCode: 6, apkUrl: "..."}
│   ├── manifest.json             #   PWA manifest
│   ├── service-worker.js         #   Offline cache (talabahan-v2)
│   ├── app-config.js             #   Dynamic JS config (served by AppConfig controller)
│   ├── build/                    #   Vite production output (manifest.json + hashed assets)
│   └── images/                   #   Static images (logo, backgrounds)
│
├── system/                       # CodeIgniter 4 framework (vendored)
├── vendor/                       # Composer PHP dependencies
├── node_modules/                 # npm dependencies
├── writable/                     # CI4 writable: session/, cache/, logs/, debugbar/
├── uploads/                      # User-uploaded files (local dev fallback)
│
├── server.js                     # Node.js Express AI chatbot backend (OpenRouter)
├── vite.config.js                # Vite build config (outDir: public/build)
├── package.json                  # npm scripts, deps (vue, inertia, axios, chart.js, gsap, etc.)
├── composer.json                 # PHP deps (codeigniter4/framework, dompdf, etc.)
├── Dockerfile.render             # Multi-stage Docker build (builder + Apache)
├── Dockerfile                    # Fallback Docker build
├── docker-entrypoint.sh          # Render entry: generate .env, symlink uploads, run migrations
├── build.sh                      # Build script (composer install, npm build, permissions)
├── render.yaml                   # Render Blueprint (Docker, env vars, persistent disk)
├── AGENTS.md                     # Auto-update APK release process
│
├── .env                          # Local dev environment (CI_ENVIRONMENT=development)
├── .env.example                  # Template for environment variables
├── .github/workflows/
│   ├── android-build.yml         # Build APK on push → GitHub Release + artifact
│   └── ci.yml                    # Prettier lint + PHPUnit tests on push/PR
│
├── mj_chatbot.sql                # Database schema export (reference)
├── install-apk.bat               # Windows USB install script
├── install-test.ps1              # PowerShell auto-download + install
└── firebase.json                 # Firebase Hosting config (unused in production)
```

---

## 3. Database Schema

### Tables & Key Relationships

| Table | Primary Key | Foreign Keys | Purpose |
|-------|-------------|--------------|---------|
| `users` | `id` | — | Login, role (admin/staff/customer), AI quota columns |
| `orders` | `id` | `user_id` → `users.id` | Order header (transaction_code, amounts, status, shipping, payments) |
| `order_items` | `id` | `order_id` → `orders.id` (CASCADE), `product_id` | Line items (unit_price, cost_price, quantity, subtotal) |
| `products` | `id` | — | Name, cost/selling price, stock, unit, aggregation columns |
| `sales_history` | `id` | — | POS quick-sale records |
| `shipping_locations` | `id` | — | Barangay-level shipping fees |
| `vouchers` | `id` | — | Discount codes (fixed/percent, scope, min order) |
| `voucher_redemptions` | `id` | `voucher_id`, `order_id` | Redemption audit trail |
| `settings` | `id` | — | Key-value store (e.g., ship_to_all) |
| `payment_attempts` | `id` | `order_id` | Payment gateway log |
| `cod_compliance` | `id` | — | Customer COD failure tracking |
| `product_payment_constraints` | `id` | `product_id` | Per-product payment method restrictions |
| `order_status_history` | `id` | `order_id` | Status change audit trail |
| `order_reviews` | `id` | `order_id` (unique) | Customer ratings + comments |
| `refund_requests` | `id` | `order_id` | Customer refund submissions |
| `activity_logs` | `id` | `user_id` | All user actions for audit |
| `damaged_ledger` | `id` | — | Damaged goods tracking |
| `losses` | `id` | — | Financial loss tracking |
| `firebase_users_tracking` | `uid` | — | Chatbot daily prompt count (used by Node.js server only) |

### Order Status Lifecycle

```
Pending → Processing → Shipped → Completed
  ↓          ↓
Cancelled  Refunded
```

### Product Aggregation

`ProductModel::updateAggregates()` recalculates `real_sold_count` and `real_rating` from `order_items` + `order_reviews`. Triggered by `OrderModel::triggerProductAggregates` on status change.

---

## 4. Authentication & Deep Link Handoff Logic (Crucial)

There are **three authentication paths**. This section documents all three, with special emphasis on the Android deep-link flow (the most complex and hardest to debug).

### Path A: Email/Password Login

1. User fills email + password on `LoginPage.vue`
2. reCAPTCHA v2 widget appears after first interaction (unless trusted admin)
3. `POST /api/auth/verify` with `provider=email`
4. Server validates reCAPTCHA, verifies password hash (`password_verify`), sets CI4 session
5. Returns JSON with `redirect` URL → Vue calls `window.location.href`
6. Rate-limited: 10 attempts/email/5min, 20/IP/5min, exponential backoff on client

### Path B: Google OAuth via Firebase (SPA — Desktop & Standard Mobile)

1. User taps "Sign in with Google" in `LoginPage.vue`
2. Firebase SDK (`signInWithPopup`) opens Google account picker
3. On success, `POST /api/auth/verify` with `provider=google`, email, name
4. Server finds or auto-creates a `customer` account, sets session
5. Returns redirect URL, Vue navigates to dashboard

### Path C: Google OAuth via External Chrome (Android WebView — the critical path)

This is the **primary auth method for the Android app** because Firebase popups don't work inside WebViews.

```
┌─────────────────────────────────────────────────────────────────┐
│  FLOW DIAGRAM                                                   │
│                                                                 │
│  [WebView]                             [Chrome]                 │
│     │                                      │                    │
│     │ 1. User taps "Sign in with Google"   │                    │
│     │    Vue detects TALAbahanAndroidApp   │                    │
│     │    in user agent                     │                    │
│     │──────────────────────────────────────│                    │
│     │                                      │                    │
│     │ 2. AndroidBridge.openInBrowser(url)  │                    │
│     │    → Chrome opens /auth/mobile-login │                    │
│     │─────────────────────────────────────>│                    │
│     │                                      │                    │
│     │    [ChromeLoadingOverlay VISIBLE]    │ 3. Firebase signIn │
│     │                                      │    WithPopup/      │
│     │                                      │    WithRedirect    │
│     │                                      │    → Google        │
│     │                                      │       completes    │
│     │                                      │                    │
│     │   4. Firebase redirects Chrome to:   │                    │
│     │      /auth/mobile-callback           │                    │
│     │      ?email=...&name=...&app_return=1│                    │
│     │                                      │                    │
│     │   5. PHP (Auth::mobileCallback)      │                    │
│     │      • Creates/finds user            │                    │
│     │      • Sets session                  │                    │
│     │      • Since app_return=1:           │                    │
│     │        renders HTML with "Return to  │                    │
│     │        App" button whose href is:    │                    │
│     │        talabahan://auth?redirect=    │                    │
│     │        [encoded callback URL]        │                    │
│     │                                      │                    │
│     │   6. User taps "Return to App"       │                    │
│     │   7. Chrome fires intent:            │                    │
│     │      talabahan://auth?redirect=...   │                    │
│     │<─────────────────────────────────────│                    │
│     │                                      │                    │
│     │ 8. Android catches intent in          │                    │
│     │    handleDeepLinkIntent()             │                    │
│     │    → Extracts redirect param         │                    │
│     │    → webView.loadUrl(redirect)       │                    │
│     │    → ChromeLoadingOverlay GONE       │                    │
│     │                                      │                    │
│     │ 9. PHP sets session cookies in       │                    │
│     │    the WebView response              │                    │
│     │    302 redirects to dashboard        │                    │
│     │                                      │                    │
│     │ 10. WebView loads dashboard          │                    │
│     │     (cookies persist)                │                    │
│     │                                      │                    │
└─────────────────────────────────────────────────────────────────┘
```

#### Key Code Locations

| Component | File | Purpose |
|-----------|------|---------|
| **Vue detection & kick-out** | `resources/js/Page/LoginPage.vue:621-673` | Checks `TALAbahanAndroidApp` UA, calls `openInBrowser` |
| **Android native JS bridge** | `android-shell/.../MainActivity.java:554-568` | `@JavascriptInterface openInBrowser(String)` |
| **WebView intercept** | `MainActivity.java:326-361` | `shouldOverrideUrl()` — blocks `intent://`, routes `talabahan://`, kicks `/mobile-login` to Chrome |
| **Chrome login page** | `app/Views/mobile_login.php` | Firebase Auth popup/redirect with error handling |
| **Callback handler (PHP)** | `app/Controllers/Auth.php:186-287` | `mobileCallback()` — creates user, sets session, returns deep link HTML or 302 |
| **Deep link return (HTML)** | `Auth.php:246-278` | HTML page with `talabahan://auth?redirect=...` button |
| **Android intent handler** | `MainActivity.java:160-195` | `handleDeepLinkIntent()` — parses `talabahan://auth` and HTTPS callback URLs |
| **AndroidManifest filters** | `android-shell/app/src/main/AndroidManifest.xml:30-47` | `talabahan://auth` scheme + HTTPS pathPrefix |
| **Chrome overlay (XML)** | `android-shell/app/src/main/res/layout/activity_main.xml:24-59` | "Opening Chrome..." overlay |
| **Chrome overlay dismiss** | `MainActivity.java:181,189,747` | Hidden on deep link return and `onResume` |

### URL Parameter Convention

Every URL loaded inside the WebView gets `&auth_mode=mobile` appended (line 348 in `MainActivity.java`) to let the backend know it's being served inside the app.

---

## 5. Current State & Known Workarounds

### 5.1 Chrome "missing-initial-state" Error

**Problem:** Firebase's `getRedirectResult()` throws `auth/missing-initial-state` when the redirect-based sign-in is interrupted (user closes Chrome, loses network, etc.). This manifests as a blank page with no error message.

**Fix (mobile_login.php:244-249):**
- Caught in the `try { getRedirectResult() } catch` block
- Shows a user-friendly error: "Previous sign-in was interrupted. Try the popup method instead."
- Offers a "Try Popup" button
- Also detected in `LoginPage.vue` via `localStorage.getItem('googleSignInInProgress')`

### 5.2 Popup Blocked Fallback

**Problem:** Firefox and some Android browsers block `window.open()` calls.

**Fix:**
- **Web:** Catches `auth/popup-blocked`, falls back to `signInWithRedirect` → `getRedirectResult`
- **Android:** Never uses popup in WebView; always kicks to external Chrome via `openInBrowser`

### 5.3 COOP/COEP Headers for Firebase Popup Compatibility

**Problem:** Firebase Auth popups fail to communicate with the parent window when `Cross-Origin-Opener-Policy` is `same-origin`.

**Fix (`BaseController.php:44-45`):**
```php
$this->response->setHeader('Cross-Origin-Opener-Policy', 'unsafe-none');
$this->response->setHeader('Cross-Origin-Embedder-Policy', 'unsafe-none');
```

### 5.4 Android WebView Cookie Sync

**Problem:** Cookies set by the PHP backend during the deep-link callback must persist when the WebView loads the dashboard.

**Fix (`MainActivity.java:306-311` and `onResume():741-744`):**
```java
CookieManager cookieManager = CookieManager.getInstance();
cookieManager.setAcceptCookie(true);
// In onResume:
CookieManager.getInstance().flush();
```

### 5.5 Custom Native Loading Overlay

**Problem:** When Chrome is opened for Google Auth, the WebView shows a white screen. Users don't know what's happening.

**Fix (`activity_main.xml:24-59`):**
- A `FrameLayout` overlay (`chromeLoadingOverlay`) with:
  - Indeterminate cyan `ProgressBar`
  - TextView: "Opening Chrome to verify your account... Please wait."
- Shown before launching Chrome intent (`MainActivity.java:339`)
- Hidden on deep-link return (`MainActivity.java:181`) and `onResume()` (`MainActivity.java:746`)

### 5.6 Vite Environment Detection

**Problem:** `CI_ENVIRONMENT` is locked at PHP bootstrap time and can be wrong during Docker builds.

**Fix (`app/Views/app.php:15-19`):**
```php
function vite_is_dev(): bool {
    return file_exists(FCPATH . 'hot');
}
```
Uses presence of `public/hot` (Vite dev server marker) instead of `ENVIRONMENT` constant.

### 5.7 CORS Configuration (Three Layers)

| Layer | File | Purpose |
|-------|------|---------|
| 1 | `public/index.php:3-14` | Extremely permissive CORS for InfinityFree deployment |
| 2 | `app/Controllers/BaseController.php:42-65` | Dynamic CORS per origin (Vercel, localhost) + COOP/COEP |
| 3 | `server.js:20-30` | Node.js CORS for AI chatbot API |

### 5.8 Render Disk Persistence for Uploads

**Problem:** Docker containers lose `public/uploads` on restart. Render.com provides a persistent disk at `/var/data`.

**Fix (`docker-entrypoint.sh:78-95`):**
```bash
if [ "${RENDER}" = "true" ] && [ -d "/var/data" ]; then
    rm -rf "${PUBLIC_UPLOADS}"
    ln -sfn /var/data/uploads "${PUBLIC_UPLOADS}"
fi
```
Checks `$RENDER` env var (not directory existence) to avoid false positives.

### 5.9 Stock Tracking (Disabled)

**Problem:** The business does not track stock tightly; overselling is handled operationally.

**Fix (`ProductModel.php:63-67`, `72-76`):**
```php
public function reduceStock(int $productId, float $qty): bool {
    return true; // Stock tracking disabled
}
public function increaseStock(int $productId, float $qty): bool {
    return true; // Stock tracking disabled
}
```

### 5.10 Safe-Area Insets for Mobile

**Problem:** Notch/punch-hole cameras and rounded corners overlap the app UI.

**Fix:** 
- `viewport-fit=cover` in `<head>` (`app.php:85`)
- `env(safe-area-inset-top)` and `env(safe-area-inset-bottom)` in inline styles
- System UI visibility flags in `onPageCommitVisible()` (`MainActivity.java:440-451`)

### 5.11 Offline Detection

An inline `#offline-screen` overlay is shown when `window.offline` fires, hiding the app content. It includes a "Retry Connection" button.

### 5.12 Database Connection Flow

**File:** `app/Config/Database.php`

1. `ENVIRONMENT=testing` → SQLite in-memory
2. `ENVIRONMENT=production` or env vars present → Read credentials from `getenv()` using CI4 dotted keys or `DB_*` aliases
3. Otherwise → XAMPP defaults (`localhost`, `mj_chatbot`, `root`, empty password)
4. In production, `DBDebug` is forced `false`

### 5.13 AI Chatbot Architecture

- **Backend:** `server.js` (Node.js/Express) listens on port 3000 (or `$PORT`)
- **Endpoint:** `POST /api/chat` — receives `{uid, history, modelName}`
- **Rate limit:** 15 requests/min per IP
- **Daily quota:** 20 prompts/user/day (tracked in `firebase_users_tracking` table)
- **Model:** OpenRouter API (`openrouter/auto` default), streamed SSE response
- **Sales context:** If user asks about sales, the server fetches data from PHP bridge (`PHP_BRIDGE_URL`)
- **Failsafe:** If MySQL is down, the server continues in no-DB mode without daily-limit tracking

---

## 6. Auto-Updater Engine

### How It Works

1. `MainActivity.onCreate()` calls `checkForUpdates()` on a background thread
2. Background thread fetches `BASE_URL/version.json` (`MainActivity.java:79`):
   ```json
   {
     "versionCode": 6,
     "apkUrl": "https://github.com/laurmj4-eng/TALAbahan-system/releases/download/latest/app-debug.apk"
   }
   ```
3. Compares `remoteVersion` to `PackageInfo.versionCode`
4. If remote > local, calls `mainHandler.post(() -> downloadUpdate(apkUrl))`
5. Downloads APK to `getCacheDir()/updates/talabahan-update.apk`
6. Installs via `FileProvider.getUriForFile()` → `Intent.ACTION_VIEW`

### Release Process

1. Bump `versionCode` in `android-shell/app/build.gradle:13` (e.g., versionCode 6)
2. Update `public/version.json` with matching `versionCode`
3. Push to `main` branch
4. GitHub Actions (`android-build.yml`) builds APK, uploads to Release `latest`

### CI Pipeline Files

| File | Trigger | What It Does |
|------|---------|--------------|
| `.github/workflows/android-build.yml` | Push to `main` | Builds debug APK, uploads artifact + GitHub Release |
| `.github/workflows/ci.yml` | Push/PR to `main` | Prettier format check + PHPUnit tests with MySQL service |

---

## 7. Key Environment Variables (.env)

| Variable | Purpose |
|----------|---------|
| `CI_ENVIRONMENT` | `development` or `production` |
| `app.baseURL` | Base URL for backend |
| `database.default.*` | MySQL connection (host, db, user, pass, port) (Aiven Cloud remote) |
| `encryption.key` | CI4 session encryption key |
| `OPENROUTER_API_KEY` | AI chatbot model access |
| `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` | reCAPTCHA v2 |
| `FIREBASE_*` | Firebase project config (apiKey, authDomain, projectId, etc.) |
| `GOOGLE_CLIENT_ID` | Web Google OAuth client ID |
| `CORS_ORIGIN` | Allowed CORS origins (comma-separated) |
| `INERTIA_VERSION` | Optional cache-busting version string |

### Render-Specific Vars (set in `render.yaml`)

`database.default.hostname`, `database.default.database`, `database.default.username`, `database.default.password`, `database.default.port`, `encryption.key`, `RECAPTCHA_*`, `FIREBASE_*`, `CORS_ORIGIN`, `INERTIA_VERSION`, `app.forceGlobalSecureRequests`, `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `OPENROUTER_API_KEY` — all marked `sync: false` (set manually in Render dashboard).

---

## 8. Security Notes

- **CSRF:** Global except for public API routes (auth, chatbot streaming, some POS AJAX)
- **Rate limiting:** Login attempts (10/email/5min, 20/IP/5min), chatbot (15/min/IP), client-side exponential backoff
- **Password hashing:** `password_hash(PASSWORD_DEFAULT)` via CI4 model callback
- **Session:** CI4 file-based sessions in `writable/session/`
- **SSL:** Enforced in production (`forceGlobalSecureRequests`), Android network config blocks cleartext
- **CORS:** Three layers (index.php, BaseController, server.js) with origin whitelisting
- **Service Worker:** Caches build assets + navigation requests for offline resilience
- **No secrets in HTML:** Firebase config and keys are served via `/app-config.js` (PHP controller), never in `view-source`

---

## 9. Common Troubleshooting

| Problem | Likely Cause | Check First |
|---------|-------------|-------------|
| Google Auth fails in Android app | Deep link not registered in manifest | `AndroidManifest.xml` intent filters |
| Chrome stays blank after auth | `mobileLogin()` redirect URL wrong | `Auth::mobileCallback()` `$deepLinkData` |
| "missing-initial-state" error | Interrupted redirect flow | `mobile_login.php` catch block; localStorage `googleSignInInProgress` |
| Auth works but WebView doesn't load dashboard | Cookies not synced | `CookieManager.getInstance().flush()` in `onResume()` |
| APK auto-update not triggering | versionCode mismatch | Compare `app/build.gradle` vs `public/version.json` |
| Vite assets 404 in production | Manifest path wrong | `vite_asset()` checks `FCPATH . 'public/build/manifest.json'` then `FCPATH . 'build/manifest.json'` |
| Login stuck on "Connecting..." | Firebase config missing | Check `/app-config.js` for `FIREBASE_CONFIG` |
| Uploads disappear after deploy | Render persistent disk not mounted | Check `docker-entrypoint.sh` symlink logic; `$RENDER` env var |
| Chatbot returns no response | OpenRouter API key invalid | Check `server.js` logs; test key directly with curl |

---

## 10. Session Log — June 29, 2026

### 10.1 App Installation Tracker & Device Analytics
- **Migration** `2026-06-29-000001_AddAppVersionAndLastConnected.php` — formalizes `app_version` (VARCHAR 20) and `last_connected` (DATETIME) columns on `fcm_device_tokens`
- **`FcmTokenModel::getDeviceAnalytics()`** — returns `total_installs`, `active_devices`, `online_now` (active in 24h), `unique_models`, `unique_versions`
- **`getDeviceTrackingData()`** — now injects `is_online` boolean per device
- **Developer Dashboard** — 4 stat cards: Total App Installs, Online Now, Platforms, App Versions; device table with Status pill (Online/Inactive), TRUSTED chip, "Yesterday at X" relative time, token copy on row click

### 10.2 Native Pull-to-Refresh (SwipeRefreshLayout — legacy, now disabled)
- Added `androidx.swiperefreshlayout:swiperefreshlayout:1.1.0` dependency
- Wrapped WebView in `<SwipeRefreshLayout>` in `activity_main.xml`
- Had scroll-sync bugs with `OnChildScrollUpCallback` → replaced with `ViewTreeObserver` + `setEnabled()` → still unreliable
- **Final state (81aaa53+):** `swipeRefreshLayout.setEnabled(false)` — permanently disabled; WebView gets 100% touch events

### 10.3 Web-Based Pull-to-Refresh (active solution)
- **`resources/js/composables/usePullToRefresh.js`** — touch composable:
  - Only activates when `.smooth-scroll-container` or `documentElement` is at `scrollTop === 0`
  - 10px dead zone before spinner appears (prevents hold-to-trigger)
  - 0.5 damping, 90px max pull, 75px activation threshold
  - `window.location.reload()` with 400ms delay (replaced buggy Inertia `router.reload()`)
- **`resources/js/components/PullToRefreshOverlay.vue`** — fixed-position spinner:
  - `inset-x-0` + `flex justify-center` for perfect centering
  - Responsive sizing: `w-12 h-12` mobile, `w-14 h-14` desktop
  - Frosted glass (`bg-white/90 backdrop-blur`), `shadow-2xl`
  - Slides with finger via `translateY()`, snaps back on cancel
- **`main.js`** — mounts overlay globally alongside Inertia app (no layout changes needed)

### 10.4 Key Decisions
- Web-based pull-to-refresh over native: avoids all scroll-sync bugs inherent to WebView + SwipeRefreshLayout
- `window.location.reload()` over `router.reload()`: Inertia reload caused white screen flash + spinner stuck on back navigation
- No layout modifications — overlay is mounted as sibling of Inertia app in `main.js` via fragment render
