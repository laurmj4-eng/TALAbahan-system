# reCAPTCHA v2 Configuration & Error Diagnosis Guide

## Problem Summary

**Symptoms:**
- Red error message displayed on reCAPTCHA widget
- Network request to `/recaptcha/api2/reload` returns 200 status
- Widget fails verification despite successful API call
- "Please complete the reCAPTCHA verification" error appears

**Root Causes Identified:**

---

## Issue #1: Test Site Key in Production Build

### Location
- [dist/index.html](../dist/index.html#L13)
- [app/Views/app.php](../app/Views/app.php#L19)

### Problem
The hardcoded Google test key `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI` is being used instead of your real production key from environment variables.

```javascript
// ❌ WRONG - Test key only works on localhost
window.RECAPTCHA_SITE_KEY = "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI";

// ✅ CORRECT - Uses environment variable
window.RECAPTCHA_SITE_KEY = "<?= env('RECAPTCHA_SITE_KEY') ?>";
```

### Why It Fails
- The test key is restricted to `localhost` and `127.0.0.1` only
- When you access from any other domain, Google's API returns 200 but the widget fails client-side
- The test key is designed for development/testing, not production

### Solution
**Fixed in:** [app/Views/app.php](../app/Views/app.php#L19)

---

## Issue #2: Missing Environment Configuration

### Problem
There's no `.env` file with `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` defined.

### Solution Steps

1. **Copy the example file:**
   ```bash
   cp .env.example .env
   ```

2. **Get your real keys from Google:**
   - Go to https://www.google.com/recaptcha/admin
   - Sign in with your Google account
   - Create a new site or select existing one
   - Note: Must be **reCAPTCHA v2 (I'm not a robot Checkbox)**

3. **Add your domain(s) to the whitelist:**
   ```
   Domains:
   - localhost (for development)
   - 127.0.0.1 (for XAMPP local testing)
   - your-production-domain.com (for production)
   - www.your-production-domain.com (if applicable)
   ```

4. **Update .env file:**
   ```env
   RECAPTCHA_SITE_KEY = 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
   RECAPTCHA_SECRET_KEY = 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
   ```

---

## Issue #3: Component Using Placeholder Key

### Location
[resources/components/LoginForm.vue](../resources/components/LoginForm.vue#L195-L204)

### Problem
The component was originally using a hardcoded placeholder instead of reading from `window.RECAPTCHA_SITE_KEY`.

### Status
✅ **Already Fixed** - Now correctly uses:
```typescript
// Gets key from window global (set in spa.php)
const RECAPTCHA_SITE_KEY = window.RECAPTCHA_SITE_KEY || 'YOUR_RECAPTCHA_V2_SITE_KEY'

if (!window.RECAPTCHA_SITE_KEY) {
  console.error('[reCAPTCHA] Site key not found. Ensure RECAPTCHA_SITE_KEY is set in .env file')
}
```

---

## Issue #4: Inconsistent Script Loading

### Scripts That Load reCAPTCHA API

1. **[app/Views/spa.php](../app/Views/spa.php#L9-L14)** ✅ Correct
   ```html
   <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
   <script>
     window.RECAPTCHA_SITE_KEY = "<?= env('RECAPTCHA_SITE_KEY') ?>";
   </script>
   ```

2. **[app/Views/app.php](../app/Views/app.php#L13-L19)** ✅ Fixed
   ```html
   <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
   <script>
     window.RECAPTCHA_SITE_KEY = "<?= env('RECAPTCHA_SITE_KEY') ?>";
   </script>
   ```

3. **[dist/index.html](../dist/index.html#L8-L13)** ⚠️ Old Build
   - This is a pre-built dist file with hardcoded test key
   - Regenerate with: `npm run build`

---

## Why 200 Status + UI Error Happens

### Request Flow Breakdown

```
1. Browser sends reCAPTCHA response to /api/auth/verify
   ↓
2. Backend receives request
   curl_exec() → https://www.google.com/recaptcha/api/siteverify
   ↓
3. Google verifies token with SECRET_KEY
   Response (HTTP 200): 
   {
     "success": false,  ← Domain mismatch
     "challenge_ts": "...",
     "hostname": "your-domain.com",
     "error-codes": ["invalid-input-response"]
   }
   ↓
4. Backend returns 400 error
   But frontend may have already called /recaptcha/api2/reload
   which returns 200 because it's just checking if API is available
```

### The Gap
- **`/recaptcha/api2/reload`** = Google's reload endpoint (always returns 200 if reachable)
- **Actual verification** = Happens at `/api/auth/verify` on your backend
- They are different APIs!

---

## Verification Checklist

### ✅ Client-Side
- [ ] `window.RECAPTCHA_SITE_KEY` is set (check browser console)
- [ ] reCAPTCHA script loads: `https://www.google.com/recaptcha/api.js?render=explicit`
- [ ] Widget renders with: `window.grecaptcha.render()`
- [ ] Token is obtained: `window.grecaptcha.getResponse()`

### ✅ Server-Side
- [ ] `.env` file exists with `RECAPTCHA_SECRET_KEY`
- [ ] Backend receives `g-recaptcha-response` field
- [ ] cURL request to Google's verify endpoint returns success: `true`
- [ ] Response includes: `"success": true`

### ✅ Domain Configuration
- [ ] Your domain is added in: https://www.google.com/recaptcha/admin
- [ ] Both `domain.com` and `www.domain.com` are included
- [ ] For localhost testing: `127.0.0.1` is added

### ✅ Build Process
- [ ] Run `npm run build` to regenerate `/dist` folder
- [ ] `.env` file is loaded (not committed to git)
- [ ] Environment variables are accessible via `env()` function

---

## Debug Steps

### Step 1: Check Client-Side Setup
```javascript
// In browser console
console.log('Site Key:', window.RECAPTCHA_SITE_KEY);
console.log('grecaptcha available:', typeof window.grecaptcha);
console.log('grecaptcha.render available:', typeof window.grecaptcha?.render);
```

### Step 2: Check Backend Configuration
```bash
# Check if .env file exists
ls -la .env

# Verify reCAPTCHA keys are set
grep RECAPTCHA .env
```

### Step 3: Test Backend Verification
```php
// In app/Controllers/Auth.php
// Add temporary logging:
log_message('debug', 'reCAPTCHA Response: ' . $recaptchaResponse);
log_message('debug', 'Verify Result: ' . $verify);
log_message('debug', 'Parsed Response: ' . json_encode($captchaData));
```

### Step 4: Monitor Network Requests
1. Open DevTools → Network tab
2. Filter by `recaptcha` or `verify`
3. Check:
   - Request status codes
   - Request/response payloads
   - Header values (especially `g-recaptcha-response`)

---

## Common Issues & Solutions

### Issue: "Please complete the reCAPTCHA verification"
**Cause:** Token not being sent to backend  
**Fix:** Ensure `g-recaptcha-response` field is in FormData before sending

### Issue: "reCAPTCHA verification failed" with score < 0.5
**Cause:** Using v3 score-based logic with v2 checkbox  
**Fix:** Check backend - v2 doesn't have scores, ignore `score` field

### Issue: Widget shows red error after 2-3 minutes
**Cause:** Token expired (6-minute expiration)  
**Fix:** Auto-reset after 5 minutes or on form error

### Issue: Works on localhost but fails on production
**Cause:** Domain not registered in Google Console  
**Fix:** Add production domain to [reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)

### Issue: Subdomain issues (www.domain.com vs domain.com)
**Cause:** Keys are domain-specific  
**Fix:** Register both variants or use wildcard if supported

---

## Files Modified

| File | Status | Change |
|------|--------|--------|
| [app/Views/app.php](../app/Views/app.php) | ✅ Fixed | Use env() instead of hardcoded test key |
| [resources/components/LoginForm.vue](../resources/components/LoginForm.vue) | ✅ Fixed | Use window.RECAPTCHA_SITE_KEY |
| [.env.example](./.env.example) | ✅ Created | Template with reCAPTCHA configuration |
| [dist/index.html](../dist/index.html) | ⚠️ Needs rebuild | Regenerate with `npm run build` |
| [public/build/assets/](../public/build/assets/) | ⚠️ Needs rebuild | Regenerate with `npm run build` |

---

## Next Steps

1. **Copy environment template:**
   ```bash
   cp .env.example .env
   ```

2. **Add your real reCAPTCHA keys to .env:**
   ```env
   RECAPTCHA_SITE_KEY=YOUR_KEY_HERE
   RECAPTCHA_SECRET_KEY=YOUR_SECRET_HERE
   ```

3. **Rebuild the dist files:**
   ```bash
   npm run build
   ```

4. **Verify configuration:**
   - Check browser console for `window.RECAPTCHA_SITE_KEY`
   - Try a test login
   - Check network tab for reCAPTCHA requests

5. **Monitor logs:**
   ```bash
   tail -f writable/logs/log-*.log
   ```

---

## References

- [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
- [reCAPTCHA v2 Documentation](https://developers.google.com/recaptcha/docs/v2)
- [Server-side Verification Guide](https://developers.google.com/recaptcha/docs/verify)
