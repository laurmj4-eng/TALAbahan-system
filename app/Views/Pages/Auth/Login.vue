<template>
  <div class="relative min-h-screen w-full overflow-hidden bg-gray-950">
    <!-- Seafood-themed Background Image with Overlay -->
    <div class="absolute inset-0">
      <img
        src="https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=1920&q=80"
        alt="Premium Seafood Background"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-black/50"></div>
    </div>

    <!-- Glassmorphism Card Container -->
    <div class="relative flex items-center justify-center min-h-screen px-4 py-8">
      <div
        class="w-full max-w-md rounded-2xl bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl p-8 md:p-10"
      >
        <!-- Logo Section -->
        <div class="flex flex-col items-center mb-8">
          <div class="w-20 h-20 rounded-lg bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center mb-4">
            <span class="text-3xl font-bold text-white">ST</span>
          </div>
          <h1 class="text-3xl font-bold text-white text-center">TALAbahan System</h1>
          <p class="text-white/70 text-center mt-2">Welcome back! Please login to your account.</p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-sm font-medium text-white/90 mb-2">
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              name="email"
              required
              placeholder="you@example.com"
              class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-transparent transition-all duration-200 backdrop-blur-sm"
            />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password Input -->
          <div>
            <label for="password" class="block text-sm font-medium text-white/90 mb-2">
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              name="password"
              required
              placeholder="••••••••"
              class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-400/50 focus:border-transparent transition-all duration-200 backdrop-blur-sm"
            />
            <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- reCAPTCHA Container - Fixed Vertical Stretch Bug -->
          <div class="flex justify-center py-2">
            <div class="h-auto overflow-hidden" style="display: flex; justify-content: center; align-items: center;">
              <div
                ref="recaptchaContainer"
                class="g_recaptcha"
                data-sitekey="YOUR_RECAPTCHA_SITE_KEY"
                data-callback="onRecaptchaSuccess"
              ></div>
            </div>
          </div>
          <p v-if="form.errors.recaptcha_response" class="text-xs text-red-400 text-center">
            {{ form.errors.recaptcha_response }}
          </p>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 px-4 rounded-lg bg-white text-gray-900 font-semibold hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg"
          >
            <span v-if="!form.processing">Sign In</span>
            <span v-else class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </span>
          </button>

          <!-- Divider -->
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-white/20"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-gradient-to-b from-white/10 to-transparent text-white/60">or</span>
            </div>
          </div>

          <!-- Google Sign-In Button -->
          <button
            type="button"
            @click="handleGoogleSignIn"
            class="w-full py-3 px-4 rounded-lg bg-white/15 border border-white/30 text-white font-medium hover:bg-white/20 transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Sign in with Google
          </button>
        </form>

        <!-- Registration Link -->
        <div class="mt-8 text-center">
          <p class="text-white/70 text-sm">
            Don't have an account?
            <Link
              :href="route('register')"
              class="text-blue-400 hover:text-blue-300 font-semibold transition-colors"
            >
              Register here
            </Link>
          </p>
        </div>

        <!-- Forgot Password Link -->
        <div class="mt-4 text-center">
          <Link
            :href="route('password.request')"
            class="text-white/60 hover:text-white/80 text-xs transition-colors"
          >
            Forgot your password?
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

// Form state using Inertia's useForm hook
const form = useForm({
  email: '',
  password: '',
  recaptcha_response: '',
})

const recaptchaContainer = ref(null)

/**
 * Initialize reCAPTCHA on component mount
 * Safely checks for grecaptcha availability during Inertia routing
 */
onMounted(() => {
  // Check if grecaptcha is available globally
  if (window.grecaptcha && recaptchaContainer.value) {
    window.grecaptcha.render(recaptchaContainer.value, {
      sitekey: import.meta.env.VITE_RECAPTCHA_SITE_KEY || 'YOUR_RECAPTCHA_SITE_KEY',
      callback: onRecaptchaSuccess,
      'error-callback': onRecaptchaError,
    })
  } else if (!window.grecaptcha) {
    console.warn('reCAPTCHA script not loaded. Ensure the Google reCAPTCHA script tag is in your HTML head.')
  }
})

/**
 * Callback when reCAPTCHA verification succeeds
 */
window.onRecaptchaSuccess = (token) => {
  form.recaptcha_response = token
}

/**
 * Callback when reCAPTCHA verification fails
 */
window.onRecaptchaError = () => {
  form.recaptcha_response = ''
  form.clearErrors('recaptcha_response')
}

/**
 * Handle form submission
 * Sends email, password, and reCAPTCHA token to backend
 */
const handleSubmit = () => {
  // Validate reCAPTCHA was completed
  if (!form.recaptcha_response) {
    form.setError('recaptcha_response', 'Please complete the reCAPTCHA verification')
    return
  }

  // Submit form to backend login route
  form.post(route('login'), {
    onFinish: () => {
      // Reset reCAPTCHA on error for retry
      if (form.hasErrors) {
        if (window.grecaptcha) {
          window.grecaptcha.reset()
        }
        form.recaptcha_response = ''
      }
    },
  })
}

/**
 * Handle Google Sign-In button click
 * This is a placeholder - integrate with your OAuth provider
 */
const handleGoogleSignIn = () => {
  // Example: Redirect to your Google OAuth endpoint
  // window.location.href = route('auth.google')
  
  // Or use a proper OAuth library like:
  // initializeGoogleSignIn()
  
  console.log('Google Sign-In clicked - integrate with your OAuth provider')
}
</script>

<style scoped>
/* Ensure reCAPTCHA container maintains proper dimensions */
:deep(.g_recaptcha) {
  display: flex;
  justify-content: center;
}

/* Smooth transitions for input focus states */
input {
  transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
}

input:focus {
  transform: translateY(-1px);
}
</style>
