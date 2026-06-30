import axios from 'axios';

export function useFcmToken() {
  const TOKEN_ENDPOINT = '/api/fcm/register';
  const TRUSTED_ENDPOINT = '/api/admin/fcm/toggle-trusted';
  let registerAttempts = 0;
  const MAX_ATTEMPTS = 200;
  const RETRY_DELAY_MS = 3000;

  // Reset attempts when returning to foreground
  if (typeof document !== 'undefined') {
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible' && registerAttempts > 0) {
        registerAttempts = Math.max(0, registerAttempts - 20);
      }
    });
  }

  function getFcmToken() {
    if (window.AndroidBridge && typeof window.AndroidBridge.getFcmToken === 'function') {
      const token = window.AndroidBridge.getFcmToken();
      return token || null;
    }
    return null;
  }

  function getDeviceInfo() {
    return {
      userAgent: navigator.userAgent,
      bridgeAvailable: !!(window.AndroidBridge && typeof window.AndroidBridge.getFcmToken === 'function'),
      tokenPreview: null,
    };
  }

  async function registerFcmToken() {
    const token = getFcmToken();
    if (!token) {
      const info = getDeviceInfo();
      console.log('[FCM] No token available yet (attempt ' + (registerAttempts + 1) + ') '
        + 'bridge=' + info.bridgeAvailable + ' ua=<' + info.userAgent.slice(-30) + '>');
      return false;
    }

    const payload = {
      token,
      platform: 'android',
    };

    const username = localStorage.getItem('username');
    if (username) {
      payload.username = username;
    }

    try {
      const response = await axios.post(TOKEN_ENDPOINT, payload);
      if (response.data?.status === 'success') {
        console.log('[FCM] Token registered successfully');
        registerAttempts = 0;
        return true;
      }
      console.warn('[FCM] Registration response:', response.data);
      return false;
    } catch (err) {
      const msg = err.response?.data?.message || err.message;
      console.warn('[FCM] Registration failed:', msg);
      return false;
    }
  }

  async function registerFcmTokenWithRetry() {
    const result = await registerFcmToken();
    if (result) return true;

    registerAttempts++;
    if (registerAttempts >= MAX_ATTEMPTS) {
      console.warn('[FCM] Max registration attempts reached');
      return false;
    }

    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(registerFcmTokenWithRetry());
      }, RETRY_DELAY_MS);
    });
  }

  async function toggleTrustedDevice(trusted) {
    const token = getFcmToken();
    if (!token) {
      console.warn('[FCM] Cannot toggle trusted status: no token');
      return false;
    }

    try {
      const response = await axios.post(TRUSTED_ENDPOINT, { token, trusted });
      if (response.data?.status === 'success') {
        console.log('[FCM] Trusted device status:', trusted);
        return true;
      }
      console.warn('[FCM] Toggle trusted response:', response.data);
      return false;
    } catch (err) {
      const msg = err.response?.data?.message || err.message;
      console.warn('[FCM] Toggle trusted failed:', msg);
      return false;
    }
  }

  function getTrustedStatus() {
    const token = getFcmToken();
    if (!token) return false;
    const stored = localStorage.getItem('trustedDeviceToken');
    return stored === token;
  }

  return { getFcmToken, getDeviceInfo, registerFcmToken, registerFcmTokenWithRetry, toggleTrustedDevice, getTrustedStatus };
}
