# Render Docker Deployment Guide - TALAbahan System

**Last Updated:** May 26, 2026  
**Tech Stack:** CodeIgniter 4 + Vue 3 + Inertia.js + Vite  
**Target Platform:** Render.com

---

## 📋 Pre-Deployment Checklist

- [ ] All files mentioned in this guide have been updated
- [ ] Credentials removed from `.env` (repository contains only `.env.example`)
- [ ] Database is provisioned on a supported provider (MySQL, PostgreSQL)
- [ ] Docker image tested locally with `docker build -f Dockerfile.production .`
- [ ] All environment variables have been saved to Render secrets
- [ ] Git repository is connected to Render

---

## 🔧 Configuration Files Summary

### 1. **Dockerfile.production** (CRITICAL)
**Location:** `/Dockerfile.production`

**Key Changes:**
- ✅ Listens on dynamic `$PORT` environment variable (not fixed port 80)
- ✅ Multi-stage build: Node stage compiles Vite assets, PHP stage serves
- ✅ Apache configuration optimized for Render's proxy setup
- ✅ Health check endpoint configured
- ✅ GZIP compression enabled
- ✅ Cache headers set for static assets (1 year TTL)
- ✅ Proper file permissions for `writable/` directory

**Usage:**
```bash
docker build -f Dockerfile.production -t talabahan:latest .
docker run -e PORT=8080 -p 8080:8080 talabahan:latest
```

---

### 2. **vite.config.production.js** (CRITICAL)
**Location:** `/vite.config.production.js`

**Key Changes:**
- ✅ Asset filenames include content hash: `[name].[hash].js`
- ✅ Generates `manifest.json` for version tracking
- ✅ Server listens on `0.0.0.0` for Docker compatibility
- ✅ Production source maps disabled (use error tracking service)

**Why:** Without hashes, browsers cache stale CSS/JS after deployments, causing broken UI.

**Usage in CI4:**
```php
// app/Views/app.php uses vite_asset() helper to read manifest
<script type="module" src="<?= vite_asset('resources/js/main.js') ?>"></script>
```

---

### 3. **app/Views/app.production.php** (CRITICAL)
**Location:** `/app/Views/app.production.php`

**Key Changes:**
- ✅ `vite_asset()` helper function reads from `manifest.json`
- ✅ Development: serves from Vite dev server (`http://localhost:5173`)
- ✅ Production: reads versioned assets from manifest with proper cache busting
- ✅ Firebase config uses environment variables (not hardcoded)

**Why:** Static asset paths cause cache issues. Manifest ensures proper versioning.

---

### 4. **.env.example** (CRITICAL - SECURITY)
**Location:** `/.env.example`

**Key Changes:**
- ✅ NO credentials in repository (use `${VARIABLE_NAME}` syntax)
- ✅ Proper environment variable structure for Render
- ✅ Clear instructions to set secrets in Render dashboard
- ✅ Database connection uses environment variables
- ✅ All API keys use environment variables

**⚠️ IMPORTANT SECURITY RULES:**
1. **Never commit `.env` file** to Git
2. **Never commit database passwords** in any file
3. **Use Render's Environment Variables section** for all secrets
4. **Rotate credentials** after initial setup

---

### 5. **.dockerignore** (OPTIMIZATION)
**Location:** `/.dockerignore`

**Benefits:**
- Reduces Docker image size
- Excludes unnecessary files (node_modules, .git, .env)
- Faster builds

---

### 6. **render.yaml** (DEPLOYMENT CONFIG)
**Location:** `/render.yaml`

**Configuration:**
- Maps environment variables
- Sets CI_ENVIRONMENT=production
- Configures health check endpoint
- Auto-deploys on git push

**Usage:**
1. Push this file to your Git repo
2. Connect repo to Render
3. Render will auto-detect `render.yaml` and apply config

---

## 🚀 Render Setup (Step-by-Step)

### Step 1: Prepare Your Repository

```bash
# 1. Remove credentials from .env and commit .env.example instead
git rm --cached .env
echo ".env" >> .gitignore
git add .env.example .gitignore
git commit -m "Remove credentials from repository, use environment variables"

# 2. Verify no credentials in history (IMPORTANT!)
git log --all -S="yEEY6EnLGIfdD" # Example password - should return nothing

# 3. Push to main branch
git push origin main
```

### Step 2: Create Render Service

1. **Go to** [https://dashboard.render.com/](https://dashboard.render.com/)
2. **Click** "New +" → "Web Service"
3. **Connect your Git repository**
4. **Configure:**
   - Name: `talabahan-system` (or your service name)
   - Environment: `Docker`
   - Branch: `main`
   - Build Command: (leave empty - uses Dockerfile)
   - Start Command: (leave empty - uses Dockerfile CMD)

### Step 3: Add Environment Variables

In the Render dashboard, go to **Environment** tab and add:

```
CI_ENVIRONMENT=production
PORT=8080
INERTIA_VERSION=1.0.0

# Database (get from your MySQL provider)
DB_HOSTNAME=your-db-host.mysql.db
DB_NAME=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password
DB_PORT=3306

# Security
ENCRYPTION_KEY=generate_a_random_key_here
RECAPTCHA_SITE_KEY=your_recaptcha_public_key
RECAPTCHA_SECRET_KEY=your_recaptcha_private_key

# Firebase
FIREBASE_API_KEY=your_firebase_api_key
FIREBASE_PROJECT_ID=your_firebase_project_id
FIREBASE_AUTH_DOMAIN=your_firebase_auth_domain
FIREBASE_STORAGE_BUCKET=your_storage_bucket
FIREBASE_MESSAGING_SENDER_ID=your_messaging_sender_id
FIREBASE_APP_ID=your_firebase_app_id
FIREBASE_MEASUREMENT_ID=your_measurement_id

# API Keys
OPENROUTER_API_KEY=your_openrouter_key
```

**To Generate ENCRYPTION_KEY:**
```bash
php spark key:generate
# Copy the key and paste into Render dashboard
```

### Step 4: Configure Database

**Option A: MySQL on Render (Recommended)**
- Create MySQL database on Render
- Copy connection details to environment variables

**Option B: External MySQL (AWS RDS, DigitalOcean, etc.)**
- Create database on external provider
- Ensure Render IP whitelist allows access
- Copy connection string to environment variables

**Verify connection:**
```bash
# In your CI4 app
php spark migrate
```

---

## ✅ Deployment Verification

### 1. Check Health Endpoint

After deployment, visit:
```
https://your-service.onrender.com/health
```

Expected response: `200 OK`

### 2. Verify Asset Versioning

Open browser DevTools → Network → find CSS/JS files  
Expected: Files named like `index.[hash].css` (with hash)

### 3. Check Application Logs

In Render dashboard → Logs:
```bash
# Should see
✓ Apache started successfully
✓ Database connected
✓ Inertia component rendering with version X
```

### 4. Test Dynamic Port Binding

The application automatically detects `$PORT` environment variable:
```bash
# In Render logs, you should see Apache listening on the PORT env var
PORT=8080 docker run your-image
```

---

## 🔄 Continuous Deployment

### Auto-Deploy on Push

Render automatically deploys when you push to main branch (if connected):

```bash
git commit -m "Fix asset versioning"
git push origin main
# Render automatically builds and deploys!
```

### Manual Redeploy

1. Go to Render Dashboard → Your Service
2. Click "Manual Deploy" → "Deploy latest commit"

### Increment Asset Version

After each deployment that changes assets:

```bash
# Update in Render environment variables (or .env)
INERTIA_VERSION=1.0.1

# Render will detect and redeploy
```

---

## 🐛 Troubleshooting

### Issue: "Port already in use" or "Connection refused"

**Solution:** Ensure Dockerfile listens on `${PORT}` env variable
```dockerfile
RUN echo "Listen \${PORT}" >> /etc/apache2/ports.conf
```

### Issue: Assets not loading (404 on CSS/JS)

**Solution:** Verify manifest.json exists in build output
```bash
docker run your-image ls -la public/build/manifest.json
# Should list the file
```

### Issue: Database connection timeout

**Solution:** 
1. Verify DB credentials in Render environment
2. Check database is accessible from Render IP range
3. Test connection: `php spark tinker` → `\CodeIgniter\Database\Config\Database::connect()`

### Issue: Vite dev server not working in development

**Solution:** In `.env` for local dev:
```
CI_ENVIRONMENT=development
```

app.php will then load from `http://localhost:5173` (Vite dev server)

### Issue: "X-Inertia header missing" in production

**Solution:** Clear browser cache
```bash
# Or use hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

---

## 📊 Performance Optimization

### 1. Enable Caching

In `app/Config/App.php`:
```php
public $cacheTTL = 3600; // 1 hour
```

### 2. Use Redis (If Available)

In `.env`:
```
cache.default=redis
cache.redis.host=your-redis-host
cache.redis.password=your-redis-password
```

### 3. CDN for Static Assets (Optional)

Configure Cloudflare or similar:
```
*.render.com → https://your-service.onrender.com
public/build/* → CDN with 1-year cache
```

### 4. Database Indexing

Ensure your database has proper indexes:
```sql
CREATE INDEX idx_user_id ON orders(user_id);
CREATE INDEX idx_created_at ON orders(created_at DESC);
```

---

## 🔒 Security Checklist

- [ ] No credentials in Git history
- [ ] All secrets in Render environment variables
- [ ] HTTPS enforced (`$forceGlobalSecureRequests = true` in App.php)
- [ ] CSRF protection enabled in CI4
- [ ] Database user has minimal required permissions
- [ ] Regular database backups configured
- [ ] Rate limiting enabled on API endpoints
- [ ] Content Security Policy headers configured

---

## 📝 Additional Notes

### CodeIgniter Environment File

In production, CI4 still needs a `.env` file to bootstrap. Render automatically creates one with your environment variables.

### Static Asset Caching

Assets are cached for 1 year via HTTP headers:
```apache
ExpiresByType application/javascript "access plus 1 year"
ExpiresByType text/css "access plus 1 year"
```

This requires proper cache busting (which `manifest.json` provides).

### Log Files

- Apache access logs: `/var/log/apache2/access.log`
- CI4 debug logs: `writable/logs/`
- Available in Render dashboard → Logs

---

## 🆘 Need Help?

1. **Render Documentation:** https://render.com/docs
2. **CodeIgniter 4:** https://codeigniter.com/user_guide/
3. **Inertia.js:** https://inertiajs.com/
4. **Vite:** https://vitejs.dev/

---

**Deployment Status:** ✅ Production-Ready
