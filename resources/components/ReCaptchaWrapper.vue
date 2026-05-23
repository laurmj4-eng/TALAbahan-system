<template>
  <div class="recaptcha-container">
    <div ref="recaptchaElement" class="recaptcha-inner"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'

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

onMounted(() => {
  // Load Google's reCAPTCHA script if not already loaded
  if (!window.grecaptcha) {
    const script = document.createElement('script')
    script.src = 'https://www.google.com/recaptcha/api.js'
    script.async = true
    script.defer = true
    script.onload = () => initRecaptcha()
    document.head.appendChild(script)
  } else {
    initRecaptcha()
  }
})

const initRecaptcha = () => {
  if (recaptchaElement.value && window.grecaptcha) {
    recaptchaId.value = window.grecaptcha.render(recaptchaElement.value, {
      sitekey: props.siteKey,
      theme: props.theme,
      size: props.size,
      tabindex: props.tabindex,
      callback: onRecaptchaSuccess,
      'expired-callback': onRecaptchaExpire,
      'error-callback': onRecaptchaError
    })
  }
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
  width: 100%;
}

.recaptcha-inner {
  /* Ensures reCAPTCHA is centered and responsive */
  display: flex;
  justify-content: center;
}

/* Scale down reCAPTCHA for very small screens (< 350px) */
@media (max-width: 350px) {
  .recaptcha-inner {
    transform: scale(0.9);
    transform-origin: top center;
  }
}

/* Further scale for ultra-small screens */
@media (max-width: 320px) {
  .recaptcha-inner {
    transform: scale(0.75);
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
