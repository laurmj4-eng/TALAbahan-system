import axios from 'axios';

export function useFcmToken() {
  const TOKEN_ENDPOINT = '/api/fcm/register';

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

  return { isAndroidApp, getFcmToken, registerFcmToken };
}
