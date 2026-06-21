<template>
  <div
    class="login-page-wrapper"
    :style="{
      backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.45)), url('${windowObj.BASE_URL}images/pic1.jpg')`,
      backgroundColor: '#0f172a'
    }"
  >
    <div class="login-content-container">
      <div class="w-full max-w-[360px] px-4 md:px-0">
        <div class="auth-card">

          <!-- Logo -->
          <div class="mb-4 md:mb-6">
            <img
              :src="windowObj.BASE_URL + 'images/pic3.jpg'"
              alt="TALAbahan Logo"
              class="w-20 md:w-24 h-auto mx-auto rounded-2xl shadow-lg border border-white/[0.08] hover:scale-105 transition-transform duration-300"
            />
          </div>

          <h2 class="auth-heading text-white mb-1 md:mb-2 tracking-tight">Create Account</h2>
          <p class="text-white/50 font-medium mb-6 md:mb-8 text-sm md:text-base">Join us and start managing your seafood today.</p>

          <form @submit.prevent="handleRegister" class="space-y-3 md:space-y-4 text-left">

            <!-- Username Field -->
            <div class="input-group">
              <label for="username" class="block text-[12px] font-semibold text-white/90 mb-1.5 tracking-wide">Username</label>
              <div class="input-wrapper">
                <span
                  class="input-icon"
                  :class="{ 'input-icon--active': usernameFocused }"
                >
                  <User :size="18" :stroke-width="2.5" />
                </span>
                <input
                  v-model="username"
                  type="text"
                  id="username"
                  class="glass-input"
                  placeholder="Choose a username"
                  autocomplete="username"
                  required
                  @focus="usernameFocused = true"
                  @blur="usernameFocused = false"
                />
              </div>
            </div>

            <!-- Email Field -->
            <div class="input-group">
              <label for="email" class="block text-[12px] font-semibold text-white/90 mb-1.5 tracking-wide">Email Address</label>
              <div class="input-wrapper">
                <span
                  class="input-icon"
                  :class="{ 'input-icon--active': emailFocused }"
                >
                  <Mail :size="18" :stroke-width="2.5" />
                </span>
                <input
                  v-model="email"
                  type="email"
                  id="email"
                  class="glass-input"
                  placeholder="you@example.com"
                  autocomplete="email"
                  required
                  @focus="emailFocused = true"
                  @blur="emailFocused = false"
                />
              </div>
            </div>

            <!-- Password Field -->
            <div class="input-group">
              <label for="password" class="block text-[12px] font-semibold text-white/90 mb-1.5 tracking-wide">Password</label>
              <div class="input-wrapper">
                <span
                  class="input-icon"
                  :class="{ 'input-icon--active': passwordFocused }"
                >
                  <Lock :size="18" :stroke-width="2.5" />
                </span>
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  class="glass-input pr-11"
                  placeholder="Create a password"
                  autocomplete="new-password"
                  required
                  @focus="passwordFocused = true"
                  @blur="passwordFocused = false"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70 transition-colors"
                  style="pointer-events: auto;"
                >
                  <EyeOff v-if="showPassword" :size="18" :stroke-width="2" />
                  <Eye v-else :size="18" :stroke-width="2" />
                </button>
              </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="input-group">
              <label for="confirmPassword" class="block text-[12px] font-semibold text-white/90 mb-1.5 tracking-wide">Confirm Password</label>
              <div class="input-wrapper">
                <span
                  class="input-icon"
                  :class="{ 'input-icon--active': confirmPasswordFocused }"
                >
                  <Lock :size="18" :stroke-width="2.5" />
                </span>
                <input
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  id="confirmPassword"
                  class="glass-input pr-11"
                  placeholder="Re-enter your password"
                  autocomplete="new-password"
                  required
                  @focus="confirmPasswordFocused = true"
                  @blur="confirmPasswordFocused = false"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70 transition-colors"
                  style="pointer-events: auto;"
                >
                  <EyeOff v-if="showConfirmPassword" :size="18" :stroke-width="2" />
                  <Eye v-else :size="18" :stroke-width="2" />
                </button>
              </div>
            </div>

            <!-- reCAPTCHA Widget -->
            <div v-if="recaptchaRequired && !recaptchaFailed" class="recaptcha-section my-4 md:my-6">
              <div ref="recaptchaContainerRef" class="recaptcha-widget-host"></div>
              <p v-if="recaptchaError" class="text-amber-300 text-xs text-center mt-2 px-2">
                {{ recaptchaError }}
              </p>
            </div>

            <!-- Error Alert -->
            <div v-if="error" class="bg-rose-500/10 border border-rose-500/20 text-rose-400 py-2 md:py-3 px-3 md:px-4 rounded-xl text-xs font-bold mb-2 md:mb-4 text-center">
              {{ error }}
            </div>

            <!-- Register Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-black py-3 md:py-3.5 rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all active:scale-95 disabled:opacity-50 shadow-[0_4px_20px_rgba(59,130,246,0.45)] hover:shadow-[0_4px_28px_rgba(59,130,246,0.55)] text-sm md:text-base flex items-center justify-center gap-2 min-h-[44px]"
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
              <span>{{ loading ? 'Creating Account...' : 'Register' }}</span>
            </button>
          </form>

          <!-- Login Footer -->
          <div class="mt-6 md:mt-8 text-center">
            <p class="text-white font-bold text-sm">
              Already have an account?
              <Link
                href="/login"
                class="text-blue-400 underline underline-offset-4 decoration-blue-400 hover:text-blue-300 transition-colors ml-1"
              >
                Login
              </Link>
            </p>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── Wrapper ───────────────────────────────────────────────── */
.login-page-wrapper {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: scroll;
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

.login-content-container {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  margin: auto;
  flex-direction: column;
}

/* ── Auth Card (GPU-accelerated glassmorphism) ─────────────── */
.auth-card {
  width: 100%;
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1.5rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
  padding: 1.5rem 1.75rem;
  text-align: center;
}

@media (min-width: 768px) {
  .auth-card {
    padding: 2rem;
  }
}

/* ── Fluid Typography ──────────────────────────────────────── */
.auth-heading {
  font-size: clamp(1.25rem, 5vw, 1.75rem);
  font-weight: 900;
  line-height: 1.2;
}

/* ── Input Group ───────────────────────────────────────────── */
.input-group {
  position: relative;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

/* ── Input Icons (pixel-perfect centering, non-interactive) ── */
.input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.5);
  pointer-events: none;
  z-index: 1;
  transition: color 0.2s ease, filter 0.2s ease, transform 0.2s ease;
  display: flex;
  align-items: center;
}

.input-icon--active {
  color: #60a5fa;
  transform: translateY(-50%) scale(1.15);
  filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.6));
}

/* ── Glass Input (GPU-optimized, no-zoom iOS) ─────────────── */
.glass-input {
  width: 100%;
  min-height: 48px;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  background: rgba(0, 0, 0, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 1rem;
  font-size: 16px;
  font-weight: 600;
  color: #fff;
  outline: none;
  transition: border-color 0.2s ease, background-color 0.2s ease;
  transform: translateZ(0);
  backface-visibility: hidden;
  -webkit-font-smoothing: antialiased;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), inset 0 1px 2px rgba(0, 0, 0, 0.15);
}

.glass-input::placeholder {
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
}

.glass-input:focus {
  border-color: #3b82f6;
  background: rgba(0, 0, 0, 0.25);
}

/* Desktop: keep the glow on focus */
@media (min-width: 769px) {
  .glass-input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.35), 0 0 16px rgba(59, 130, 246, 0.2), inset 0 1px 2px rgba(0, 0, 0, 0.15);
  }
}

/* Mobile: simplified focus (no complex shadow during typing) */
@media (max-width: 768px) {
  .glass-input:focus {
    box-shadow: none;
  }
}

/* Password toggle buttons */
.input-wrapper > button {
  position: absolute;
  right: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  z-index: 1;
  min-width: 44px;
  min-height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── reCAPTCHA Scaling ─────────────────────────────────────── */
.recaptcha-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 50;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  padding: 0.5rem;
  min-height: 78px;
}

.recaptcha-widget-host {
  display: flex;
  justify-content: center;
  align-items: center;
}

:deep(iframe[title*="recaptcha challenge"]) {
  z-index: 9999 !important;
}

/* ── Responsive Logo ───────────────────────────────────────── */
@media (max-width: 480px) {
  img {
    width: 5rem !important;
    height: auto !important;
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
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { useRecaptcha } from '../composables/useRecaptcha';
import { runHeavyTaskWithoutBlockingUI } from '../composables/usePerformance';

const windowObj = window;

const username = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const loading = ref(false);
const error = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const usernameFocused = ref(false);
const emailFocused = ref(false);
const passwordFocused = ref(false);
const confirmPasswordFocused = ref(false);

const recaptchaRequired = window.RECAPTCHA_ENABLED !== false;
const recaptchaContainerRef = ref(null);
const { recaptchaError, recaptchaFailed, getResponse, reset: resetRecaptcha } =
  useRecaptcha(recaptchaContainerRef, { theme: 'dark' });

const handleRegister = () => {
  runHeavyTaskWithoutBlockingUI(async () => {
    if (password.value !== confirmPassword.value) {
      error.value = 'Passwords do not match.';
      return;
    }

    const recaptchaAvailable = recaptchaRequired && !recaptchaFailed.value;
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
      formData.append('username', username.value);
      formData.append('email', email.value);
      formData.append('password', password.value);
      if (recaptchaAvailable) {
        formData.append('g-recaptcha-response', recaptchaResponse);
      }

      const response = await axios.post('/api/auth/register', formData);

      if (response.data.status === 'success') {
        router.visit('/login');
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed. Please try again.';
      if (!recaptchaFailed.value) resetRecaptcha();
      if (err.response?.data?.token) {
        window.CSRF_HASH = err.response.data.token;
      }
    } finally {
      loading.value = false;
    }
  });
};
</script>
