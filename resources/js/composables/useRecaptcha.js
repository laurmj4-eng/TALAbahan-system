import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';

/**
 * Load and render Google reCAPTCHA v2 (checkbox / image challenge).
 * Uses grecaptcha.ready() so it works with api.js?render=explicit from app.php.
 */
export function useRecaptcha(containerRef, options = {}) {
  const recaptchaError = ref('');
  const widgetId = ref(null);

  const siteKey = options.siteKey ?? window.RECAPTCHA_SITE_KEY;

  const getSize = () => {
    if (options.size) return options.size;
    return typeof window !== 'undefined' && window.innerWidth < 480 ? 'compact' : 'normal';
  };

  const renderWidget = () => {
    if (!siteKey || siteKey === 'undefined' || siteKey === 'null') {
      recaptchaError.value =
        'reCAPTCHA is not configured. Set RECAPTCHA_SITE_KEY in your .env file and restart the server.';
      return;
    }

    if (!containerRef.value) {
      return;
    }

    if (widgetId.value !== null) {
      return;
    }

    if (!window.grecaptcha?.ready) {
      recaptchaError.value = 'reCAPTCHA script did not load. Check your network or ad blocker.';
      return;
    }

    window.grecaptcha.ready(() => {
      if (!containerRef.value || widgetId.value !== null) {
        return;
      }

      try {
        widgetId.value = window.grecaptcha.render(containerRef.value, {
          sitekey: siteKey,
          theme: options.theme ?? 'light',
          size: getSize(),
          callback: options.onVerify,
          'expired-callback': options.onExpire,
          'error-callback': () => {
            recaptchaError.value =
              'reCAPTCHA failed to load. In Google reCAPTCHA Admin, add this site domain (e.g. localhost) to your key.';
            options.onError?.();
          },
        });
      } catch (err) {
        console.error('[reCAPTCHA] render error:', err);
        recaptchaError.value =
          'Could not display reCAPTCHA. Refresh the page. If it persists, verify your site key is reCAPTCHA v2 and domains are allowed.';
      }
    });
  };

  const ensureScript = () =>
    new Promise((resolve) => {
      if (window.grecaptcha?.ready) {
        window.grecaptcha.ready(resolve);
        return;
      }

      const existing = document.querySelector('script[src*="google.com/recaptcha/api.js"]');
      if (existing) {
        const deadline = Date.now() + 15000;
        const tick = () => {
          if (window.grecaptcha?.ready) {
            window.grecaptcha.ready(resolve);
          } else if (Date.now() < deadline) {
            setTimeout(tick, 100);
          } else {
            recaptchaError.value = 'reCAPTCHA script timed out loading.';
            resolve();
          }
        };
        tick();
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
      script.async = true;
      script.defer = true;
      script.onload = () => {
        if (window.grecaptcha?.ready) {
          window.grecaptcha.ready(resolve);
        } else {
          resolve();
        }
      };
      script.onerror = () => {
        recaptchaError.value =
          'Could not load Google reCAPTCHA. Disable ad blockers or allow google.com / gstatic.com.';
        resolve();
      };
      document.head.appendChild(script);
    });

  onMounted(async () => {
    await nextTick();
    await ensureScript();
    renderWidget();
  });

  onBeforeUnmount(() => {
    if (containerRef.value) {
      containerRef.value.innerHTML = '';
    }
    widgetId.value = null;
  });

  const getResponse = () => {
    if (widgetId.value === null || !window.grecaptcha) {
      return '';
    }
    return window.grecaptcha.getResponse(widgetId.value) || '';
  };

  const reset = () => {
    if (widgetId.value !== null && window.grecaptcha) {
      window.grecaptcha.reset(widgetId.value);
    }
  };

  const rerender = async () => {
    if (containerRef.value) {
      containerRef.value.innerHTML = '';
    }
    widgetId.value = null;
    recaptchaError.value = '';
    await nextTick();
    renderWidget();
  };

  return {
    recaptchaError,
    getResponse,
    reset,
    rerender,
  };
}
