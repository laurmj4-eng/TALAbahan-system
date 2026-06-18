<template>
  <div
    class="login-page-wrapper"
    :style="{
      backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('${windowObj.BASE_URL}images/pic1.jpg')`,
      backgroundColor: '#0f172a'
    }"
  >
    <div class="login-content-container">
      <div class="w-full max-w-[400px] px-4 md:px-0">
        <div class=" bg-white/25 border border-white/10 rounded-3xl shadow-2xl p-6 md:p-8 text-center duration-500 overflow-visible">

          <!-- Logo -->
          <div class="mb-4 md:mb-6">
            <img
              :src="windowObj.BASE_URL + 'images/pic3.jpg'"
              alt="TALAbahan Logo"
              width="96"
              height="96"
              style="aspect-ratio: 1 / 1;"
              class="w-20 md:w-24 h-auto mx-auto rounded-2xl shadow-lg border border-white/10 hover:scale-105 transition-transform duration-300"
            />
          </div>

          <h2 class="text-2xl md:text-3xl font-black text-white mb-1 md:mb-2 tracking-tight">TALAbahan System</h2>
          <p class="text-white/50 font-medium mb-6 md:mb-8 text-xs md:text-sm">Welcome back! Please login to your account.</p>

          <form @submit.prevent="handleLogin" @mouseenter="handleInputFocus('form')" @touchstart="handleInputFocus('form')" class="space-y-3 md:space-y-4 text-left">

            <!-- Email Field -->
            <div class="relative">
              <label for="email" class="block text-[12px] font-semibold text-white/90 mb-1.5 tracking-wide">Email Address</label>
              <div class="relative flex items-center">
                <span
                  class="absolute left-3.5 text-white/50 transition-all duration-200"
                  :class="{ 'text-blue-400 scale-[1.15] drop-shadow-[0_0_8px_rgba(59,130,246,0.6)]': emailFocused }"
                >
                  <Mail :size="18" :stroke-width="2.5" />
                </span>
                <input
                  v-model="email"
                  type="email"
                  id="email"
                  :class="loginInputClass"
                  placeholder="you@example.com"
                  autocomplete="email"
                  required
                  @focus="handleInputFocus('email')"
                  @blur="emailFocused = false"
                />
              </div>
            </div>

            <!-- Password Field -->
            <div class="relative">
              <label for="password" class="block text-[12px] font-semibold text-white/90 mb-1.5 tracking-wide">Password</label>
              <div class="relative flex items-center">
                <span
                  class="absolute left-3.5 text-white/50 transition-all duration-200"
                  :class="{ 'text-blue-400 scale-[1.15] drop-shadow-[0_0_8px_rgba(59,130,246,0.6)]': passwordFocused }"
                >
                  <Lock :size="18" :stroke-width="2.5" />
                </span>
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  :class="loginInputClass"
                  placeholder="Enter your password"
                  autocomplete="current-password"
                  required
                  @focus="handleInputFocus('password')"
                  @blur="passwordFocused = false"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3.5 text-white/40 hover:text-white/70 transition-colors"
                >
                  <EyeOff v-if="showPassword" :size="18" :stroke-width="2" />
                  <Eye v-else :size="18" :stroke-width="2" />
                </button>
              </div>
            </div>

            <!-- Forgot Password -->
            <div class="text-center pt-1">
              <a
                href="/forgot-password"
                class="text-white font-bold hover:text-blue-300 transition-colors underline-offset-4 hover:underline"
                style="font-size: 13px;"
              >
                Forgot Password?
              </a>
            </div>

            <!-- reCAPTCHA Widget -->
            <div v-if="showRecaptcha" class="recaptcha-section">
              <div class="recaptcha-inner">
                <div ref="recaptchaContainerRef" class="recaptcha-widget-host"></div>
              </div>
              <p v-if="recaptchaError" class="text-amber-300 text-xs text-center mt-2 px-2">
                {{ recaptchaError }}
              </p>
            </div>

            <!-- Error Alert -->
            <div v-if="error" class="bg-rose-500/10 border border-rose-500/20 text-rose-400 py-2 md:py-3 px-3 md:px-4 rounded-xl text-xs font-bold mb-2 md:mb-4 text-center">
              {{ error }}
            </div>

            <!-- Sign In Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-black py-3.5 md:py-4 rounded-2xl hover:from-blue-600 hover:to-blue-700 transition-all active:scale-95 disabled:opacity-50 shadow-[0_4px_20px_rgba(59,130,246,0.45)] hover:shadow-[0_4px_28px_rgba(59,130,246,0.55)] text-sm md:text-base flex items-center justify-center gap-2"
            >
              <svg
                v-if="loading"
                class="w-5 h-5 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Signing In...' : 'Sign In' }}</span>
            </button>
          </form>

          <!-- OR Separator -->
          <div class="flex items-center my-5 md:my-6">
            <div class="flex-grow border-t border-white/10"></div>
            <span class="px-4 text-white/70 text-[10px] font-black uppercase" style="letter-spacing: 0.25em;">OR</span>
            <div class="flex-grow border-t border-white/10"></div>
          </div>

          <!-- Google Sign-In Button -->
          <button
            @click="handleGoogleLogin"
            type="button"
            :disabled="loading || googleLoading"
            class="w-full flex items-center justify-center gap-2 md:gap-3 bg-white/5 border border-white/10 text-white font-bold py-3 md:py-4 rounded-2xl hover:bg-white/10 transition-all active:scale-95 disabled:opacity-50 text-sm md:text-base"
          >
            <svg v-if="!googleLoading" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
              <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
              <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
              <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
              <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
            </svg>
            <span>{{ googleLoading ? 'Connecting...' : 'Sign in with Google' }}</span>
          </button>

          <!-- Register Footer -->
          <div class="mt-8 text-center">
            <p class="text-white font-bold text-sm">
              Don't have an account?
              <Link
                href="/register"
                class="text-blue-400 underline underline-offset-4 decoration-blue-400 hover:text-blue-300 transition-colors ml-1"
              >
                Register
              </Link>
            </p>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-page-wrapper {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
  min-height: 100dvh;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 2rem 0;
}

@media (max-width: 768px) {
  .login-page-wrapper {
    background-attachment: scroll;
  }
}

.login-content-container {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  margin: auto;
  flex-direction: column;
}

/* ── reCAPTCHA Section ─────────────────────────────────────── */
.recaptcha-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 0.5rem 0;
  margin: 0.5rem 0;
  position: relative;
  z-index: 50;
}

.recaptcha-inner {
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  padding: 0.5rem;
  transition: all 0.2s ease;
  min-height: 78px;
}

.recaptcha-widget-host {
  display: flex;
  justify-content: center;
  align-items: center;
}

:deep(.recaptcha-widget-host iframe) {
  display: block;
  max-width: 100%;
}

/* Ensure the puzzle iframe has room and isn't clipped by stacking contexts */
:deep(iframe[title*="recaptcha challenge"]) {
  z-index: 9999 !important;
}

@media (max-width: 480px) {
  img {
    width: 5rem !important;
    height: auto !important;
  }
}

@media (max-width: 480px) {
  h2 {
    font-size: 1.5rem !important;
  }
}

@media (max-width: 640px) {
  h2 {
    margin-bottom: 0.25rem !important;
  }

  p {
    margin-bottom: 1rem !important;
  }
}
</style>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { useRecaptcha } from '../composables/useRecaptcha';

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getAuth, GoogleAuthProvider, signInWithPopup, signInWithRedirect, getRedirectResult } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

const windowObj = window;
const loginInputClass =
  'w-full rounded-2xl pl-11 pr-4 py-3 md:pl-12 md:pr-5 md:py-3 bg-black/20 border-2 border-white/30 text-[13px] font-bold text-white placeholder-white/40 shadow-[0_4px_16px_rgba(0,0,0,0.25),inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-200 focus:outline-none focus:border-blue-400 focus:bg-black/25 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.45),0_0_20px_rgba(59,130,246,0.25),inset_0_1px_3px_rgba(0,0,0,0.25)]';

const email = ref('');
const password = ref('');
const loading = ref(false);
const googleLoading = ref(false);
const error = ref('');
const showRecaptcha = ref(false);
const hasInteracted = ref(false);
const recaptchaContainerRef = ref(null);
const showPassword = ref(false);
const emailFocused = ref(false);
const passwordFocused = ref(false);

const {
  recaptchaError,
  getResponse,
  reset: resetRecaptcha,
  rerender: rerenderRecaptcha,
} = useRecaptcha(recaptchaContainerRef, {
  theme: 'dark',
});

const handleInputFocus = (field) => {
  if (field === 'email') emailFocused.value = true;
  if (field === 'password') passwordFocused.value = true;
  
  if (!hasInteracted.value) {
    hasInteracted.value = true;
    const trustedAdminEmail = localStorage.getItem('trustedAdminEmail');
    if (!trustedAdminEmail || email.value.toLowerCase() !== trustedAdminEmail.toLowerCase()) {
      showRecaptcha.value = true;
    }
  }
};

let auth = null;
let provider = null;

onMounted(() => {
  const trustedAdminEmail = localStorage.getItem('trustedAdminEmail');
  if (trustedAdminEmail && email.value && email.value.toLowerCase() === trustedAdminEmail.toLowerCase()) {
    showRecaptcha.value = false;
  }

  if (window.FIREBASE_CONFIG && window.FIREBASE_CONFIG.apiKey) {
    try {
      const app = initializeApp(window.FIREBASE_CONFIG);
      auth = getAuth(app);
      provider = new GoogleAuthProvider();

      getRedirectResult(auth).then((result) => {
        if (result) {
          googleLoading.value = true;
          verifyWithBackend(result.user.email, result.user.displayName, 'google');
        }
      }).catch((err) => {
        console.error("Firebase redirect error:", err);
        error.value = "Google redirect failed. Please try again.";
      });
    } catch (err) {
      console.error("Firebase init error:", err);
    }
  }
});

watch(email, (newEmail) => {
  if (!hasInteracted.value) return;
  const trustedAdminEmail = localStorage.getItem('trustedAdminEmail');
  if (trustedAdminEmail && newEmail.toLowerCase() === trustedAdminEmail.toLowerCase()) {
    showRecaptcha.value = false;
  } else {
    showRecaptcha.value = true;
  }
});

watch(showRecaptcha, async (newVal) => {
  if (newVal) {
    await nextTick();
    setTimeout(async () => {
      await rerenderRecaptcha();
    }, 150);
  }
});

const handleLogin = () => {
  // Let the browser paint any active states (button click, ripple)
  requestAnimationFrame(() => {
    setTimeout(async () => {
      const recaptchaResponse = showRecaptcha.value ? getResponse() : '';

      if (showRecaptcha.value && !recaptchaResponse) {
        error.value = 'Please complete the reCAPTCHA verification.';
        return;
      }

      loading.value = true;
      error.value = '';

      // Defer the heavy HTTP prep to the next frame to prevent Vue reactivity from blocking the UI thread
      requestAnimationFrame(async () => {
        try {
          const formData = new FormData();
          formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
          formData.append('email', email.value);
          formData.append('password', password.value);
          formData.append('provider', 'email');
          formData.append('is_trusted_device', !showRecaptcha.value);
          if (showRecaptcha.value) {
            formData.append('g-recaptcha-response', recaptchaResponse);
          }

          const response = await axios.post('/api/auth/verify', formData);

          if (response.data.status === 'success') {
            handleSuccessfulLogin(response.data);
          }
        } catch (err) {
          error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';

          if (err.response?.data?.message?.toLowerCase().includes('recaptcha')) {
            showRecaptcha.value = true;
            localStorage.removeItem('trustedAdminEmail');
          }

          if (showRecaptcha.value) resetRecaptcha();

          if (err.response?.data?.token) {
            window.CSRF_HASH = err.response.data.token;
          }
        } finally {
          loading.value = false;
        }
      });
    }, 0);
  });
};

const handleGoogleLogin = async () => {
  if (!auth || !provider) {
    error.value = 'Google Sign-In is not configured correctly.';
    return;
  }

  googleLoading.value = true;
  error.value = '';

  try {
    const result = await signInWithPopup(auth, provider);
    await verifyWithBackend(result.user.email, result.user.displayName, 'google');
  } catch (err) {
    if (err.code === 'auth/popup-blocked' || err.code === 'auth/popup-closed-by-user') {
      try {
        await signInWithRedirect(auth, provider);
      } catch (redirectErr) {
        error.value = 'Google login failed: ' + redirectErr.message;
        googleLoading.value = false;
      }
    } else {
      error.value = 'Google login failed: ' + err.message;
      googleLoading.value = false;
    }
  }
};

const verifyWithBackend = async (userEmail, name, providerType) => {
  if (providerType === 'google') googleLoading.value = true;
  else loading.value = true;

  try {
    const formData = new FormData();
    formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    formData.append('email', userEmail);
    formData.append('name', name);
    formData.append('provider', providerType);
    formData.append('remember', 'true');

    const response = await axios.post('/api/auth/verify', formData);

    if (response.data.status === 'success') {
      handleSuccessfulLogin(response.data);
    } else {
      error.value = response.data.message || 'Verification failed.';
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Verification failed.';
    if (err.response?.data?.token) {
      window.CSRF_HASH = err.response.data.token;
    }
  } finally {
    loading.value = false;
    googleLoading.value = false;
  }
};

const handleSuccessfulLogin = (data) => {
  localStorage.setItem('isLoggedIn', 'true');
  localStorage.setItem('userRole', data.role || 'customer');
  localStorage.setItem('username', data.username || '');

  if (data.role === 'admin') {
    localStorage.setItem('trustedAdminEmail', email.value.toLowerCase());
  }

  const redirectPath = data.redirect || (data.data && data.data.redirect);

  if (redirectPath) {
    const finalUrl = (window.BASE_URL || '/') + redirectPath.replace(/^\//, '');
    window.location.href = finalUrl;
  } else {
    const role = data.role || 'customer';
    let defaultPath = '/customer/dashboard';
    if (role === 'admin') defaultPath = '/admin/dashboard';
    else if (role === 'staff') defaultPath = '/staff/dashboard';

    window.location.href = (window.BASE_URL || '/') + defaultPath.replace(/^\//, '');
  }
};
</script>
