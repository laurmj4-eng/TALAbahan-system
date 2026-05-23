# Quick Action Plan: Fix reCAPTCHA Red Error

## TL;DR - The Problem
You're using **test reCAPTCHA keys** (which only work on `localhost`/`127.0.0.1`) but accessing the app from a **different IP or domain**.

## Immediate Fix (2 minutes)

### Option 1: Use Localhost (Development Only)
```bash
# Instead of accessing from IP, use localhost:
http://127.0.0.1:8080/login    ✅ Works with test keys
http://localhost:8080/login     ✅ Works with test keys
http://192.168.1.100:8080/login ❌ Fails - not localhost
```

### Option 2: Get Real Keys (Recommended for Any External Access)

1. **Go to:** https://www.google.com/recaptcha/admin

2. **Create new site:**
   - Choose: reCAPTCHA v2 (I'm not a robot Checkbox)
   - Add these domains:
     ```
     localhost
     127.0.0.1
     your-actual-domain.com
     www.your-actual-domain.com
     ```

3. **Copy your keys:**
   ```
   Site Key:   [Copy this]
   Secret Key: [Copy this]
   ```

4. **Update .env file:**
   ```bash
   # Find and replace these lines:
   RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
   RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
   
   # With your real keys:
   RECAPTCHA_SITE_KEY=YOUR_SITE_KEY_HERE
   RECAPTCHA_SECRET_KEY=YOUR_SECRET_KEY_HERE
   ```

5. **Rebuild:**
   ```bash
   npm run build
   ```

---

## What Was Fixed in Code

✅ **[app/Views/app.php](../app/Views/app.php#L19)** - Now uses env variable instead of hardcoded test key  
✅ **[resources/components/LoginForm.vue](../resources/components/LoginForm.vue#L195)** - Now uses window.RECAPTCHA_SITE_KEY with error checking  
✅ **[.env.example](./.env.example)** - Template created with documentation  

---

## Files You Need to Review

1. **[RECAPTCHA_RED_ERROR_ANALYSIS.md](./RECAPTCHA_RED_ERROR_ANALYSIS.md)** ← Start here for full explanation
2. **[RECAPTCHA_TROUBLESHOOTING.md](./RECAPTCHA_TROUBLESHOOTING.md)** ← Detailed troubleshooting guide
3. **[.env.example](./.env.example)** ← Configuration template

---

## How to Verify It's Working

### In Browser Console:
```javascript
console.log(window.RECAPTCHA_SITE_KEY)  // Should show your key, not the test key
```

### Try Form:
- Try checking the reCAPTCHA checkbox
- It should verify without errors
- Submit form should work

---

## Current Status

| Component | Issue | Fix Applied |
|-----------|-------|-------------|
| [app/Views/app.php](../app/Views/app.php) | Hardcoded test key | ✅ Uses env() variable |
| [LoginForm.vue](../resources/components/LoginForm.vue) | Placeholder key | ✅ Uses window global |
| [.env configuration](./.env) | Test keys only | ⚠️ Update with real keys |
| [Build output](../dist) | Old hardcoded keys | ⚠️ Rebuild with `npm run build` |

---

## Root Cause

```
Test Key: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
↓
Only works on: localhost, 127.0.0.1, ::1
↓
Your access domain: [IP or domain not in list]
↓
Result: Red error message + 200 status from reload endpoint
```

---

## Still Having Issues?

See comprehensive guides:
- [Full Analysis](./RECAPTCHA_RED_ERROR_ANALYSIS.md)
- [Troubleshooting Steps](./RECAPTCHA_TROUBLESHOOTING.md)
