import axios from 'axios';

export function useFcmToken() {
  const TOKEN_ENDPOINT = '/api/fcm/register';
  let registerAttempts = 0;
  const MAX_ATTEMPTS = 20;
  const RETRY_DELAY_MS = 3000;

  function isAndroidApp() {
    return navigator.userAgent.includes('TALAbahanAndroidApp');
  }

  function getFcmToken() {
    if (!isAndroidApp()) return null;
    if (window.AndroidBridge && typeof window.AndroidBridge.getFcmToken === 'function') {
      const token = window.AndroidBridge.getFcmToken();
      return token || null;
    }
    return null;
  }

  async function registerFcmToken() {
    const token = getFcmToken();
    if (!token) return false;

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

  return { isAndroidApp, getFcmToken, registerFcmToken, registerFcmTokenWithRetry };
}
