# Fix Android WebView Google Sign-In Flow

## Goal

Eliminate extra clicks and automate the deep link return so that Google Sign-In requires **ONE tap** in the app, automatically opens Chrome for Google auth, and automatically returns to the app after authentication.

---

## Architecture

An Android WebView app (package `com.mjseafood.app`) wrapping a CodeIgniter 4 web app hosted at `https://talabahan-system-1.onrender.com`. Firebase project: `sefood-d603d`.

Google blocks OAuth inside Android WebViews (`Error 403: disallowed_useragent`), so the Google auth flow MUST happen in Chrome (the device's default browser). After auth completes, a custom scheme deep link (`talabahan://auth`) brings the user back to the app automatically.

---

## Desired Flow (ONE click total)

1. User taps "Sign in with Google" in the app's WebView (LoginPage.vue)
2. `shouldOverrideUrlLoading` detects the Google/Firebase URL → opens Chrome with `/auth/mobile-login`
3. Chrome loads `mobile_login.php` → **immediately auto-redirects to Google OAuth** (no button, no user interaction)
4. Google account picker shows in Chrome → user picks their Gmail
5. Google authenticates → redirects to Firebase auth handler → Firebase redirects back to `mobile_login.php`
6. `mobile_login.php` gets the user → **immediately fires `talabahan://auth?redirect=<encoded callback URL>` deep link**
7. Android catches the deep link → loads `/auth/mobile-callback?email=...&name=...`
8. Server creates/returns user session → returns HTML that sets localStorage and redirects to dashboard
9. User is logged in — automatic, seamless, no extra clicks

---

## Current Broken Flow (3+ clicks)

1. Tap "Sign in with Google" in WebView
2. WebView navigates to `/auth/mobile-login`
3. `shouldOverrideUrlLoading` opens Chrome with `/auth/mobile-login`
4. Chrome shows `mobile_login.php` with ANOTHER "Sign in with Google" button — **user must click again (click 2)**
5. `mobile_login.php` uses `signInWithPopup` (imported from Firebase) which tries to open a popup — this doesn't work when the page was opened via an intent
6. After popup fails or user picks account, there's a "click to go back to app" message — **user must click again (click 3)**
7. `getRedirectResult(auth)` returns null → "Sign-in cancelled"

---

## The Three Files That Need Fixing

### File 1: `app/Views/mobile_login.php`

**Problem:** This page shows a "Sign in with Google" button and uses `signInWithPopup`. It should:

- Remove the button entirely
- On page load, immediately check if we're returning from a Google redirect (check `getRedirectResult` or `onAuthStateChanged`)
- If NOT returning from redirect → immediately call `signInWithRedirect(auth, provider)` (no user interaction)
- If returning from redirect → get the user and immediately fire the deep link
- The deep link format: `window.location.href = 'talabahan://auth?redirect=' + encodeURIComponent(callbackUrl)` where `callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name`

Current code (WRONG):

```php
import { getAuth, GoogleAuthProvider, signInWithPopup } from "firebase-auth.js";
// Has a button, uses signInWithPopup, shows "Sign-in cancelled" on getRedirectResult returning null
```

Should be:

```php
import { getAuth, GoogleAuthProvider, signInWithRedirect, getRedirectResult, onAuthStateChanged } from "firebase-auth.js";
// NO button, auto-redirects on first visit, handles redirect return
// Show spinner "Authenticating..." while in progress
```

### File 2: `android-shell/app/src/main/java/com/mjseafood/app/MainActivity.java`

**`shouldOverrideUrlLoading`** must:

- Intercept `talabahan://` URLs → fire Android Intent
- Intercept Google/Firebase URLs (`accounts.google.com`, `sefood-d603d.firebaseapp.com`) → open Chrome with `/auth/mobile-login?auth_mode=mobile` (NOT the original Google URL)
- Allow all other `BASE_URL` URLs through normally (appending `auth_mode=mobile`)

**`handleDeepLinkIntent`** must:

- Catch `talabahan://auth?redirect=<encoded URL>`
- Extract the `redirect` query parameter
- Load that URL directly in the WebView (it's the `/auth/mobile-callback?email=...&name=...` URL)

### File 3: `app/Controllers/Auth.php` — `mobileCallback()`

This is working correctly — receives `email` and `name` query params, creates/finds user in DB, sets session, returns HTML that writes localStorage and redirects to the appropriate dashboard. **No changes needed here.**

---

## Server-Side Callback Endpoint

`GET /auth/mobile-callback?email=<email>&name=<name>` — Already implemented in `Auth::mobileCallback()`. Creates user if not exists, sets session, returns:

```html
<script>
localStorage.setItem("isLoggedIn", "true");
localStorage.setItem("userRole", "customer");
localStorage.setItem("username", "...");
window.location.href = "/customer/dashboard";
</script>
```

---

## Routes (already configured)

```php
$routes->get('auth/mobile-login', 'Auth::mobileLogin');     // Serves mobile_login.php
$routes->get('auth/mobile-callback', 'Auth::mobileCallback'); // Handles OAuth callback
```

---

## Firebase Config (from `app-config.js`)

```js
window.FIREBASE_CONFIG = {
    apiKey: "...",
    authDomain: "sefood-d603d.firebaseapp.com",
    projectId: "sefood-d603d",
    // ...
};
window.GOOGLE_CLIENT_ID = "220178423865-...";
window.BASE_URL = "https://talabahan-system-1.onrender.com/";
```

---

## Android Deep Link Intent Filter (AndroidManifest.xml)

```xml
<intent-filter>
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data android:scheme="talabahan" android:host="auth" />
</intent-filter>
```

---

## Critical Constraints

1. **Google blocks OAuth in Android WebViews** — auth MUST happen in Chrome
2. **Firebase `signInWithRedirect` stores state in session storage** — the entire redirect flow must happen within the same browser context (Chrome)
3. **`getRedirectResult` only works once** — after consuming, it returns null
4. **`onAuthStateChanged` fires automatically after redirect** — use it alongside `getRedirectResult` as a fallback
5. The `LoginPage.vue` already detects the Android app via user agent (`TALAbahanAndroidApp`) and redirects to `/auth/mobile-login` — this works, no changes needed
6. **OAuth consent screen must be in "Testing" mode** with the test user email added — this is a Google Cloud Console config, not a code issue

---

## What to Fix

1. Rewrite `mobile_login.php` to auto-redirect (no button) and properly handle the redirect return
2. Verify `shouldOverrideUrlLoading` correctly opens Chrome for Google URLs
3. Verify `handleDeepLinkIntent` properly extracts and follows the `redirect` parameter
4. Make the entire flow seamless — one tap in the app → automatic Google sign-in in Chrome → automatic return to app → logged in
