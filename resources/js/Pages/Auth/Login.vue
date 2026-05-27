<template>
  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative overflow-hidden"
    :style="{ backgroundImage: 'url(https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80)' }">
    <!-- Dark overlay for depth -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Glassmorphism Card Container -->
    <div class="relative z-10 w-full max-w-md px-4">
      <div class="backdrop-blur-md bg-white/10 border border-white/20 rounded-2xl shadow-2xl p-8 space-y-6">

        <!-- Header -->
        <div class="text-center space-y-2">
          <h1 class="text-3xl font-bold text-white">TALAbahan</h1>
          <p class="text-gray-200 text-sm">Premium Dining Experience</p>
          <p class="text-gray-300 text-xs">Sign in to your account</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-5">

          <!-- Email Field -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-100">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="you@example.com"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:bg-white/20 focus:border-white/40 focus:outline-none focus:ring-2 focus:ring-amber-400/50 transition-all duration-200"
            />
            <p v-if="form.errors.email" class="text-red-300 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-100">Password</label>
            <input
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              required
              class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:bg-white/20 focus:border-white/40 focus:outline-none focus:ring-2 focus:ring-amber-400/50 transition-all duration-200"
            />
            <p v-if="form.errors.password" class="text-red-300 text-xs mt-1">{{ form.errors.password }}</p>
          </div>

          <!-- Remember & Forgot Password Row -->
          <div class="flex items-center justify-between text-xs">
            <label class="flex items-center space-x-2 cursor-pointer text-gray-200 hover:text-white transition-colors">
              <input type="checkbox" class="rounded border-white/20 bg-white/10 text-amber-400" />
              <span>Remember me</span>
            </label>
            <a href="#" class="text-amber-300 hover:text-amber-200 transition-colors">Forgot password?</a>
          </div>

          <!-- reCAPTCHA Container -->
          <div class="flex justify-center py-2">
            <div class="h-[78px] overflow-hidden rounded-lg flex items-center justify-center"
              ref="recaptchaContainer">
              <div id="recaptcha-element" class="g-recaptcha" :data-sitekey="siteKey"></div>
            </div>
          </div>
          <p v-if="form.errors.recaptcha_response" class="text-red-300 text-xs text-center">{{ form.errors.recaptcha_response }}</p>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 px-4 rounded-lg bg-white text-gray-900 font-semibold hover:bg-gray-100 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg"
          >
            <span v-if="!form.processing">Sign In</span>
            <span v-else class="flex items-center justify-center space-x-2">
              <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Signing In...</span>
            </span>
          </button>

          <!-- Google Sign-In Button -->
          <button
            type="button"
            class="w-full py-3 px-4 rounded-lg bg-white/20 border border-white/30 text-white font-semibold hover:bg-white/30 transition-all duration-200 flex items-center justify-center space-x-2 group"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path fill="#FFFFFF" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#FFFFFF" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FFFFFF" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#FFFFFF" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            <span>Continue with Google</span>
          </button>

        </form>

        <!-- Registration Link -->
        <div class="text-center pt-2 border-t border-white/10">
          <p class="text-gray-300 text-sm">
            Don't have an account?
            <a href="/register" class="text-amber-300 hover:text-amber-200 font-semibold transition-colors">
              Create one now
            </a>
          </p>
        </div>

      </div>

      <!-- Footer Info -->
      <div class="text-center mt-6 text-gray-300 text-xs">
        <p>© 2026 TALAbahan. All rights reserved.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// Props
const props = defineProps({
  siteKey: {
    type: String,
    default: () => import.meta.env.VITE_RECAPTCHA_SITE_KEY || 'your_recaptcha_site_key'
  }
});

// Local refs
const recaptchaContainer = ref(null);

// Inertia form setup
const form = useForm({
  email: '',
  password: '',
  recaptcha_response: ''
});

// Handle reCAPTCHA callback
const onRecaptchaVerify = (token) => {
  form.recaptcha_response = token;
};

// Mount hook for grecaptcha
onMounted(() => {
  // Safely load reCAPTCHA widget
  if (window.grecaptcha) {
    // reCAPTCHA is already loaded
    renderRecaptcha();
  } else {
    // Listen for reCAPTCHA to load
    const checkRecaptcha = () => {
      if (window.grecaptcha) {
        renderRecaptcha();
      } else {
        setTimeout(checkRecaptcha, 100);
      }
    };
    checkRecaptcha();
  }
});

// Render reCAPTCHA widget
const renderRecaptcha = () => {
  if (!window.grecaptcha || !recaptchaContainer.value) return;

  try {
    window.grecaptcha.render('recaptcha-element', {
      sitekey: props.siteKey,
      callback: onRecaptchaVerify,
      theme: 'dark'
    });
  } catch (error) {
    console.warn('reCAPTCHA render warning:', error);
  }
};

// Form submission
const submitForm = () => {
  // Get reCAPTCHA token
  if (window.grecaptcha) {
    const token = window.grecaptcha.getResponse();
    if (token) {
      form.recaptcha_response = token;
    }
  }

  // Validate reCAPTCHA
  if (!form.recaptcha_response) {
    form.setError('recaptcha_response', 'Please complete the reCAPTCHA challenge');
    return;
  }

  // Submit form to backend
  form.post(route('login'), {
    onSuccess: () => {
      // Handle successful login
      console.log('Login successful');
    },
    onError: (errors) => {
      // Reset reCAPTCHA on error
      if (window.grecaptcha) {
        window.grecaptcha.reset();
      }
      form.recaptcha_response = '';
    }
  });
};
</script>

<style scoped>
/* Smooth transitions for input focus states */
input {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Ensure reCAPTCHA maintains proper aspect ratio */
:deep(.g-recaptcha) {
  display: inline-block;
}

/* Loading spinner animation */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
