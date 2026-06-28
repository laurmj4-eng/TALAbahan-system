<template>
  <div
    class="login-page-wrapper"
    :style="{
      backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('${windowObj.BASE_URL}images/pic1.jpg')`,
      backgroundColor: '#0f172a'
    }"
  >
    <div class="login-content-container">
      <div class="w-full max-w-[340px] md:max-w-[440px] px-3 md:px-0">
        <div class="auth-card" ref="cardRef" :style="{ transform: cardScale < 1 ? `scale(${cardScale})` : undefined, transformOrigin: 'top center' }">

          <!-- Logo -->
          <div class="mb-2 md:mb-4">
            <img
              :src="windowObj.BASE_URL + 'images/pic3.jpg'"
              alt="MJ Talabahan Logo"
              width="96"
              height="96"
              style="aspect-ratio: 1 / 1;"
              class="w-14 md:w-20 h-auto mx-auto rounded-xl shadow-lg border border-white/[0.08] hover:scale-105 transition-transform duration-300"
            />
          </div>

          <h2 class="text-2xl md:text-4xl font-black text-white mb-1 md:mb-2 tracking-tight">MJ Talabahan System</h2>
          <p class="text-white/50 font-medium mb-4 md:mb-8 text-[11px] md:text-base">Welcome back! Please login to your account.</p>

          <form @submit.prevent="handleLogin" @mouseenter="handleInputFocus('form')" @touchstart="handleInputFocus('form')" class="space-y-3 md:space-y-6 text-left">

            <!-- Email Field -->
            <div class="relative">
              <label for="email" class="block text-[11px] md:text-[13px] font-semibold text-white/90 mb-1.5 md:mb-2 tracking-wide">Email Address</label>
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
              <label for="password" class="block text-[11px] md:text-[13px] font-semibold text-white/90 mb-1.5 md:mb-2 tracking-wide">Password</label>
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
            <div class="text-center pt-1 md:pt-2">
              <a
                href="/forgot-password"
                class="text-white font-bold hover:text-blue-300 transition-colors underline-offset-4 hover:underline text-[11px] md:text-[13px]"
              >
                Forgot Password?
              </a>
            </div>

            <!-- reCAPTCHA Widget -->
            <div v-if="showRecaptcha && !recaptchaFailed" class="recaptcha-section">
              <div class="recaptcha-inner">
                <div ref="recaptchaContainerRef" class="recaptcha-widget-host"></div>
              </div>
              <p v-if="recaptchaError" class="text-amber-300 text-xs text-center mt-2 px-2">
                {{ recaptchaError }}
              </p>
            </div>

            <!-- Error Alert -->
            <div v-if="error" class="bg-rose-500/10 border border-rose-500/20 text-rose-400 py-1.5 md:py-3 px-3 md:px-4 rounded-xl text-[10px] md:text-xs font-bold mb-1 md:mb-3 text-center">
              {{ error }}
            </div>

            <!-- Sign In Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-black py-2.5 md:py-3 rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all active:scale-95 disabled:opacity-50 shadow-[0_2px_12px_rgba(59,130,246,0.3)] hover:shadow-[0_2px_16px_rgba(59,130,246,0.4)] text-xs md:text-base flex items-center justify-center gap-2"
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
          <div class="flex items-center my-3 md:my-6">
            <div class="flex-grow border-t border-white/[0.08]"></div>
            <span class="px-3 text-white/70 text-[9px] font-black uppercase" style="letter-spacing: 0.25em;">OR</span>
            <div class="flex-grow border-t border-white/[0.08]"></div>
          </div>

          <!-- Google Sign-In Button -->
          <button
            @click="handleGoogleLogin"
            type="button"
            :disabled="loading || googleLoading"
            class="w-full flex items-center justify-center gap-2 md:gap-3 bg-white/5 border border-white/[0.08] text-white font-bold py-2.5 md:py-3 rounded-xl hover:bg-white/10 transition-all active:scale-95 disabled:opacity-50 text-xs md:text-base"
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
          <div class="mt-4 md:mt-8 text-center">
            <p class="text-white font-bold text-sm md:text-base">
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
  background-attachment: scroll;
  min-height: 100vh;
  min-height: 100dvh;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

.login-content-container {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  margin: auto;
  flex-direction: column;
  min-height: 0;
  flex: 1;
}

/* ── Auth Card ─────────────────────────────────────────────── */
.auth-card {
  width: 100%;
  max-width: 100%; /* Let Tailwind width classes control this */
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1.25rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
  padding: 1.25rem 1.25rem;
  text-align: center;
  transition: transform 0.25s ease;
}

@media (min-width: 768px) {
  .auth-card {
    padding: 2rem;
    border-radius: 1.5rem;
  }
}

/* ── reCAPTCHA Section ─────────────────────────────────────── */
.recaptcha-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 0.25rem 0;
  margin: 0.25rem 0;
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
  padding: 0.375rem;
  transition: all 0.2s ease;
  min-height: 64px;
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

:deep(iframe[title*="recaptcha challenge"]) {
  z-index: 9999 !important;
}

@media (max-width: 480px) {
  .login-content-container {
    padding: 0.25rem;
  }

  .auth-card {
    padding: 1rem 1rem;
  }

  img {
    width: 3.5rem !important;
    height: auto !important;
  }

  h2 {
    font-size: 1.25rem !important;
    margin-bottom: 0 !important;
  }

  p {
    margin-bottom: 0.5rem !important;
  }

  input {
    font-size: 16px !important;
  }
}

@media (max-width: 380px) {
  .auth-card {
    padding: 0.875rem 0.875rem;
    border-radius: 1rem;
  }
}
</style>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { useRecaptcha } from '../composables/useRecaptcha';
import { useFcmToken } from '../composables/useFcmToken';

// Firebase SDK — lazy-loaded on demand to avoid render-blocking network fetches
let _firebaseModules = null;
async function getFirebaseModules() {
  if (_firebaseModules) return _firebaseModules;
  const [appMod, authMod] = await Promise.all([
    import(/* webpackIgnore: true */ "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js"),
    import(/* webpackIgnore: true */ "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js"),
  ]);
  _firebaseModules = { ...appMod, ...authMod };
  return _firebaseModules;
}

// Google Identity Services (GIS) — provides in-page sign-in overlay on mobile
let _gisLoaded = false;
let _gisResolve = null;
function loadGIS() {
  return new Promise((resolve) => {
    if (_gisLoaded && window.google?.accounts?.id) {
      resolve(true);
      return;
    }
    // Check if script already exists
    if (document.querySelector('script[src*="accounts.google.com/gsi/client"]')) {
      const check = setInterval(() => {
        if (window.google?.accounts?.id) {
          clearInterval(check);
          _gisLoaded = true;
          resolve(true);
        }
      }, 50);
      setTimeout(() => { clearInterval(check); resolve(false); }, 5000);
      return;
    }
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = () => {
      _gisLoaded = true;
      resolve(true);
    };
    script.onerror = () => resolve(false);
    document.head.appendChild(script);
  });
}

const windowObj = window;
let _googleSignInFocusHandler = null;
const loginInputClass =
  'w-full rounded-xl pl-10 pr-4 py-2.5 md:pl-12 md:pr-5 md:py-4 bg-black/20 border border-white/15 text-[13px] md:text-[15px] font-bold text-white placeholder-white/40 shadow-[0_4px_16px_rgba(0,0,0,0.25),inset_0_1px_3px_rgba(0,0,0,0.2)] transition-all duration-200 focus:outline-none focus:border-blue-400 focus:bg-black/25 focus:shadow-[0_0_0_1px_rgba(59,130,246,0.3),inset_0_1px_3px_rgba(0,0,0,0.25)]';

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
const cardRef = ref(null);
const cardScale = ref(1);

const loginAttempts = ref(0);
const lockoutUntil = ref(0);
const MAX_ATTEMPTS = 5;
const LOCKOUT_BASE_MS = 15000;
const isLockedOut = ref(false);
const lockoutTimer = ref('');

function checkAndUpdateLockout() {
  const now = Date.now();
  if (lockoutUntil.value > now) {
    isLockedOut.value = true;
    updateLockoutDisplay();
    return true;
  }
  isLockedOut.value = false;
  lockoutTimer.value = '';
  return false;
}

function updateLockoutDisplay() {
  const remaining = Math.ceil((lockoutUntil.value - Date.now()) / 1000);
  if (remaining <= 0) {
    isLockedOut.value = false;
    lockoutTimer.value = '';
    return;
  }
  const m = Math.floor(remaining / 60);
  const s = remaining % 60;
  lockoutTimer.value = m > 0 ? `${m}m ${s}s` : `${s}s`;
  setTimeout(updateLockoutDisplay, 1000);
}

function recordFailedAttempt() {
  loginAttempts.value++;
  if (loginAttempts.value >= MAX_ATTEMPTS) {
    const delay = LOCKOUT_BASE_MS * Math.pow(2, loginAttempts.value - MAX_ATTEMPTS);
    lockoutUntil.value = Date.now() + Math.min(delay, 300000);
    checkAndUpdateLockout();
  }
}

function resetAttempts() {
  loginAttempts.value = 0;
  lockoutUntil.value = 0;
  isLockedOut.value = false;
  lockoutTimer.value = '';
}

function recalcCardScale() {
  nextTick(() => {
    const card = cardRef.value;
    if (!card) return;
    const isMobile = window.innerWidth < 768;
    if (isMobile) {
      cardScale.value = 1;
      return;
    }
    const vh = window.visualViewport?.height || window.innerHeight;
    const cardH = card.getBoundingClientRect().height;
    const maxH = vh - 32;
    cardScale.value = cardH > maxH ? Math.max(0.55, maxH / cardH) : 1;
  });
}

const {
  recaptchaError,
  recaptchaFailed,
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

/** Lazily initialize Firebase Auth (only when needed) */
async function ensureFirebaseAuth() {
  if (auth) return true;
  if (!window.FIREBASE_CONFIG || !window.FIREBASE_CONFIG.apiKey) return false;
  try {
    const fb = await getFirebaseModules();
    const app = fb.initializeApp(window.FIREBASE_CONFIG);
    auth = fb.getAuth(app);
    provider = new fb.GoogleAuthProvider();
    return true;
  } catch (err) {
    console.error("Firebase init error:", err);
    return false;
  }
}

let _resizeObserver = null;
let _viewportHandler = null;

onMounted(async () => {
  recalcCardScale();
  const card = cardRef.value;
  if (card && typeof ResizeObserver !== 'undefined') {
    _resizeObserver = new ResizeObserver(() => recalcCardScale());
    _resizeObserver.observe(card);
  }
  if (window.visualViewport) {
    _viewportHandler = () => recalcCardScale();
    window.visualViewport.addEventListener('resize', _viewportHandler);
  }
  window.addEventListener('resize', _viewportHandler);

  const trustedAdminEmail = localStorage.getItem('trustedAdminEmail');
  if (trustedAdminEmail && email.value && email.value.toLowerCase() === trustedAdminEmail.toLowerCase()) {
    showRecaptcha.value = false;
  }

  // Detect interrupted Google sign-in from a previous attempt
  const inProgress = localStorage.getItem('googleSignInInProgress');
  if (inProgress) {
    const elapsed = Date.now() - parseInt(inProgress, 10);
    localStorage.removeItem('googleSignInInProgress');
    if (elapsed < 120000 && !localStorage.getItem('isLoggedIn')) {
      error.value = 'Previous sign-in was cancelled or interrupted. Please try again.';
    }
  }

  // Preload Firebase Auth and Google Identity Services in parallel on page load
  // so they are ready instantly when the user clicks "Sign in with Google"
  if (window.FIREBASE_CONFIG?.apiKey) {
    ensureFirebaseAuth().then(async (ok) => {
      if (!ok) return;
      try {
        const fb = await getFirebaseModules();
        const result = await fb.getRedirectResult(auth);
        if (result && result.user) {
          googleLoading.value = true;
          verifyWithBackend(result.user.email, result.user.displayName, 'google');
        }
      } catch (err) {
        console.error("Firebase redirect result error:", err);
      }
    });
  }
  if (window.GOOGLE_CLIENT_ID) {
    loadGIS();
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
  if (newVal && !recaptchaFailed.value) {
    await nextTick();
    setTimeout(async () => {
      await rerenderRecaptcha();
      recalcCardScale();
    }, 150);
  }
});

onBeforeUnmount(() => {
  if (_googleSignInFocusHandler) {
    window.removeEventListener('focus', _googleSignInFocusHandler);
    _googleSignInFocusHandler = null;
  }
  if (_resizeObserver) { _resizeObserver.disconnect(); _resizeObserver = null; }
  if (_viewportHandler) {
    window.visualViewport?.removeEventListener('resize', _viewportHandler);
    window.removeEventListener('resize', _viewportHandler);
  }
});

const handleLogin = async () => {
  if (checkAndUpdateLockout()) {
    error.value = `Too many failed attempts. Please wait ${lockoutTimer.value}.`;
    return;
  }

  const recaptchaAvailable = showRecaptcha.value && !recaptchaFailed.value;
  const recaptchaResponse = recaptchaAvailable ? getResponse() : '';

  if (recaptchaAvailable && !recaptchaResponse) {
    error.value = 'Please complete the reCAPTCHA verification.';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const formData = new FormData();
    formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    formData.append('email', email.value);
    formData.append('password', password.value);
    formData.append('provider', 'email');
    formData.append('is_trusted_device', !recaptchaAvailable);
    if (recaptchaAvailable) {
      formData.append('g-recaptcha-response', recaptchaResponse);
    }

    const response = await axios.post('/api/auth/verify', formData);

    if (response.data.status === 'success') {
      resetAttempts();
      handleSuccessfulLogin(response.data);
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';

    recordFailedAttempt();

    if (isLockedOut.value) {
      error.value = `Too many failed attempts. Please wait ${lockoutTimer.value}.`;
    }

    if (err.response?.data?.message?.toLowerCase().includes('recaptcha')) {
      showRecaptcha.value = true;
      localStorage.removeItem('trustedAdminEmail');
    }

    if (showRecaptcha.value && !recaptchaFailed.value) resetRecaptcha();

    if (err.response?.data?.token) {
      window.CSRF_HASH = err.response.data.token;
    }
  } finally {
    loading.value = false;
  }
};

const isMobile = () => /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 768;

const showGoogleOverlay = ref(false);

const handleGoogleLogin = async () => {
  googleLoading.value = true;
  error.value = '';

  // In Android WebView, skip Firebase popup and open external browser via native bridge
  if (navigator.userAgent.includes('TALAbahanAndroidApp')) {
    localStorage.setItem('googleSignInInProgress', Date.now().toString());

    const url = window.BASE_URL + 'auth/mobile-login?auth_mode=mobile';
    let opened = false;
    if (window.AndroidBridge && window.AndroidBridge.openInBrowser) {
      try {
        window.AndroidBridge.openInBrowser(url);
        opened = true;
      } catch (e) {
        console.error('AndroidBridge.openInBrowser failed:', e);
      }
    }
    if (!opened) {
      const a = document.createElement('a');
      a.href = url;
      a.style.display = 'none';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      opened = true;
    }
    googleLoading.value = false;

    setTimeout(() => {
      const handler = () => {
        setTimeout(() => {
          const val = localStorage.getItem('googleSignInInProgress');
          if (val) {
            const elapsed = Date.now() - parseInt(val, 10);
            localStorage.removeItem('googleSignInInProgress');
            if (elapsed < 120000 && !localStorage.getItem('isLoggedIn')) {
              error.value = 'Sign-in was cancelled or interrupted. Please try again.';
            }
          }
          window.removeEventListener('focus', handler);
          _googleSignInFocusHandler = null;
        }, 1500);
      };
      _googleSignInFocusHandler = handler;
      window.addEventListener('focus', handler);
      setTimeout(() => {
        window.removeEventListener('focus', handler);
        _googleSignInFocusHandler = null;
      }, 120000);
    }, 100);
    return;
  }

  if (!auth) {
    const ok = await ensureFirebaseAuth();
    if (!ok) {
      error.value = 'Google Sign-In is not configured correctly.';
      googleLoading.value = false;
      return;
    }
  }

  const fb = _firebaseModules || await getFirebaseModules();

  if (auth.currentUser) {
    await fb.signOut(auth);
  }

  try {
    const result = await fb.signInWithPopup(auth, provider);
    await verifyWithBackend(result.user.email, result.user.displayName, 'google');
  } catch (err) {
    if (err.code === 'auth/popup-closed-by-user') {
      googleLoading.value = false;
    } else if (err.code === 'auth/popup-blocked') {
      try {
        await fb.signInWithRedirect(auth, provider);
      } catch (redirectErr) {
        error.value = 'Google login failed. Please try again.';
        googleLoading.value = false;
      }
    } else {
      console.error('Google sign-in error:', err);
      error.value = 'Google login failed. Please try again.';
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
  localStorage.removeItem('googleSignInInProgress');
  localStorage.setItem('isLoggedIn', 'true');
  localStorage.setItem('userRole', data.role || 'customer');
  localStorage.setItem('username', data.username || '');

  const { registerFcmTokenWithRetry } = useFcmToken();
  registerFcmTokenWithRetry();

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
