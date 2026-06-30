const INACTIVITY_LIMIT = 300000;

export function useAutoLogout() {
  function init() {
    document.addEventListener('visibilitychange', handleVisibilityChange);
  }

  function destroy() {
    document.removeEventListener('visibilitychange', handleVisibilityChange);
  }

  function handleVisibilityChange() {
    if (document.visibilityState === 'hidden') {
      localStorage.setItem('app_backgrounded_time', Date.now());
    } else if (document.visibilityState === 'visible') {
      const stored = localStorage.getItem('app_backgrounded_time');
      if (stored) {
        const elapsed = Date.now() - Number(stored);
        if (elapsed >= INACTIVITY_LIMIT) {
          localStorage.removeItem('app_backgrounded_time');
          window.location.href = (window.BASE_URL || '/') + 'logout?expired=1';
        }
      }
    }
  }

  return { init, destroy };
}
