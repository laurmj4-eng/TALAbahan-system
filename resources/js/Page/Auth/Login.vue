<template>
  <div class="login-wrapper">
    <!-- Login Card -->
    <div class="login-card">
      <!-- Logo/Header -->
      <div class="mb-8 text-center">
        <div class="mb-4 flex justify-center">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
        <p class="mt-2 text-sm text-gray-600">Sign in to your account</p>
      </div>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-800">{{ errorMessage }}</p>
      </div>

      <!-- Success Alert -->
      <div v-if="successMessage" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-sm text-green-800">{{ successMessage }}</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleSubmit" class="space-y-4" novalidate>
        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Email Address
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            placeholder="you@example.com"
            :disabled="isLoading"
            @blur="validateEmail"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">
            {{ errors.email }}
          </p>
        </div>

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Password
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-10"
              placeholder="••••••••"
              :disabled="isLoading"
              @blur="validatePassword"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
              :disabled="isLoading"
            >
              <svg
                v-if="!showPassword"
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg
                v-else
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
              </svg>
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">
            {{ errors.password }}
          </p>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
          <label class="flex items-center cursor-pointer">
            <input
              v-model="form.rememberMe"
              type="checkbox"
              class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
              :disabled="isLoading"
            />
            <span class="ml-2 text-sm text-gray-700">Remember me</span>
          </label>
          <a href="/forgot-password" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">
            Forgot password?
          </a>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-6"
        >
          <svg
            v-if="isLoading"
            class="w-5 h-5 animate-spin"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>{{ isLoading ? 'Signing in...' : 'Sign In' }}</span>
        </button>
      </form>

      <!-- Sign Up Link -->
      <div class="mt-6 text-center text-sm">
        <p class="text-gray-600">
          Don't have an account?
          <a href="/signup" class="font-medium text-blue-600 hover:text-blue-700 hover:underline">
            Sign up
          </a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  data() {
    return {
      form: {
        email: '',
        password: '',
        rememberMe: false,
      },
      errors: {
        email: '',
        password: '',
      },
      errorMessage: '',
      successMessage: '',
      isLoading: false,
      showPassword: false,
    };
  },
  methods: {
    validateEmail() {
      this.errors.email = '';
      const email = this.form.email.trim();

      if (!email) {
        this.errors.email = 'Email is required';
        return false;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        this.errors.email = 'Please enter a valid email address';
        return false;
      }

      return true;
    },

    validatePassword() {
      this.errors.password = '';
      const password = this.form.password;

      if (!password) {
        this.errors.password = 'Password is required';
        return false;
      }

      if (password.length < 6) {
        this.errors.password = 'Password must be at least 6 characters';
        return false;
      }

      return true;
    },

    validateForm() {
      const emailValid = this.validateEmail();
      const passwordValid = this.validatePassword();

      return emailValid && passwordValid;
    },

    async handleSubmit() {
      this.errorMessage = '';
      this.successMessage = '';

      if (!this.validateForm()) {
        return;
      }

      this.isLoading = true;

      try {
        // Replace with your actual API endpoint
        const response = await fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          },
          body: JSON.stringify({
            email: this.form.email.trim(),
            password: this.form.password,
            rememberMe: this.form.rememberMe,
          }),
        });

        const data = await response.json();

        if (!response.ok) {
          this.errorMessage = data.message || 'Login failed. Please try again.';
          return;
        }

        this.successMessage = 'Login successful! Redirecting...';

        // Redirect after short delay
        setTimeout(() => {
          window.location.href = data.redirect || '/dashboard';
        }, 1000);
      } catch (error) {
        console.error('Login error:', error);
        this.errorMessage = 'An error occurred. Please try again later.';
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  background: linear-gradient(to bottom right, rgb(240, 249, 255), rgb(255, 255, 255), rgb(243, 232, 255));
  padding: 1rem;
}

.login-card {
  width: 100%;
  background-color: white;
  border-radius: 1rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  max-width: 400px;
  margin: auto 0;
}

input:focus-visible {
  outline: none;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
