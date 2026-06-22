# Fix Google Sign-In: Account Picker Skipped

## The Bug

When Chrome opens `mobile_login.php`, it skips the Google account picker and immediately sends the user back to the app. The user wants to see the Google account picker to choose which Gmail to use.

### Root Cause

`mobile_login.php` uses `onAuthStateChanged(auth, callback)`. If the user was previously signed in via Firebase in Chrome, this fires **immediately** with the cached user — before `signInWithRedirect` is ever called. The code then calls `handleSuccess(user)` which fires the `talabahan://auth` deep link, sending the user straight back to the app without ever showing Google.

**Current buggy logic in `mobile_login.php`:**
```javascript
onAuthStateChanged(auth, (user) => {
    if (user) {
        handleSuccess(user);  // ← FIRES IMMEDIATELY WITH CACHED USER
    } else {
        signInWithRedirect(auth, provider);  // ← NEVER REACHED
    }
});
```

### The Fix

**Only trust `getRedirectResult`** — it returns the user ONLY when returning from a Google redirect. On first visit it returns null, which means we should `signOut()` then `signInWithRedirect`. Remove the `onAuthStateChanged` listener entirely.

---

## Only File to Edit

### `app/Views/mobile_login.php` — replace JavaScript logic (lines 86–151)

Replace the entire `<script type="module">` block with:

```html
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
    import {
        getAuth,
        GoogleAuthProvider,
        signInWithRedirect,
        getRedirectResult
    } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

    const statusEl = document.getElementById('status');
    const loadingState = document.getElementById('loading-state');
    const errorContainer = document.getElementById('error-container');
    const errorMsg = document.getElementById('error-msg');

    function showError(msg) {
        loadingState.classList.add('hidden');
        errorContainer.classList.remove('hidden');
        errorMsg.textContent = msg;
    }

    function handleSuccess(user) {
        statusEl.textContent = 'Redirecting back to app...';
        const email = encodeURIComponent(user.email);
        const name = encodeURIComponent(user.displayName || '');
        const callbackUrl = window.BASE_URL + 'auth/mobile-callback?email=' + email + '&name=' + name;
        window.location.href = 'talabahan://auth?redirect=' + encodeURIComponent(callbackUrl);
    }

    if (!window.FIREBASE_CONFIG || !window.FIREBASE_CONFIG.apiKey) {
        showError('Firebase is not configured. Please contact support.');
    } else {
        const app = initializeApp(window.FIREBASE_CONFIG);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();
        provider.setCustomParameters({ prompt: 'select_account' });

        getRedirectResult(auth).then((result) => {
            if (result && result.user) {
                handleSuccess(result.user);
            } else {
                auth.signOut().then(() => {
                    signInWithRedirect(auth, provider);
                });
            }
        }).catch((error) => {
            console.error('Google auth error:', error);
            showError('Authentication failed: ' + (error.message || 'Unknown error'));
        });
    }
</script>
```

### Key Changes
1. **Removed `onAuthStateChanged`** — it fires immediately with cached user, skipping Google
2. **Removed `onAuthStateChanged` import** — no longer needed
3. **Only use `getRedirectResult`** — returns user ONLY when returning from a Google redirect
4. **Always `signOut()` then `signInWithRedirect()` on first visit** — forces Google to show the account picker every time

---

## No Changes Needed in These Files

| File | Why it's fine |
|------|---------------|
| `android-shell/.../MainActivity.java` | `shouldOverrideUrlLoading` correctly opens Chrome for Google URLs. `handleDeepLinkIntent` correctly extracts `redirect` param. |
| `app/Controllers/Auth.php::mobileCallback()` | Receives email/name, creates user, sets session, returns HTML with localStorage + redirect. |
| `resources/js/Page/LoginPage.vue` | Detects `TALAbahanAndroidApp` user agent, redirects to `/auth/mobile-login`. |
| `android-shell/app/src/main/AndroidManifest.xml` | Deep link intent filter for `talabahan://auth` is correct. |
| `app/Config/Routes.php` | Routes for `/auth/mobile-login` and `/auth/mobile-callback` are correct. |

---

## Complete Flow After Fix

```
1. User taps "Sign in with Google" in app (WebView - LoginPage.vue)
2. WebView navigates to /auth/mobile-login?auth_mode=mobile
3. shouldOverrideUrlLoading opens Chrome with /auth/mobile-login
4. Chrome loads mobile_login.php
5. getRedirectResult returns null (first visit)
6. signOut() clears any cached session
7. signInWithRedirect() → Chrome redirects to Google
8. Google account picker shows (prompt: 'select_account')
9. User picks Gmail → Google authenticates
10. Google → Firebase auth handler → back to Chrome's mobile_login.php
11. getRedirectResult returns the user
12. handleSuccess fires deep link: talabahan://auth?redirect=<encoded callback URL>
13. Android catches deep link → loads /auth/mobile-callback?email=...&name=...
14. Server creates/returns user → HTML sets localStorage → redirects to dashboard
15. User is logged in
```

---

## Firebase Config Reference

```js
window.FIREBASE_CONFIG = {
    apiKey: "...",
    authDomain: "sefood-d603d.firebaseapp.com",
    projectId: "sefood-d603d",
};
window.GOOGLE_CLIENT_ID = "220178423865-...";
window.BASE_URL = "https://talabahan-system-1.onrender.com/";
```

## Constraints

1. Google blocks OAuth in Android WebViews — auth MUST happen in Chrome
2. `getRedirectResult` only works once — returns the user only when returning from a redirect
3. `onAuthStateChanged` is dangerous — fires immediately with cached users, bypassing Google
4. OAuth consent screen must be "Testing" mode in Google Cloud Console
5. `prompt: 'select_account'` forces Google to show the account picker even with one account
