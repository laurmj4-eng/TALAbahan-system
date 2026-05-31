import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { loadRecaptchaV2Script, whenRecaptchaReady } from './recaptchaLoader';
import { getRecaptchaOriginDiagnostic, logRecaptchaOriginDiagnostic } from './recaptchaDiagnostics';

/**
 * Google reCAPTCHA v2 (checkbox) for Vue 3 — vanilla grecaptcha.render, no wrapper library.
 *
 * Site key: window.RECAPTCHA_SITE_KEY (from spa.php / .env) or options.siteKey.
 * Script: loaded once via recaptchaLoader (v2 explicit onload callback).
 */
export function useRecaptcha(containerRef, options = {}) {
  const recaptchaError = ref('');
  const widgetId = ref(null);
  const scriptReady = ref(false);

  const siteKey = options.siteKey ?? window.RECAPTCHA_SITE_KEY;

  const getSize = () => {
    if (options.size) return options.size;
    return typeof window !== 'undefined' && window.innerWidth < 480 ? 'compact' : 'normal';
  };

  const renderWidget = async () => {
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

    try {
      await loadRecaptchaV2Script();
      scriptReady.value = true;
    } catch (err) {
      recaptchaError.value = err.message;
      return;
    }

    await whenRecaptchaReady(() => {
      if (!containerRef.value || widgetId.value !== null) {
        return;
      }

      if (!window.grecaptcha?.render) {
        recaptchaError.value = 'reCAPTCHA script did not initialize grecaptcha.render.';
        return;
      }

      try {
        widgetId.value = window.grecaptcha.render(containerRef.value, {
          sitekey: siteKey,
          theme: options.theme ?? 'light',
          size: getSize(),
          callback: (token) => {
            recaptchaError.value = '';
            options.onVerify?.(token);
            if (options.debug) {
              logRecaptchaOriginDiagnostic(siteKey, containerRef.value);
            }
          },
          'expired-callback': options.onExpire,
          'error-callback': () => {
            recaptchaError.value =
              'reCAPTCHA failed to load. In Google reCAPTCHA Admin, add this host to your v2 key domains (localhost and/or 127.0.0.1).';
            options.onError?.();
            if (options.debug) {
              logRecaptchaOriginDiagnostic(siteKey, containerRef.value);
            }
          },
        });

        if (options.debug) {
          setTimeout(
            () => logRecaptchaOriginDiagnostic(siteKey, containerRef.value),
            800,
          );
        }
      } catch (err) {
        console.error('[reCAPTCHA] render error:', err);
        recaptchaError.value =
          'Could not display reCAPTCHA. Confirm the key is reCAPTCHA v2 ("I\'m not a robot"), not v3 or Enterprise.';
      }
    });
  };

  const destroyWidget = () => {
    if (containerRef.value) {
      containerRef.value.innerHTML = '';
    }
    widgetId.value = null;
  };

  onMounted(async () => {
    await nextTick();
    if (containerRef.value) {
      await renderWidget();
    }
  });

  watch(
    () => containerRef.value,
    async (el) => {
      if (el && widgetId.value === null && scriptReady.value) {
        await nextTick();
        await renderWidget();
      }
    },
  );

  onBeforeUnmount(() => {
    destroyWidget();
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
    destroyWidget();
    recaptchaError.value = '';
    await nextTick();
    await renderWidget();
  };

  const runOriginDiagnostic = () =>
    getRecaptchaOriginDiagnostic(siteKey, containerRef.value);

  return {
    recaptchaError,
    scriptReady,
    getResponse,
    reset,
    rerender,
    runOriginDiagnostic,
    logOriginDiagnostic: () => logRecaptchaOriginDiagnostic(siteKey, containerRef.value),
  };
}
