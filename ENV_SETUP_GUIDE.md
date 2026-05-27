# Environment Variables Configuration for Premium Login System

## Quick Setup Checklist

Copy this to your `.env` file and fill in your actual keys:

```env
# ============================================================================
# RECAPTCHA v2 CONFIGURATION
# ============================================================================
# Get these from: https://www.google.com/recaptcha/admin

# Frontend: Used by the Vue component to render the reCAPTCHA widget
VITE_RECAPTCHA_SITE_KEY=6Lc[PASTE_YOUR_SITE_KEY_HERE]

# Backend: Used to verify the reCAPTCHA token on the server
RECAPTCHA_SECRET_KEY=6Lc[PASTE_YOUR_SECRET_KEY_HERE]


# ============================================================================
# GOOGLE OAUTH CONFIGURATION (Optional - for Google Sign-In)
# ============================================================================
# Get these from: https://console.cloud.google.com/

VITE_GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_CALLBACK_URL=http://localhost:8080/auth/google/callback


# ============================================================================
# DATABASE CONFIGURATION (Example for user authentication)
# ============================================================================

database.default.hostname=localhost
database.default.database=talabahan_system
database.default.username=root
database.default.password=your_db_password
database.default.DBDriver=MySQLi
database.default.DBPrefix=
database.default.port=3306


# ============================================================================
# EMAIL CONFIGURATION (For password reset emails)
# ============================================================================

email.fromEmail=noreply@talabahan.com
email.fromName=TALAbahan System
email.protocol=smtp
email.SMTPHost=smtp.your-email-provider.com
email.SMTPPort=587
email.SMTPUser=your-email@provider.com
email.SMTPPass=your-email-password
email.SMTPCrypto=tls


# ============================================================================
# SESSION CONFIGURATION
# ============================================================================

session.driver=FileDriver
session.cookieName=talabahan_session
session.expiration=7200
session.savePath=writable/session
session.matchIP=false
session.timeToUpdate=300


# ============================================================================
# APP CONFIGURATION
# ============================================================================

app.name=TALAbahan System
app.environment=development
app.baseURL=http://localhost:8080/
app.forceGlobalSecureRequests=false
app.CSRFProtection=true
```

---

## Step-by-Step Guides

### 1. Get reCAPTCHA v2 Keys

1. Go to: https://www.google.com/recaptcha/admin
2. Click "+ Create" to create a new site
3. Fill in:
   - **Label**: TALAbahan System
   - **reCAPTCHA type**: reCAPTCHA v2 → "I'm not a robot" Checkbox
   - **Domains**: 
     - `localhost` (for development)
     - `yourdomain.com` (for production)
4. Accept terms and submit
5. Copy:
   - **Site Key** → `VITE_RECAPTCHA_SITE_KEY`
   - **Secret Key** → `RECAPTCHA_SECRET_KEY`

### 2. Get Google OAuth Credentials (Optional)

1. Go to: https://console.cloud.google.com/
2. Create a new project or select existing
3. Enable Google+ API:
   - Search "Google+ API"
   - Click "Enable"
4. Create OAuth 2.0 Credentials:
   - Go to "Credentials" → "Create Credentials" → "OAuth client ID"
   - Select "Web application"
   - Add authorized redirect URIs:
     - `http://localhost:8080/auth/google/callback`
     - `https://yourdomain.com/auth/google/callback`
5. Copy the client ID and secret

### 3. Database Setup

Create a `users` table with this schema:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    status VARCHAR(50) DEFAULT 'active',
    reset_token VARCHAR(255) NULL,
    reset_token_expires DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. Email Configuration

Choose your email provider:

#### Using Gmail (with App Password)
```env
email.protocol=smtp
email.SMTPHost=smtp.gmail.com
email.SMTPPort=587
email.SMTPUser=your-email@gmail.com
email.SMTPPass=your-app-specific-password
email.SMTPCrypto=tls
```

#### Using Mailtrap (for testing)
```env
email.protocol=smtp
email.SMTPHost=live.smtp.mailtrap.io
email.SMTPPort=587
email.SMTPUser=api
email.SMTPPass=your-mailtrap-api-token
email.SMTPCrypto=tls
```

#### Using Sendgrid
```env
email.protocol=smtp
email.SMTPHost=smtp.sendgrid.net
email.SMTPPort=587
email.SMTPUser=apikey
email.SMTPPass=SG.your-sendgrid-api-key
email.SMTPCrypto=tls
```

---

## Verification Checklist

After setting up your `.env`, verify everything works:

### ✅ reCAPTCHA
```bash
# Test from backend - should return true
curl -X POST "https://www.google.com/recaptcha/api/siteverify" \
  -d "secret=YOUR_SECRET_KEY&response=YOUR_TEST_TOKEN"
```

### ✅ Database Connection
```php
// In your controller
$db = \Config\Database::connect();
echo $db->connect() ? "Connected!" : "Failed!";
```

### ✅ Email Configuration
```php
// Test email
$email = \Config\Services::email();
$email->setFrom('noreply@talabahan.com');
$email->setTo('test@example.com');
$email->setSubject('Test Email');
$email->setMessage('This is a test email.');
$email->send() ? print("Email sent!") : print("Email failed!");
```

### ✅ Session Configuration
```php
// Check session is working
session()->set('test', 'value');
echo session()->get('test'); // Should output: value
```

---

## Production Deployment Checklist

Before deploying to production, update:

```env
# Change environment
app.environment=production

# Change base URL
app.baseURL=https://yourdomain.com/

# Force HTTPS
app.forceGlobalSecureRequests=true

# Update reCAPTCHA domain
# In Google reCAPTCHA admin, add your production domain

# Use production email service
email.SMTPHost=smtp.your-production-provider.com

# Update Google OAuth redirect URI
# In Google Cloud Console, add production URL
```

---

## Troubleshooting

### "reCAPTCHA failed" - 
- Verify `RECAPTCHA_SECRET_KEY` is correct
- Ensure your domain is added to reCAPTCHA admin
- Check firewall isn't blocking Google API calls

### "Email not sent" -
- Verify SMTP credentials in `.env`
- Check email provider allows third-party apps
- Enable "Less secure apps" (Gmail) if applicable
- Check logs in `writable/logs/`

### "Session not persisting" -
- Verify `writable/session/` directory exists and is writable
- Check `session.cookieName` is set correctly
- Ensure cookies are enabled in browser

### "Database connection failed" -
- Verify database exists and credentials are correct
- Ensure MySQL/MariaDB service is running
- Check port (usually 3306) is correct

---

## File Locations

Create/verify these directories exist:
- `writable/logs/` - Application logs
- `writable/session/` - Session storage
- `writable/cache/` - Cache storage
- `writable/uploads/` - File uploads

Ensure they're writable:
```bash
# Linux/Mac
chmod -R 755 writable/

# Windows (in PowerShell as admin)
icacls writable /grant Everyone:F /t
```

---

## Support Resources

- **reCAPTCHA Docs**: https://developers.google.com/recaptcha/docs/v2
- **Google OAuth Docs**: https://developers.google.com/identity/protocols/oauth2
- **CodeIgniter Config**: https://codeigniter.com/user_guide/general/configuration.html
- **Inertia.js Docs**: https://inertiajs.com/
- **Tailwind CSS**: https://tailwindcss.com/docs

---

## Next Steps

1. ✅ Fill in your `.env` with keys from this guide
2. ✅ Create the users database table
3. ✅ Run migrations/setup
4. ✅ Test the login page at `/login`
5. ✅ Create a test user account
6. ✅ Test login with valid credentials
7. ✅ Test reCAPTCHA verification
8. ✅ Test password reset email flow
9. ✅ (Optional) Set up Google OAuth
10. ✅ Deploy to production

For additional help, refer to `PREMIUM_LOGIN_SETUP.md` for detailed component documentation.
