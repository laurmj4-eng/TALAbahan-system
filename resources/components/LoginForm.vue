<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
    <!-- Background gradient accent (optional, removes if not needed) -->
    <div
      class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-purple-500/10 pointer-events-none"
    ></div>

    <!-- Main Login Card -->
    <div
      class="relative w-full max-w-md backdrop-blur-xl bg-white/10 border border-white/20 rounded-[2.5rem] p-6 sm:p-8 md:p-10 shadow-2xl overflow-hidden"
    >
      <!-- Header -->
      <div class="mb-6 sm:mb-8 text-center">
        <h1 class="text-2xl sm:text-3xl font-black text-white mb-1 sm:mb-2 leading-tight">Welcome Back</h1>
        <p class="text-white/60 text-xs sm:text-sm">Sign in to your account</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-4 sm:space-y-6">
        <!-- Email Input -->
        <div class="space-y-1.5 sm:space-y-2">
          <label for="email" class="block text-xs sm:text-sm font-semibold text-white/90">
            Email Address
          </label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="you@example.com"
            required
            class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl sm:rounded-2xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-white/30 focus:bg-white/10 transition-all duration-200"
          />
          <span v-if="errors.email" class="text-red-400 text-xs mt-1 block">
            {{ errors.email }}
          </span>
        </div>

        <!-- Password Input -->
        <div class="space-y-1.5 sm:space-y-2">
          <label for="password" class="block text-xs sm:text-sm font-semibold text-white/90">
            Password
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="formData.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              class="w-full px-3 sm:px-4 py-2 sm:py-3 rounded-xl sm:rounded-2xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-white/30 focus:bg-white/10 transition-all duration-200"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 sm:right-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-white/90 transition-colors p-1"
            >
              <svg
                v-if="!showPassword"
                class="w-4 h-4 sm:w-5 sm:h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>
              <svg
                v-else
                class="w-4 h-4 sm:w-5 sm:h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                />
              </svg>
            </button>
          </div>
          <span v-if="errors.password" class="text-red-400 text-xs mt-1 block">
            {{ errors.password }}
          </span>
        </div>

        <!-- reCAPTCHA Section with Professional Styling -->
        <div class="pt-2 pb-4 sm:pt-4 sm:pb-6">
          <!-- Visual Separator -->
          <div class="flex items-center gap-4 mb-4">
            <div class="flex-grow h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
            <span class="text-white/40 text-xs font-medium uppercase tracking-wider">Verification</span>
            <div class="flex-grow h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
          </div>

          <!-- reCAPTCHA Wrapper with Enhanced Container -->
          <div class="flex justify-center px-2 sm:px-0">
            <ReCaptchaWrapper
              ref="recaptchaRef"
              :site-key="RECAPTCHA_SITE_KEY"
              theme="dark"
              size="normal"
              @verify="handleRecaptchaVerify"
              @expire="handleRecaptchaExpire"
              @error="handleRecaptchaError"
            />
          </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs sm:text-sm">
          <label class="flex items-center cursor-pointer group">
            <input
              v-model="formData.rememberMe"
              type="checkbox"
              class="w-4 h-4 rounded bg-white/10 border border-white/20 text-white accent-white cursor-pointer hover:bg-white/15 transition-colors"
            />
            <span class="ml-2 text-white/70 group-hover:text-white/90 transition-colors select-none">
              Remember me
            </span>
          </label>
          <a href="#" class="text-white/60 hover:text-white/90 transition-colors whitespace-nowrap">
            Forgot password?
          </a>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading || !recaptchaVerified"
          class="w-full py-2.5 sm:py-3 mt-6 sm:mt-8 px-6 rounded-xl sm:rounded-2xl bg-white text-slate-950 font-black text-sm sm:text-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:disabled:opacity-50 hover:shadow-lg hover:shadow-white/20 active:scale-95"
        >
          <span v-if="!isLoading">Sign In</span>
          <span v-else class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            Signing in...
          </span>
        </button>

        <!-- Sign Up Link -->
        <p class="text-center text-white/60 text-xs sm:text-sm mt-4 sm:mt-6">
          Don't have an account?
          <a href="#" class="text-white font-semibold hover:text-white/80 transition-colors">
            Sign up here
          </a>
        </p>
      </form>

      <!-- Error Message Alert -->
      <transition
        enter-active-class="transition duration-300"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="globalError"
          class="mt-4 sm:mt-6 p-3 sm:p-4 rounded-lg sm:rounded-2xl bg-red-500/20 border border-red-500/30 text-red-300 text-xs sm:text-sm"
        >
          {{ globalError }}
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import ReCaptchaWrapper from './ReCaptchaWrapper.vue'

// reCAPTCHA Site Key - Retrieved from window global set in spa.php
const RECAPTCHA_SITE_KEY = window.RECAPTCHA_SITE_KEY || 'YOUR_RECAPTCHA_V2_SITE_KEY'

if (!window.RECAPTCHA_SITE_KEY) {
  console.error('[reCAPTCHA] Site key not found. Ensure RECAPTCHA_SITE_KEY is set in .env file')
}

// Form state
const formData = reactive({
  email: '',
  password: '',
  rememberMe: false
})

const showPassword = ref(false)
const isLoading = ref(false)
const recaptchaVerified = ref(false)
const recaptchaToken = ref<string | null>(null)
const globalError = ref('')
const recaptchaRef = ref<InstanceType<typeof ReCaptchaWrapper> | null>(null)

const errors = reactive({
  email: '',
  password: ''
})

// Validation
const validateForm = (): boolean => {
  let isValid = true
  errors.email = ''
  errors.password = ''

  if (!formData.email) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!isValidEmail(formData.email)) {
    errors.email = 'Please enter a valid email'
    isValid = false
  }

  if (!formData.password) {
    errors.password = 'Password is required'
    isValid = false
  } else if (formData.password.length < 6) {
    errors.password = 'Password must be at least 6 characters'
    isValid = false
  }

  return isValid
}

const isValidEmail = (email: string): boolean => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

// reCAPTCHA Handlers
const handleRecaptchaVerify = (token: string) => {
  recaptchaToken.value = token
  recaptchaVerified.value = true
  globalError.value = ''
}

const handleRecaptchaExpire = () => {
  recaptchaVerified.value = false
  recaptchaToken.value = null
  globalError.value = 'reCAPTCHA verification expired. Please try again.'
}

const handleRecaptchaError = () => {
  recaptchaVerified.value = false
  recaptchaToken.value = null
  globalError.value = 'reCAPTCHA verification failed. Please try again.'
}

// Form Submit
const handleLogin = async () => {
  globalError.value = ''

  if (!validateForm()) {
    return
  }

  if (!recaptchaVerified.value) {
    globalError.value = 'Please complete the reCAPTCHA verification'
    return
  }

  isLoading.value = true

  try {
    // Call login API endpoint
    const response = await fetch('/api/auth/verify', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: formData.email,
        password: formData.password,
        recaptchaToken: recaptchaToken.value,
        rememberMe: formData.rememberMe
      })
    })

    if (!response.ok) {
      throw new Error('Login failed. Please check your credentials.')
    }

    const data = await response.json()

    // Handle successful login (e.g., redirect, store token, etc.)
    console.log('Login successful:', data)
    // Example: router.push('/dashboard')
  } catch (error) {
    globalError.value = error instanceof Error ? error.message : 'An error occurred'
    // Reset reCAPTCHA on error
    if (recaptchaRef.value) {
      recaptchaRef.value.reset()
    }
    recaptchaVerified.value = false
    recaptchaToken.value = null
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
/* Smooth transitions for form elements */
input:focus {
  transition: all 0.2s ease-in-out;
}

/* Custom accent color for checkboxes */
input[type='checkbox']:checked {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

/* Smooth button animations */
button {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

button:not(:disabled):hover {
  transform: translateY(-2px);
}

/* Loading spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
