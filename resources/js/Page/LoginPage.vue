<template>
  <div 
    class="login-page-wrapper"
    :style="{ 
      backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('${windowObj.BASE_URL}images/pic1.jpg')`,
      backgroundColor: '#0f172a'
    }"
  >
    <div class="login-content-container">
    <div class="w-full max-w-[400px]">
      <div class="backdrop-blur-xl bg-white/10 border border-white/20 rounded-[2.5rem] shadow-2xl p-6 md:p-8 text-center transition-transform hover:-translate-y-1 duration-500">
        
        <!-- Logo -->
        <div class="mb-4 md:mb-6">
          <img :src="windowObj.BASE_URL + 'images/pic3.jpg'" alt="TALAbahan Logo" class="w-24 md:w-28 h-auto mx-auto rounded-2xl shadow-lg border border-white/10 hover:scale-105 transition-transform duration-300">
        </div>

        <h2 class="text-2xl md:text-3xl font-black text-white mb-1 md:mb-2 tracking-tight">TALAbahan System</h2>
        <p class="text-white/50 font-medium mb-6 md:mb-8 text-xs md:text-sm">Welcome back! Please login to your account.</p>

        <form @submit.prevent="handleLogin" class="space-y-3 md:space-y-4">
          <div class="relative">
            <input
              v-model="email"
              type="email"
              id="email"
              class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-3 md:px-5 md:py-4 text-white placeholder-white/30 focus:outline-none focus:border-white/40 focus:bg-white/10 transition-all text-sm md:text-base"
              placeholder="Email Address"
              required
            />
          </div>

          <div class="relative">
            <input
              v-model="password"
              type="password"
              id="password"
              class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-3 md:px-5 md:py-4 text-white placeholder-white/30 focus:outline-none focus:border-white/40 focus:bg-white/10 transition-all text-sm md:text-base"
              placeholder="Password"
              required
            />
          </div>

          <!-- reCAPTCHA Widget -->
          <div id="recaptcha-container" class="flex justify-center my-4 md:my-6 min-h-[78px] rounded-xl relative z-10"></div>

          <div v-if="error" class="bg-rose-500/10 border border-rose-500/20 text-rose-400 py-2 md:py-3 px-4 rounded-xl text-xs font-bold mb-4">
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-white text-slate-950 font-black py-3 md:py-4 rounded-2xl hover:bg-white/90 transition-all active:scale-95 disabled:opacity-50 shadow-xl shadow-white/5 text-sm md:text-base"
          >
            {{ loading ? 'Authenticating...' : 'Sign In' }}
          </button>

          <!-- Divider -->
          <div class="flex items-center my-4 md:my-6">
            <div class="flex-grow border-t border-white/10"></div>
            <span class="px-4 text-white/30 text-[9px] md:text-[10px] font-black uppercase tracking-[0.2em]">OR</span>
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
        </form>

        <div class="mt-10 text-center">
          <p class="text-white/40 text-sm font-medium">Don't have an account? <Link href="/register" class="text-white font-bold hover:underline decoration-white/30 underline-offset-4">Register here</Link></p>
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
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
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
}
</style>

<script setup>
import { ref, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
import { getAuth, GoogleAuthProvider, signInWithPopup, signInWithRedirect, getRedirectResult } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

const windowObj = window;
const email = ref('');
const password = ref('');
const loading = ref(false);
const googleLoading = ref(false);
const error = ref('');
const siteKey = window.RECAPTCHA_SITE_KEY;
let recaptchaWidget = null;

let auth = null;
let provider = null;

onMounted(() => {
  // Initialize Firebase
  if (window.FIREBASE_CONFIG && window.FIREBASE_CONFIG.apiKey) {
    try {
      const app = initializeApp(window.FIREBASE_CONFIG);
      auth = getAuth(app);
      provider = new GoogleAuthProvider();

      // Handle redirect result for mobile
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

  // Function to render reCAPTCHA
  const renderRecaptcha = () => {
    if (window.grecaptcha && window.grecaptcha.render) {
      const container = document.getElementById('recaptcha-container');
      if (container && container.innerHTML === "") {
        recaptchaWidget = window.grecaptcha.render('recaptcha-container', {
          'sitekey': siteKey
        });
      }
      return true;
    }
    return false;
  };

  // Try to render immediately
  if (!renderRecaptcha()) {
    // If not loaded yet, check periodically
    const interval = setInterval(() => {
      if (renderRecaptcha()) {
        clearInterval(interval);
      }
    }, 500);

    // Stop checking after 10 seconds to avoid infinite loop if something is wrong
    setTimeout(() => clearInterval(interval), 10000);
  }
});

const handleLogin = async () => {
  const recaptchaResponse = window.grecaptcha ? window.grecaptcha.getResponse(recaptchaWidget) : "";
  
  if (!recaptchaResponse) {
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
    formData.append('g-recaptcha-response', recaptchaResponse);

    const response = await axios.post('/api/auth/verify', formData);

    if (response.data.status === 'success') {
      handleSuccessfulLogin(response.data);
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Login failed. Please check your credentials.';
    if (window.grecaptcha) window.grecaptcha.reset(recaptchaWidget);
    
    // Update CSRF hash if backend returned a new one
    if (err.response?.data?.token) {
      window.CSRF_HASH = err.response.data.token;
    }
  } finally {
    loading.value = false;
  }
};

const handleGoogleLogin = async () => {
  if (!auth || !provider) {
    error.value = 'Google Sign-In is not configured correctly.';
    return;
  }

  googleLoading.value = true;
  error.value = '';

  // Use Popup by default as it's more reliable for SPA state
  // Only fallback to redirect if popup is blocked or specifically requested
  try {
    console.log("[Auth] Attempting Google Sign-In with Popup...");
    const result = await signInWithPopup(auth, provider);
    console.log("[Auth] Popup success, user:", result.user.email);
    await verifyWithBackend(result.user.email, result.user.displayName, 'google');
  } catch (err) {
    console.error("[Auth] Google Sign-In Error:", err);
    console.warn("Popup blocked or failed, trying redirect...", err);
    
    // If popup is blocked, we can try redirect for mobile
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
  console.log(`[Auth] Verifying ${providerType} login with backend for:`, userEmail);
  if (providerType === 'google') googleLoading.value = true;
  else loading.value = true;

  try {
    const formData = new FormData();
    formData.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
    formData.append('email', userEmail);
    formData.append('name', name);
    formData.append('provider', providerType);
    formData.append('remember', 'true');

    console.log("[Auth] Sending verification request to /api/auth/verify...");
    const response = await axios.post('/api/auth/verify', formData);
    console.log("[Auth] Backend response:", response.data);

    if (response.data.status === 'success') {
      console.log("[Auth] Verification success, handling redirect...");
      handleSuccessfulLogin(response.data);
    } else {
      console.error("[Auth] Verification failed with message:", response.data.message);
      error.value = response.data.message || 'Verification failed.';
    }
  } catch (err) {
    console.error("[Auth] Axios Error:", err);
    console.error("[Auth] Error details:", err.response?.data);
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
  console.log("[Auth] Storing session data in localStorage...");
  localStorage.setItem('isLoggedIn', 'true');
  localStorage.setItem('userRole', data.role || 'customer');
  localStorage.setItem('username', data.username || '');
  
  const redirectPath = data.redirect || (data.data && data.data.redirect);
  console.log("[Auth] Redirect path from backend:", redirectPath);
  
  if (redirectPath) {
    const finalUrl = (window.BASE_URL || '/') + redirectPath.replace(/^\//, '');
    console.log("[Auth] Navigating to:", finalUrl);
    
    // Use window.location.href for a full page reload to ensure session is picked up
    window.location.href = finalUrl;
  } else {
    const role = data.role || 'customer';
    console.log("[Auth] No redirect path, using default for role:", role);
    let defaultPath = '/customer/dashboard';
    if (role === 'admin') defaultPath = '/admin/dashboard';
    else if (role === 'staff') defaultPath = '/staff/dashboard';
    
    window.location.href = (window.BASE_URL || '/') + defaultPath.replace(/^\//, '');
  }
};
</script>
