# Quick Reference & Examples

## Basic Usage

### Minimal Setup
```vue
<template>
  <LoginForm />
</template>

<script setup>
import LoginForm from '@/components/LoginForm.vue'
</script>
```

## Advanced Usage

### Using reCAPTCHA Wrapper Standalone

If you need reCAPTCHA verification in another form:

```vue
<template>
  <form @submit.prevent="submitForm">
    <!-- Your form fields -->
    
    <ReCaptchaWrapper
      ref="recaptcha"
      :site-key="RECAPTCHA_KEY"
      theme="dark"
      @verify="onRecaptchaVerify"
      @expire="onRecaptchaExpire"
    />
    
    <button type="submit" :disabled="!isVerified">
      Submit
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import ReCaptchaWrapper from '@/components/ReCaptchaWrapper.vue'

const RECAPTCHA_KEY = 'YOUR_SITE_KEY'
const recaptcha = ref(null)
const isVerified = ref(false)
const token = ref(null)

const onRecaptchaVerify = (t) => {
  token.value = t
  isVerified.value = true
}

const onRecaptchaExpire = () => {
  isVerified.value = false
  token.value = null
}

const submitForm = async () => {
  const response = await recaptcha.value.getResponse()
  console.log('Token:', response)
  // Submit to backend
}
</script>
```

## Customization Examples

### Light Theme reCAPTCHA
```vue
<ReCaptchaWrapper
  :site-key="RECAPTCHA_KEY"
  theme="light"
  size="normal"
/>
```

### Compact reCAPTCHA
```vue
<ReCaptchaWrapper
  :site-key="RECAPTCHA_KEY"
  theme="dark"
  size="compact"
/>
```

### Custom Form Styling

Modify card styling in `LoginForm.vue`:

```vue
<!-- More rounded card -->
<div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-[3rem]">
  <!-- content -->
</div>

<!-- Less transparent card -->
<div class="backdrop-blur-xl bg-white/20 border border-white/30 rounded-[2.5rem]">
  <!-- content -->
</div>

<!-- With gradient overlay -->
<div class="backdrop-blur-xl bg-gradient-to-br from-white/10 to-white/5 border border-white/20 rounded-[2.5rem]">
  <!-- content -->
</div>
```

### Custom Button Styling
```vue
<!-- Gradient button -->
<button class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-black rounded-2xl">
  Sign In
</button>

<!-- Outlined button -->
<button class="w-full py-3 bg-transparent border-2 border-white text-white font-black rounded-2xl hover:bg-white/10">
  Sign In
</button>

<!-- Icon button -->
<button class="w-full py-3 px-6 rounded-2xl bg-white text-slate-950 font-black flex items-center justify-center gap-2">
  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
    <!-- icon -->
  </svg>
  Sign In
</button>
```

## API Integration Examples

### Express.js Backend
```javascript
const express = require('express');
const axios = require('axios');

app.post('/api/login', async (req, res) => {
  const { email, password, recaptchaToken } = req.body;
  
  try {
    // Verify reCAPTCHA
    const recaptchaResponse = await axios.post(
      'https://www.google.com/recaptcha/api/siteverify',
      null,
      {
        params: {
          secret: process.env.RECAPTCHA_SECRET,
          response: recaptchaToken
        }
      }
    );
    
    if (!recaptchaResponse.data.success) {
      return res.status(400).json({ error: 'reCAPTCHA verification failed' });
    }
    
    // Verify credentials
    const user = await User.findOne({ email });
    if (!user || !user.comparePassword(password)) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }
    
    // Generate token
    const token = generateJWT(user);
    
    res.json({ token, user: { id: user.id, email: user.email } });
  } catch (error) {
    res.status(500).json({ error: 'Login failed' });
  }
});
```

### Laravel Backend
```php
Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'recaptchaToken' => 'required'
    ]);
    
    // Verify reCAPTCHA
    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret' => config('services.recaptcha.secret'),
        'response' => $validated['recaptchaToken']
    ]);
    
    if (!$response->json('success')) {
        return response()->json(['error' => 'reCAPTCHA verification failed'], 400);
    }
    
    // Attempt login
    if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }
    
    $user = Auth::user();
    $token = $user->createToken('auth')->plainTextToken;
    
    return response()->json(['token' => $token, 'user' => $user]);
});
```

### Python/Flask Backend
```python
from flask import Flask, request, jsonify
import requests
import os

app = Flask(__name__)

@app.route('/api/login', methods=['POST'])
def login():
    data = request.json
    
    # Verify reCAPTCHA
    recaptcha_response = requests.post(
        'https://www.google.com/recaptcha/api/siteverify',
        data={
            'secret': os.getenv('RECAPTCHA_SECRET'),
            'response': data.get('recaptchaToken')
        }
    )
    
    if not recaptcha_response.json().get('success'):
        return jsonify({'error': 'reCAPTCHA verification failed'}), 400
    
    # Verify credentials
    user = User.query.filter_by(email=data.get('email')).first()
    if not user or not user.check_password(data.get('password')):
        return jsonify({'error': 'Invalid credentials'}), 401
    
    # Generate token
    token = generate_jwt_token(user)
    
    return jsonify({
        'token': token,
        'user': {'id': user.id, 'email': user.email}
    })
```

## Form Validation Examples

### Additional Server-Side Validation
```vue
<script setup>
const validatePassword = (password) => {
  const requirements = {
    minLength: password.length >= 8,
    hasUppercase: /[A-Z]/.test(password),
    hasLowercase: /[a-z]/.test(password),
    hasNumbers: /[0-9]/.test(password),
    hasSpecialChar: /[!@#$%^&*]/.test(password)
  }
  return requirements
}

const validateEmail = (email) => {
  const domain = email.split('@')[1]
  const blockedDomains = ['tempmail.com', '10minutemail.com']
  return !blockedDomains.includes(domain)
}
</script>
```

## Error Handling Enhancements

### With Toast Notifications
```vue
<script setup>
import { useToast } from '@/composables/useToast'

const toast = useToast()

const handleRecaptchaError = () => {
  toast.error('reCAPTCHA verification failed. Please try again.')
  recaptchaVerified.value = false
}
</script>
```

### With Sentry Error Tracking
```vue
<script setup>
import * as Sentry from "@sentry/vue"

const handleRecaptchaError = () => {
  Sentry.captureException(new Error('reCAPTCHA verification failed'))
  globalError.value = 'Verification failed. Please try again.'
}
</script>
```

## Testing Examples

### Unit Test (Vitest)
```typescript
import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ReCaptchaWrapper from '@/components/ReCaptchaWrapper.vue'

describe('ReCaptchaWrapper', () => {
  it('emits verify event when reCAPTCHA is completed', async () => {
    const wrapper = mount(ReCaptchaWrapper, {
      props: {
        siteKey: 'test-key'
      }
    })
    
    // Mock grecaptcha
    window.grecaptcha = {
      render: vi.fn(() => 1),
      reset: vi.fn(),
      getResponse: vi.fn(() => 'test-token')
    }
    
    await wrapper.vm.initRecaptcha()
    wrapper.vm.onRecaptchaSuccess('test-token')
    
    expect(wrapper.emitted('verify')).toBeTruthy()
    expect(wrapper.emitted('verify')[0]).toEqual(['test-token'])
  })
})
```

### E2E Test (Playwright)
```typescript
import { test, expect } from '@playwright/test'

test('login flow with reCAPTCHA', async ({ page }) => {
  await page.goto('http://localhost:5173/login')
  
  // Fill email
  await page.fill('input[type="email"]', 'test@example.com')
  
  // Fill password
  await page.fill('input[type="password"]', 'password123')
  
  // Handle reCAPTCHA (mock or bypass in tests)
  await page.evaluate(() => {
    window.grecaptcha.callback('mock-token')
  })
  
  // Submit
  await page.click('button[type="submit"]')
  
  // Verify redirect
  await expect(page).toHaveURL('http://localhost:5173/dashboard')
})
```

## Styling Variations

### Modern Glass Effect (Extra Transparency)
```vue
<div class="backdrop-blur-2xl bg-white/[0.08] border border-white/15 rounded-[2.5rem]">
```

### Frosted Glass Effect
```vue
<div class="backdrop-blur-xl bg-gradient-to-b from-white/15 to-white/5 border border-white/20 rounded-[2.5rem]">
```

### Dark Minimalist
```vue
<div class="backdrop-blur-xl bg-slate-900/40 border border-slate-700/50 rounded-[2.5rem]">
```

## Performance Optimization

### Lazy Load reCAPTCHA Script
```typescript
// Only load script when form is visible
const observer = new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting) {
    loadRecaptchaScript()
  }
})
observer.observe(recaptchaElement.value)
```

### Debounce Email Validation
```typescript
const debouncedEmailValidation = debounce(validateEmail, 500)

watch(() => formData.email, (newEmail) => {
  debouncedEmailValidation(newEmail)
})
```

## Mobile Optimization

### Touch-Friendly Adjustments
```vue
<style>
@media (hover: none) and (pointer: coarse) {
  input, button {
    min-height: 48px; /* iOS 44px minimum + padding */
  }
  
  button {
    font-size: 16px; /* Prevents zoom on iOS */
  }
}
</style>
```

### Safe Area Adjustments (iPhone Notch)
```vue
<div class="min-h-screen flex items-center justify-center p-4 safe-area-inset">
```
