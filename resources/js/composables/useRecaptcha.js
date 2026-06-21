import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { loadRecaptchaV2Script, whenRecaptchaReady, resetLoadPromise } from './recaptchaLoader';
import { getRecaptchaOriginDiagnostic, logRecaptchaOriginDiagnostic } from './recaptchaDiagnostics';

/**
 * Google reCAPTCHA v2 (checkbox) for Vue 3 — vanilla grecaptcha.render, no wrapper library.
 *
 * Site key: window.RECAPTCHA_SITE_KEY (from app.php / .env) or options.siteKey.
 * Script: loaded once via recaptchaLoader (v2 explicit onload callback).
 */
function isRecaptchaEnabled(options) {
  if (options.enabled === false) {
    return false;
  }
  if (options.enabled === true) {
    return true;
  }
  return window.RECAPTCHA_ENABLED !== false;
}

export function useRecaptcha(containerRef, options = {}) {
  const recaptchaError = ref('');
  const widgetId = ref(null);
  const scriptReady = ref(false);
  const enabled = isRecaptchaEnabled(options);

  const siteKey = options.siteKey ?? window.RECAPTCHA_SITE_KEY;

  // Always "normal" — compact + parent transforms break the image puzzle popup.
  const getSize = () => options.size ?? 'normal';

  const renderWidget = async (attempt = 0) => {
    if (!enabled) {
      return;
    }

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
      if (attempt < 2) {
        resetLoadPromise();
        await new Promise((r) => setTimeout(r, 500));
        return renderWidget(attempt + 1);
      }
      recaptchaError.value = err.message;
      return;
    }

    await whenRecaptchaReady(() => {
      if (!containerRef.value || widgetId.value !== null) {
        return;
      }

      if (!window.grecaptcha?.render) {
        if (attempt < 2) {
          setTimeout(() => renderWidget(attempt + 1), 300);
          return;
        }
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
        if (attempt < 2) {
          destroyWidget();
          setTimeout(() => renderWidget(attempt + 1), 300);
          return;
        }
        recaptchaError.value =
          'Could not display reCAPTCHA. Please check your internet connection and try again.';
      }
    });
  };

  const destroyWidget = () => {
    if (containerRef.value) {
      containerRef.value.innerHTML = '';
    }
    widgetId.value = null;
  };

  // Don't eagerly load the reCAPTCHA script on mount.
  // The watch on containerRef (below) will trigger renderWidget()
  // only when the container DOM element actually exists (i.e., the
  // parent shows the recaptcha section after user interaction).

  watch(
    () => containerRef.value,
    async (el) => {
      if (el && widgetId.value === null) {
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
    enabled,
    recaptchaError,
    scriptReady,
    getResponse,
    reset,
    rerender,
    runOriginDiagnostic,
    logOriginDiagnostic: () => logRecaptchaOriginDiagnostic(siteKey, containerRef.value),
  };
}
