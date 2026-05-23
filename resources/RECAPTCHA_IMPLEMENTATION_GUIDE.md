# Login Form with reCAPTCHA v2 - Implementation Guide

## Overview

This implementation provides a production-ready, dark glassmorphism login form for Vue 3 with integrated reCAPTCHA v2 verification. The form includes proper spacing, responsive design, and maintains visual consistency across all screen sizes.

## Components Created

### 1. **ReCaptchaWrapper.vue**
A reusable Vue 3 component that wraps Google's reCAPTCHA v2 API.

**Features:**
- Automatic script loading
- TypeScript support with proper type declarations
- Event emissions for verification, expiration, and errors
- Methods to reset and get reCAPTCHA response
- Dark theme by default (can be configured)
- Responsive scaling (scales 90% at 350px, 75% at 320px)

**Props:**
```typescript
- siteKey (string, required): Your reCAPTCHA v2 site key
- theme ('light' | 'dark', default: 'dark'): reCAPTCHA theme
- size ('normal' | 'compact', default: 'normal'): Widget size
- tabindex (number, default: 0): Tab index for accessibility
```

**Events:**
```typescript
- @verify (token: string): Emitted when verification succeeds
- @expire (): Emitted when reCAPTCHA token expires
- @error (): Emitted when verification fails
```

**Methods:**
```typescript
- reset(): Clears the current verification
- getResponse(): Returns the current token or null
```

### 2. **LoginForm.vue**
Complete login form with integrated reCAPTCHA verification.

**Features:**
- Dark glassmorphism design with backdrop-blur-xl
- Form validation (email and password)
- Password visibility toggle
- Remember me checkbox
- Loading state with spinner
- Global error handling
- Responsive design for all screen sizes
- reCAPTCHA centered and balanced
- Disabled submit button until reCAPTCHA is verified

**Styling:**
- Background: `bg-slate-950`
- Card: `backdrop-blur-xl bg-white/10 border border-white/20 rounded-[2.5rem]`
- Inputs: `rounded-2xl bg-white/5 border-white/10`
- Button: `bg-white text-slate-950 font-black`

## Setup Instructions

### Step 1: Get reCAPTCHA Keys

1. Go to [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Create a new site with reCAPTCHA v2 (Checkbox)
3. Copy your **Site Key** and **Secret Key**

### Step 2: Update Component Configuration

In `resources/components/LoginForm.vue`, replace:
```typescript
const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_V2_SITE_KEY'
```

With your actual reCAPTCHA v2 site key.

### Step 3: Create Backend Endpoint

Create an API endpoint at `/api/login` that:

1. Receives the reCAPTCHA token
2. Verifies it with Google's servers:

```php
// Example using PHP/cURL
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => 'YOUR_RECAPTCHA_V2_SECRET_KEY',
    'response' => $_POST['recaptchaToken']
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$result = json_decode($response);

if ($result->success && $result->score > 0.5) {
    // Proceed with login
} else {
    // Reject login
}
```

3. Authenticates the user (email/password verification)
4. Returns authentication token/session

### Step 4: Add to Your Vue App

Import and use the LoginForm component in your main layout:

```vue
<template>
  <LoginForm />
</template>

<script setup>
import LoginForm from '@/components/LoginForm.vue'
</script>
```

## CSS Configuration for Responsive Scaling

### Tailwind Configuration (tailwind.config.js)

Ensure your Tailwind config includes the necessary utility classes:

```javascript
export default {
  content: ['./resources/**/*.{vue,js,ts}'],
  theme: {
    extend: {
      backdropBlur: {
        xl: '24px',
      },
      spacing: {
        'recaptcha-gap': '1.5rem',
      },
    },
  },
  plugins: [],
}
```

### Custom CSS for Ultra-Small Screens

The responsive scaling is handled automatically in both components, but here's the CSS logic:

```css
/* Screens 350px to 380px */
@media (max-width: 380px) {
  form {
    transform: scale(0.98);
    transform-origin: top center;
  }
}

/* Ultra-small screens < 320px */
@media (max-width: 319px) {
  form {
    transform: scale(0.90);
  }
}

/* reCAPTCHA specific scaling in ReCaptchaWrapper.vue */
@media (max-width: 350px) {
  .recaptcha-inner {
    transform: scale(0.9);
    transform-origin: top center;
  }
}

@media (max-width: 320px) {
  .recaptcha-inner {
    transform: scale(0.75);
  }
}
```

## Design Details

### Form Layout Structure

```
┌─────────────────────────────────────┐
│        Welcome Back Heading         │  (text-3xl, font-black)
│     Sign in to your account         │  (text-white/60)
│                                     │
│  Email Address                      │
│  [Email Input (rounded-2xl)]        │  (bg-white/5, border-white/10)
│                                     │  space-y-2
│  Password                           │
│  [Password Input (rounded-2xl)]     │  (bg-white/5, border-white/10)
│                                     │  space-y-2
│  ┌─────────────────────────────┐   │
│  │   [reCAPTCHA Checkbox]      │   │  Centered horizontally
│  │   powered by Google         │   │  Fixed width: 304px
│  └─────────────────────────────┘   │  space-y-6
│                                     │
│  ☐ Remember me  [Forgot password?]  │
│                                     │
│  [Sign In Button - white/black]     │  (py-3, font-black, mt-8)
│                                     │
│  Don't have an account? Sign up     │  (text-white/60, font-sm)
│                                     │
└─────────────────────────────────────┘
```

### Spacing System (Tailwind's space-y utilities)

- Between heading and form: `mb-8`
- Between label and input: `space-y-2`
- Between inputs: `space-y-6`
- Before reCAPTCHA: `py-2` (visual separator)
- After reCAPTCHA: `py-2` (visual separator)
- Before button: `mt-8`

### Color Palette

| Element | Color |
|---------|-------|
| Background | `bg-slate-950` |
| Card Backdrop | `backdrop-blur-xl bg-white/10` |
| Card Border | `border-white/20` |
| Text (Primary) | `text-white` |
| Text (Secondary) | `text-white/60` |
| Text (Tertiary) | `text-white/40` |
| Input BG | `bg-white/5` |
| Input Border | `border-white/10` |
| Focus Ring | `focus:ring-white/30` |
| Button BG | `bg-white` |
| Button Text | `text-slate-950` |

## Responsive Breakpoints

| Screen Size | Behavior |
|-------------|----------|
| > 768px | Full desktop layout, normal sizing |
| 380px - 768px | Tablet/mobile, normal form layout |
| 350px - 380px | Form scales to 98% |
| 320px - 350px | reCAPTCHA scales to 90% |
| < 320px | Form scales to 90%, reCAPTCHA scales to 75% |

## Accessibility Features

- Semantic HTML with proper labels
- Type hints in input fields
- Focus states with visible rings
- Proper color contrast ratios
- Tab index support for reCAPTCHA
- ARIA-compliant error messages
- Screen reader friendly password toggle

## Type Safety (TypeScript)

The components include full TypeScript support:

```typescript
// ReCaptchaWrapper Props
interface Props {
  siteKey: string
  theme?: 'light' | 'dark'
  size?: 'normal' | 'compact'
  tabindex?: number
}

// ReCaptchaWrapper Events
interface Emits {
  (e: 'verify', token: string): void
  (e: 'expire'): void
  (e: 'error'): void
}

// Global Window Declaration
declare global {
  interface Window {
    grecaptcha: {
      render: (container: HTMLElement | string, options: Record<string, any>) => number
      reset: (id?: number) => void
      getResponse: (id?: number) => string | null
    }
  }
}
```

## Common Issues & Solutions

### reCAPTCHA not loading
- Check that your site key is correctly configured
- Verify your domain is whitelisted in reCAPTCHA console
- Check browser console for CORS or script loading errors

### Form overflowing on mobile
- The component automatically scales for screens < 350px
- If still not fitting, reduce card padding in the media query

### reCAPTCHA token expired during form fill
- The component will reset and emit an 'expire' event
- LoginForm catches this and shows an error message
- User needs to re-verify before submitting

### Button not enabling after reCAPTCHA verification
- Ensure `recaptchaVerified` ref updates correctly
- Check that `@verify` event is being emitted
- Verify no JavaScript errors in console

## Security Considerations

1. **Backend Verification**: Always verify reCAPTCHA tokens on your backend
2. **Secret Key**: Keep your reCAPTCHA secret key secure (never expose in frontend)
3. **HTTPS**: Use HTTPS in production (reCAPTCHA requires it)
4. **Rate Limiting**: Implement rate limiting on login attempts
5. **Password Security**: Hash passwords server-side, never in frontend
6. **CSRF Protection**: Implement CSRF tokens if needed

## Integration Examples

### With Vue Router
```typescript
// router.ts
import LoginForm from '@/components/LoginForm.vue'

const routes = [
  {
    path: '/login',
    component: LoginForm,
    meta: { requiresAuth: false }
  }
]
```

### With Pinia Store
```typescript
// stores/auth.ts
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: null,
    user: null,
    isAuthenticated: false
  }),
  
  actions: {
    async login(email: string, password: string, recaptchaToken: string) {
      // Implementation
    }
  }
})
```

## Browser Support

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile browsers: Full support with responsive scaling

## Production Checklist

- [ ] Replace `YOUR_RECAPTCHA_V2_SITE_KEY` with actual key
- [ ] Create `/api/login` backend endpoint
- [ ] Implement reCAPTCHA token verification server-side
- [ ] Add rate limiting to login endpoint
- [ ] Enable HTTPS
- [ ] Test on multiple devices and browsers
- [ ] Set up error monitoring/logging
- [ ] Configure CORS if backend is on different domain
- [ ] Add redirect after successful login
- [ ] Implement password reset flow
- [ ] Add forgot password functionality
- [ ] Set up 2FA if needed

## Performance Notes

- reCAPTCHA script loads asynchronously (non-blocking)
- Form animations use CSS transitions (hardware-accelerated)
- No external dependencies beyond Vue 3 and Tailwind
- Component is lightweight and optimized for mobile

## License

This implementation is provided as-is for use in your project.
