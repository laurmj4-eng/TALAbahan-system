/**
 * Localhost / origin handshake helpers for reCAPTCHA v2.
 * The `co` query param on the anchor iframe is chosen by Google from window.location.origin —
 * you cannot pass it to grecaptcha.render(); fix domain mismatches in Admin Console instead.
 */

function decodeCoParam(co) {
  if (!co) return null;
  try {
    const normalized = co.replace(/-/g, '+').replace(/_/g, '/');
    const padded = normalized + '='.repeat((4 - (normalized.length % 4)) % 4);
    return atob(padded).replace(/\0/g, '').trim();
  } catch {
    return null;
  }
}

/**
 * @param {string} [siteKey]
 * @param {HTMLElement | null} [containerEl]
 */
export function getRecaptchaOriginDiagnostic(siteKey, containerEl = null) {
  const origin = window.location.origin;
  const hostname = window.location.hostname;
  const hostWithPort = window.location.host;
  const protocol = window.location.protocol;

  let iframeCo = null;
  let iframeDecodedOrigin = null;
  let iframeSrc = null;

  const root =
    containerEl ||
    document.querySelector('.recaptcha-widget-host') ||
    document.querySelector('.g-recaptcha') ||
    document.querySelector('[data-sitekey]');

  const iframe = root?.querySelector?.('iframe') || document.querySelector('iframe[src*="google.com/recaptcha"]');
  if (iframe?.src) {
    iframeSrc = iframe.src;
    try {
      const url = new URL(iframe.src);
      iframeCo = url.searchParams.get('co');
      iframeDecodedOrigin = decodeCoParam(iframeCo);
    } catch {
      /* ignore malformed iframe src during early lifecycle */
    }
  }

  const originsMatch =
    iframeDecodedOrigin != null &&
    (iframeDecodedOrigin === origin ||
      iframeDecodedOrigin.startsWith(`${protocol}//${hostname}`));

  const localhostVariants = ['localhost', '127.0.0.1'];
  const isLocalDev = localhostVariants.includes(hostname);

  const report = {
    siteKey: siteKey || window.RECAPTCHA_SITE_KEY || '(not set)',
    pageOrigin: origin,
    pageHostname: hostname,
    pageHost: hostWithPort,
    expectedAdminDomains: isLocalDev
      ? ['localhost', '127.0.0.1 (add both if you use either URL)']
      : [hostname],
    iframeCoEncoded: iframeCo,
    iframeCoDecodedOrigin: iframeDecodedOrigin,
    iframeSrcSnippet: iframeSrc ? iframeSrc.slice(0, 120) + '...' : null,
    originHandshakeOk: originsMatch,
    grecaptchaLoaded: Boolean(window.grecaptcha?.render),
    notes: [],
  };

  if (!iframe) {
    report.notes.push('No reCAPTCHA iframe yet — complete script load + grecaptcha.render() first.');
  } else if (!originsMatch && iframeDecodedOrigin) {
    report.notes.push(
      `Mismatch: page is ${origin} but iframe co decodes to "${iframeDecodedOrigin}". ` +
        'Use the same host in the browser and in Google Admin (localhost vs 127.0.0.1).',
    );
  } else if (originsMatch) {
    report.notes.push('Origin handshake looks correct for this tab.');
  }

  if (hostname === '127.0.0.1' && !report.expectedAdminDomains.includes('127.0.0.1')) {
    report.notes.push('Add 127.0.0.1 to reCAPTCHA Admin domains, not only localhost.');
  }

  return report;
}

/**
 * Logs a structured diagnostic table (dev-only usage).
 */
export function logRecaptchaOriginDiagnostic(siteKey, containerEl) {
  const report = getRecaptchaOriginDiagnostic(siteKey, containerEl);
  console.group('[reCAPTCHA] Origin diagnostic');
  console.table(report);
  report.notes.forEach((n) => console.info(n));
  console.groupEnd();
  return report;
}
