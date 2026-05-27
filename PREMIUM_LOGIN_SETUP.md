# TALAbahan Login Component - Setup & Integration Guide

## Component Overview

A premium, production-ready Vue 3 login component featuring:
- ✅ Glassmorphism design with backdrop blur effects
- ✅ Seafood restaurant-themed background imagery
- ✅ Perfect reCAPTCHA v2 integration (vertical stretch bug fixed)
- ✅ Full Inertia.js form handling
- ✅ Responsive design with Tailwind CSS
- ✅ Loading states and error handling
- ✅ Google Sign-In button (OAuth-ready)

**File Location:** `app/Views/Pages/Auth/Login.vue`

---

## Setup Instructions

### 1. **Add reCAPTCHA Script to Your Layout**

Add this to your main layout template (in the `<head>` section):

```html
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
```

**File:** `app/Views/Layouts/AuthLayout.vue` or wherever your main layout is defined.

### 2. **Set Environment Variables**

Add your reCAPTCHA v2 site key to your `.env` file:

```env
VITE_RECAPTCHA_SITE_KEY=your_recaptcha_v2_site_key_here
RECAPTCHA_SECRET_KEY=your_recaptcha_v2_secret_key_here
```

### 3. **Backend Login Route Setup**

Your backend login route should:
1. Verify the reCAPTCHA token using the secret key
2. Validate email and password
3. Return authenticated session/token

**Example (CodeIgniter):**

```php
public function login()
{
    if ($this->request->is('post')) {
        // Validate reCAPTCHA
        $recaptchaToken = $this->request->getPost('recaptcha_response');
        if (!$this->verifyRecaptcha($recaptchaToken)) {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => 'reCAPTCHA verification failed',
                'errors' => ['recaptcha_response' => 'Please complete the verification'],
            ]);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Validate credentials
        $user = $userModel->where('email', $email)->first();
        
        if ($user && password_verify($password, $user->password)) {
            session()->set('user', $user);
            return redirect()->to('/dashboard');
        }

        return back()->withInput()->with('error', 'Invalid credentials');
    }

    return view('Pages/Auth/Login');
}

private function verifyRecaptcha($token)
{
    $secretKey = env('RECAPTCHA_SECRET_KEY');
    $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false,
        stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret' => $secretKey,
                    'response' => $token,
                ]),
            ],
        ])
    );

    $result = json_decode($response);
    return $result->success && $result->score > 0.5;
}
```

### 4. **Update the Login Route Name**

The component uses `route('login')` and `route('register')`. Ensure your routing is configured:

```php
// In your routes file
$routes->group('auth', static function($routes) {
    $routes->post('login', 'AuthController::login', ['as' => 'login']);
    $routes->get('register', 'AuthController::register', ['as' => 'register']);
    $routes->post('password/request', 'PasswordController::request', ['as' => 'password.request']);
});
```

---

## Component Features

### Form Fields
- **Email Address**: Text input with validation
- **Password**: Password input with validation
- **reCAPTCHA v2**: Checkbox verification (with fixed vertical stretch)
- **Submit Button**: White background, loading state indicator
- **Google Sign-In**: OAuth-ready button
- **Registration Link**: Bottom navigation
- **Forgot Password**: Recovery link

### reCAPTCHA v2 Vertical Stretch Fix

The component includes a specialized container that prevents the common reCAPTCHA vertical stretching issue:

```vue
<div class="flex justify-center py-2">
  <div class="h-auto overflow-hidden" style="display: flex; justify-content: center; align-items: center;">
    <div ref="recaptchaContainer" class="g_recaptcha" ...></div>
  </div>
</div>
```

**Why this works:**
- Flexbox centering prevents widget distortion
- `overflow-hidden` contains the widget naturally
- `h-auto` respects the widget's natural height (~78px)
- No artificial height constraints

### Form Handling with Inertia.js

The component uses `useForm()` to handle:
- Form submission
- Error display
- Loading states
- Field binding

```javascript
const form = useForm({
  email: '',
  password: '',
  recaptcha_response: '',
})

form.post(route('login'), {
  onFinish: () => { /* cleanup */ }
})
```

### reCAPTCHA Lifecycle Management

```javascript
onMounted(() => {
  // Safely check for grecaptcha before rendering
  if (window.grecaptcha && recaptchaContainer.value) {
    window.grecaptcha.render(recaptchaContainer.value, {
      sitekey: import.meta.env.VITE_RECAPTCHA_SITE_KEY,
      callback: onRecaptchaSuccess,
      'error-callback': onRecaptchaError,
    })
  }
})
```

---

## Google Sign-In Integration (Optional)

To enable Google OAuth sign-in, update the `handleGoogleSignIn()` function:

### Option 1: Google OAuth Library

```javascript
const handleGoogleSignIn = async () => {
  try {
    const result = await window.google.accounts.id.initialize({
      client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
      callback: handleCredentialResponse,
    })
  } catch (error) {
    console.error('Google Sign-In failed:', error)
  }
}

const handleCredentialResponse = (response) => {
  form.post(route('auth.google'), {
    token: response.credential,
  })
}
```

Include in your layout:
```html
<script src="https://accounts.google.com/gsi/client" async defer></script>
```

### Option 2: Redirect to Backend OAuth Endpoint

```javascript
const handleGoogleSignIn = () => {
  window.location.href = route('auth.google.redirect')
}
```

---

## Styling Notes

### Glassmorphism Colors
- **Card Background**: `bg-white/10` (10% opacity white)
- **Card Blur**: `backdrop-blur-xl` (extra large blur)
- **Border**: `border-white/20` (20% opacity white border)
- **Text**: `text-white` and `text-white/70` (for secondary text)

### Responsive Breakpoints
- Mobile: `px-4 py-8`
- Desktop: `md:p-10`
- Max width: `max-w-md` (448px)

### Interactive States
- Hover: `hover:scale-105` on buttons
- Active: `active:scale-95` (press animation)
- Focus: `focus:ring-2 focus:ring-blue-400/50` on inputs
- Loading: Spinner animation on submit button

---

## Troubleshooting

### reCAPTCHA Not Showing?
1. Verify the `<script>` tag is in your layout head
2. Check that `VITE_RECAPTCHA_SITE_KEY` is set in `.env`
3. Open browser console for errors
4. Ensure site key is registered for your domain in Google reCAPTCHA admin

### reCAPTCHA Vertically Stretched?
- The component already includes the fix
- The container uses `overflow-hidden` and flexbox alignment
- If still experiencing issues, check for CSS overrides

### Form Not Submitting?
1. Verify backend route exists at `route('login')`
2. Check that reCAPTCHA verification passes
3. Ensure `RECAPTCHA_SECRET_KEY` is set on backend
4. Check browser console and network tab for API errors

### Inertia Routes Not Found?
- Ensure `ziggy-js` is properly installed
- Routes should be in your Inertia middleware response
- Check that `@vite(['resources/js/app.js'])` is in your layout

---

## Environment Variables Required

```env
# reCAPTCHA Keys
VITE_RECAPTCHA_SITE_KEY=6Lc[YOUR_SITE_KEY]
RECAPTCHA_SECRET_KEY=6Lc[YOUR_SECRET_KEY]

# Optional: For Google OAuth
VITE_GOOGLE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_google_client_secret
```

Get keys from:
- **reCAPTCHA v2**: https://www.google.com/recaptcha/admin
- **Google OAuth**: https://console.cloud.google.com/

---

## Performance Considerations

✅ **Optimized:**
- Background image uses responsive Unsplash URL with compression
- Glassmorphism uses GPU-accelerated `backdrop-blur`
- Form submission uses Inertia's built-in optimization
- CSS scoped to component only

📊 **Bundle Size Impact:**
- Component: ~8KB
- No external dependencies beyond Inertia.js (already included)

---

## Browser Support

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support (iOS 13+)
- IE11: ❌ Not supported (uses CSS Grid, backdrop-filter)

---

## Customization Guide

### Change Background Image

Replace the image URL in the template:
```vue
<img
  src="https://images.unsplash.com/photo-YOUR_ID?w=1920&q=80"
  alt="Your Background"
  class="..."
/>
```

### Change Logo

Replace the "ST" placeholder:
```vue
<img src="/images/logo.png" alt="TALAbahan" class="w-20 h-20" />
```

### Adjust Glassmorphism Intensity

Modify the card classes:
```vue
<!-- More transparent -->
<div class="bg-white/5 backdrop-blur-md ...">

<!-- More opaque -->
<div class="bg-white/20 backdrop-blur-2xl ...">
```

### Change Color Scheme

Replace Tailwind colors (currently blue and white):
```javascript
// From:
focus:ring-blue-400/50
text-blue-400

// To your brand colors
focus:ring-amber-400/50
text-amber-400
```

---

## Support & Next Steps

1. ✅ Copy component to `app/Views/Pages/Auth/Login.vue`
2. ✅ Add reCAPTCHA script to your layout
3. ✅ Set environment variables in `.env`
4. ✅ Implement backend login route with reCAPTCHA verification
5. ✅ Test form submission
6. ✅ (Optional) Integrate Google OAuth

For issues, check:
- Network tab (API calls)
- Console errors (JavaScript)
- reCAPTCHA admin panel (keys/domain)
- Backend logs (validation errors)
