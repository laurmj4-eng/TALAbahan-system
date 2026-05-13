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

        <h2 class="text-2xl md:text-3xl font-black text-white mb-1 md:mb-2 tracking-tight">Create Account</h2>
        <p class="text-white/50 font-medium mb-6 md:mb-8 text-xs md:text-sm">Join us and start managing your seafood today.</p>

        <form @submit.prevent="handleRegister" class="space-y-3 md:space-y-4">
          <div class="relative">
            <input
              v-model="username"
              type="text"
              id="username"
              class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-3 md:px-5 md:py-4 text-white placeholder-white/30 focus:outline-none focus:border-white/40 focus:bg-white/10 transition-all text-sm md:text-base"
              placeholder="Username"
              required
            />
          </div>

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

          <div class="relative">
            <input
              v-model="confirmPassword"
              type="password"
              id="confirmPassword"
              class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-3 md:px-5 md:py-4 text-white placeholder-white/30 focus:outline-none focus:border-white/40 focus:bg-white/10 transition-all text-sm md:text-base"
              placeholder="Confirm Password"
              required
            />
          </div>

          <!-- reCAPTCHA Widget -->
          <div id="recaptcha-container" class="flex justify-center my-4 md:my-6 min-h-[78px] overflow-hidden rounded-xl scale-90 md:scale-100"></div>

          <div v-if="error" class="bg-rose-500/10 border border-rose-500/20 text-rose-400 py-2 md:py-3 px-4 rounded-xl text-xs font-bold mb-4">
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-white text-slate-950 font-black py-3 md:py-4 rounded-2xl hover:bg-white/90 transition-all active:scale-95 disabled:opacity-50 shadow-xl shadow-white/5 text-sm md:text-base"
          >
            {{ loading ? 'Creating Account...' : 'Register' }}
          </button>
        </form>

        <div class="mt-8 text-center">
          <p class="text-white/40 text-sm font-medium">Already have an account? <router-link to="/login" class="text-white font-bold hover:underline decoration-white/30 underline-offset-4">Login here</router-link></p>
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
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const windowObj = window;
const username = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const loading = ref(false);
const error = ref('');
const siteKey = window.RECAPTCHA_SITE_KEY;
let recaptchaWidget = null;

onMounted(() => {
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
    const interval = setInterval(() => {
      if (renderRecaptcha()) {
        clearInterval(interval);
      }
    }, 500);
    setTimeout(() => clearInterval(interval), 10000);
  }
});

const handleRegister = async () => {
  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match.';
    return;
  }

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
    formData.append('username', username.value);
    formData.append('email', email.value);
    formData.append('password', password.value);
    formData.append('g-recaptcha-response', recaptchaResponse);

    const response = await axios.post('/api/auth/register', formData);

    if (response.data.status === 'success') {
      router.push('/login');
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration failed. Please try again.';
    if (window.grecaptcha) window.grecaptcha.reset(recaptchaWidget);
    if (err.response?.data?.token) {
      window.CSRF_HASH = err.response.data.token;
    }
  } finally {
    loading.value = false;
  }
};
</script>
