<!-- Main App Layout Example -->
<template>
  <div>
    <LoginForm />
  </div>
</template>

<script setup lang="ts">
import LoginForm from '@/components/LoginForm.vue'
</script>

<style>
/* Global responsive scaling for reCAPTCHA and form elements */

/* Base state: screens >= 350px get no special scaling */
@media (max-width: 768px) {
  /* Tablet adjustments */
  .login-form-container {
    padding: 0.5rem;
  }
}

/* Small screens: 350px to 380px */
@media (max-width: 380px) {
  /* Scale form slightly for better mobile fit */
  form {
    transform: scale(0.98);
    transform-origin: top center;
  }
}

/* Ultra-small screens: < 320px */
@media (max-width: 319px) {
  form {
    transform: scale(0.90);
    transform-origin: top center;
  }

  /* Reduce padding on very small screens */
  .rounded-\[2\.5rem\] {
    padding: 1.5rem !important;
  }
}
</style>
