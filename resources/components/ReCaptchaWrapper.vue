<template>
  <div class="recaptcha-container">
    <div ref="recaptchaElement" class="recaptcha-inner"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { whenRecaptchaReady } from '../js/composables/recaptchaLoader'

interface Props {
  siteKey: string
  theme?: 'light' | 'dark'
  size?: 'normal' | 'compact'
  tabindex?: number
}

interface Emits {
  (e: 'verify', token: string): void
  (e: 'expire'): void
  (e: 'error'): void
}

const props = withDefaults(defineProps<Props>(), {
  theme: 'dark',
  size: 'normal',
  tabindex: 0
})

const emit = defineEmits<Emits>()

const recaptchaElement = ref<HTMLDivElement | null>(null)
const recaptchaId = ref<number | null>(null)

onMounted(async () => {
  await whenRecaptchaReady(initRecaptcha)
})

const initRecaptcha = () => {
  if (!props.siteKey || props.siteKey === 'YOUR_RECAPTCHA_V2_SITE_KEY') {
    onRecaptchaError()
    return
  }
  if (!recaptchaElement.value || !window.grecaptcha?.ready) {
    return
  }
  window.grecaptcha.ready(() => {
    if (!recaptchaElement.value || recaptchaId.value !== null) {
      return
    }
    recaptchaId.value = window.grecaptcha.render(recaptchaElement.value, {
      sitekey: props.siteKey,
      theme: props.theme,
      size: props.size,
      tabindex: props.tabindex,
      callback: onRecaptchaSuccess,
      'expired-callback': onRecaptchaExpire,
      'error-callback': onRecaptchaError
    })
  })
}

const onRecaptchaSuccess = (token: string) => {
  emit('verify', token)
}

const onRecaptchaExpire = () => {
  emit('expire')
}

const onRecaptchaError = () => {
  emit('error')
}

const reset = () => {
  if (recaptchaId.value !== null && window.grecaptcha) {
    window.grecaptcha.reset(recaptchaId.value)
  }
}

const getResponse = (): string | null => {
  if (recaptchaId.value !== null && window.grecaptcha) {
    return window.grecaptcha.getResponse(recaptchaId.value) || null
  }
  return null
}

// Expose methods for parent component
defineExpose({
  reset,
  getResponse
})
</script>

<style scoped>
.recaptcha-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 0.25rem 0;
  margin: 0 auto;
}

.recaptcha-inner {
  /* Minimal, clean styling - reCAPTCHA widget centered */
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0.5rem 0;
  background: transparent;
  border: none;
  border-radius: 0;
  transition: all 0.3s ease;
}

/* Desktop - minimal padding */
@media (min-width: 768px) {
  .recaptcha-inner {
    padding: 0.25rem 0;
  }
}

/* Tablet and mobile - tight spacing */
@media (max-width: 767px) {
  .recaptcha-container {
    padding: 0.125rem 0;
  }

  .recaptcha-inner {
    padding: 0.375rem 0;
  }
}

/* Small mobile devices */
@media (max-width: 480px) {
  .recaptcha-inner {
    padding: 0.25rem 0;
  }
}

/* Very small screens */
@media (max-width: 375px) {
  .recaptcha-inner {
    padding: 0.125rem 0;
  }
}

/* Ultra-small screens (320px and below) */
@media (max-width: 320px) {
  .recaptcha-inner {
    padding: 0;
  }
}
</style>

<!-- Type declarations for window.grecaptcha -->
<script lang="ts">
declare global {
  interface Window {
    grecaptcha: {
      render: (
        container: HTMLElement | string,
        options: Record<string, any>
      ) => number
      reset: (id?: number) => void
      getResponse: (id?: number) => string | null
    }
  }
}
</script>
