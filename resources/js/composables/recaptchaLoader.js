/**
 * Singleton loader for Google reCAPTCHA v2 (checkbox) — explicit render only.
 *
 * Do NOT use api.js?render=YOUR_SITE_KEY (that is reCAPTCHA v3).
 * Do NOT load enterprise.js unless your Admin console key is Enterprise.
 */

const RECAPTCHA_SCRIPT_ID = 'google-recaptcha-v2-explicit';
const RECAPTCHA_SCRIPT_URL =
  'https://www.google.com/recaptcha/api.js?onload=__talabahanRecaptchaOnload&render=explicit';

/** @type {Promise<void> | null} */
let loadPromise = null;

/** @type {Array<() => void>} */
const readyQueue = [];

function flushReadyQueue() {
  const queue = readyQueue.splice(0, readyQueue.length);
  queue.forEach((fn) => {
    try {
      fn();
    } catch (err) {
      console.error('[reCAPTCHA] ready callback error:', err);
    }
  });
}

/**
 * Global onload hook required by api.js?onload=...&render=explicit
 * (standard Google v2 pattern; avoids duplicate script tags racing).
 */
window.__talabahanRecaptchaOnload = function __talabahanRecaptchaOnload() {
  if (window.grecaptcha?.ready) {
    window.grecaptcha.ready(flushReadyQueue);
  } else {
    flushReadyQueue();
  }
};

/**
 * @returns {Promise<void>}
 */
export function loadRecaptchaV2Script() {
  if (window.grecaptcha?.render) {
    return Promise.resolve();
  }

  if (loadPromise) {
    return loadPromise;
  }

  loadPromise = new Promise((resolve, reject) => {
    readyQueue.push(resolve);

    if (window.grecaptcha?.render) {
      if (window.grecaptcha.ready) {
        window.grecaptcha.ready(flushReadyQueue);
      } else {
        flushReadyQueue();
      }
      return;
    }

    // Drop legacy tags from old PHP shells (api.js without our onload hook) to avoid double init.
    document.querySelectorAll('script[src*="google.com/recaptcha/api.js"]').forEach((node) => {
      if (node.id !== RECAPTCHA_SCRIPT_ID) {
        node.remove();
      }
    });

    const existing = document.getElementById(RECAPTCHA_SCRIPT_ID);
    if (existing) {
      if (window.grecaptcha?.ready) {
        window.grecaptcha.ready(flushReadyQueue);
      }
      return;
    }

    const script = document.createElement('script');
    script.id = RECAPTCHA_SCRIPT_ID;
    script.src = RECAPTCHA_SCRIPT_URL;
    script.async = true;
    script.defer = true;

    script.onerror = () => {
      loadPromise = null;
      readyQueue.length = 0;
      reject(
        new Error(
          'Failed to load https://www.google.com/recaptcha/api.js — check ad blockers and allow google.com / gstatic.com.',
        ),
      );
    };

    document.head.appendChild(script);
  });

  return loadPromise;
}

/**
 * Run fn after grecaptcha is ready (v2 explicit).
 * @param {() => void} fn
 * @returns {Promise<void>}
 */
export async function whenRecaptchaReady(fn) {
  await loadRecaptchaV2Script();
  return new Promise((resolve) => {
    const run = () => {
      fn();
      resolve();
    };
    if (window.grecaptcha?.ready) {
      window.grecaptcha.ready(run);
    } else {
      readyQueue.push(run);
    }
  });
}
