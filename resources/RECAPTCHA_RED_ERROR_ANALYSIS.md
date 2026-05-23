# reCAPTCHA v2 Red Error - Analysis & Solutions

## Summary

Your reCAPTCHA is showing a red error because **the test site key is configured for `localhost` only**, but your application is running on a different domain.

---

## The Issue Explained

### Current Configuration
```env
# In .env file:
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI      # Test key
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe  # Test secret
```

### Why You See the Error

These are **Google's public test keys** that only work on:
- ✅ `localhost`
- ✅ `127.0.0.1`
- ✅ `::1` (IPv6 localhost)

If you access the app from any other domain/IP:
```
1. Browser renders reCAPTCHA widget
2. Widget tries to load from Google → 200 OK
3. Widget renders the checkbox
4. User checks the box → Token generated
5. Token sent to backend → Backend verifies with Google
6. Google API returns: "This token is invalid for your domain"
7. Red error message displayed
```

The **200 status** you see is from Google's `/recaptcha/api2/reload` endpoint, which just checks if the API servers are up. It's NOT the actual verification endpoint.

---

## What You're Actually Accessing From

Based on your error message mentioning `/recaptcha/api2/reload`, you're likely accessing the application from:
- Your actual domain (not localhost)
- OR a different IP address
- OR a development server

**Example scenarios:**
```
✅ Works:   http://127.0.0.1:8080/login
✅ Works:   http://localhost:8080/login
❌ Fails:   http://192.168.1.100:8080/login
❌ Fails:   http://yourdomain.com/login
❌ Fails:   http://10.0.0.5/login
```

---

## Solution: Get Your Real reCAPTCHA Keys

### Step 1: Create a Google reCAPTCHA Site

1. Visit: https://www.google.com/recaptcha/admin
2. Click **"Create"** or **"+"**
3. Enter site details:
   ```
   Label: TALAbahan System (or your app name)
   reCAPTCHA type: v2 "I'm not a robot" Checkbox
   Domains:
   - localhost
   - 127.0.0.1
   - (your actual domain)
   - www.(your actual domain)
   - (your staging/development domain if applicable)
   ```

### Step 2: Copy Your Keys

After creating the site, Google will display:
```
Site Key:     6Lc...xxxxx (copy this)
Secret Key:   6Lc...yyyyy (copy this)
```

### Step 3: Update .env File

Replace the test keys with your real keys:
```env
# Before (Test keys - only work on localhost):
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe

# After (Your real keys):
RECAPTCHA_SITE_KEY=6Lc1234567890abcdefghijklmnopqrst
RECAPTCHA_SECRET_KEY=6Lc1234567890abcdefghijklmnopqrst
```

### Step 4: Rebuild the Application

```bash
npm run build
```

This regenerates the `/dist` folder with your new keys injected from `.env`.

---

## Code Fixes Applied

### 1. ✅ Environment Variable Usage
**File:** [app/Views/app.php](../app/Views/app.php#L19)

```php
<!-- ✅ CORRECT - Now uses environment variable -->
window.RECAPTCHA_SITE_KEY = "<?= env('RECAPTCHA_SITE_KEY') ?>";
```

Previously used hardcoded test key - **now fixed**.

### 2. ✅ Component Configuration
**File:** [resources/components/LoginForm.vue](../resources/components/LoginForm.vue#L195)

```typescript
// ✅ CORRECT - Uses window global from spa.php
const RECAPTCHA_SITE_KEY = window.RECAPTCHA_SITE_KEY || 'YOUR_RECAPTCHA_V2_SITE_KEY'

if (!window.RECAPTCHA_SITE_KEY) {
  console.error('[reCAPTCHA] Site key not found. Ensure RECAPTCHA_SITE_KEY is set in .env file')
}
```

Now includes error checking and fallback.

### 3. ✅ API Endpoint Configuration  
**File:** [app/Config/Routes.php](../app/Config/Routes.php#L30)

```php
$routes->post('auth/verify', 'Auth::verify');
```

Backend correctly configured to receive and verify tokens.

---

## Troubleshooting Checklist

Before you get real keys, verify your current setup:

### Browser Console Check
```javascript
// Open DevTools → Console
console.log('Site Key:', window.RECAPTCHA_SITE_KEY)
console.log('grecaptcha loaded:', typeof window.grecaptcha)
console.log('Can render:', typeof window.grecaptcha?.render)
```

**Expected output:**
```
Site Key: 6LeIxAcTA...
grecaptcha loaded: object
Can render: function
```

### Network Check
```
DevTools → Network → Filter by "recaptcha" or "api2"
Look for requests to:
✅ https://www.google.com/recaptcha/api.js
✅ https://www.google.com/recaptcha/api2/...
✅ https://www.google.com/recaptcha/api/siteverify (backend)
```

### Backend Verification Log
Check your logs:
```bash
tail -f writable/logs/log-*.log
```

Look for:
```
[error] reCAPTCHA verification failed
[error] Invalid reCAPTCHA response
[debug] Token verified successfully
```

---

## When Using Test Keys (Development)

If you MUST use test keys for development on multiple machines:

### What Works:
```
✅ http://127.0.0.1:PORT
✅ http://localhost:PORT
✅ Manual checking: Always passes
✅ No verification needed from Google
```

### What Doesn't Work:
```
❌ http://192.168.x.x:PORT (IP address)
❌ http://machinename.local:PORT (Hostname)
❌ http://yourdomain.com:PORT (Your domain)
❌ http://10.0.0.x:PORT (Different subnet)
```

**Solution for team development:**
- Use `127.0.0.1` and `localhost` via `/etc/hosts` mapping
- OR get your own free reCAPTCHA keys from Google

---

## Next Steps

1. **Immediate (to test):**
   - Access app from `http://127.0.0.1` instead of IP
   - If it works, you've confirmed the test key domain issue

2. **For production/external testing:**
   - Go to https://www.google.com/recaptcha/admin
   - Create new site with your domain
   - Update `.env` with your keys
   - Run `npm run build`
   - Redeploy

3. **For team collaboration:**
   - Use localhost with `/etc/hosts` mapping
   - OR each team member gets their own reCAPTCHA keys
   - OR use test keys on shared development server

---

## Additional Resources

- [Google reCAPTCHA Console](https://www.google.com/recaptcha/admin)
- [reCAPTCHA v2 API Documentation](https://developers.google.com/recaptcha/docs/v2)
- [Server-Side Verification Guide](https://developers.google.com/recaptcha/docs/verify)
- [Common Issues & Solutions](https://support.google.com/recaptcha/?hl=en)

---

## Configuration Files Reference

| File | Purpose | Status |
|------|---------|--------|
| [.env](./.env) | Environment configuration with keys | ✅ Configure here |
| [.env.example](./.env.example) | Template reference | ℹ️ Reference |
| [app/Views/spa.php](../app/Views/spa.php) | Loads reCAPTCHA for SPA | ✅ Correct |
| [app/Views/app.php](../app/Views/app.php) | Loads reCAPTCHA for app | ✅ Fixed |
| [resources/components/LoginForm.vue](../resources/components/LoginForm.vue) | Uses reCAPTCHA widget | ✅ Fixed |
| [app/Controllers/Auth.php](../app/Controllers/Auth.php) | Backend verification | ✅ Correct |
| [dist/index.html](../dist/index.html) | Built output | ⚠️ Rebuild needed |

---

## FAQ

**Q: Why does it work on `127.0.0.1` but not my IP?**  
A: Test keys are domain-restricted to localhost only.

**Q: Will my users see the error?**  
A: Only if they access from unregistered domains. Register your domain in Google Console.

**Q: Do I need different keys for dev/prod?**  
A: No, you can register multiple domains on one set of keys.

**Q: How often should I rebuild?**  
A: Only when you change `.env` values and want them in the dist build.

**Q: Is the test key compromised?**  
A: No, it's Google's public test key meant to be shared. Never share your SECRET key.

**Q: Can I use the same keys on multiple domains?**  
A: Yes, register all domains in Google Console when creating the site.
