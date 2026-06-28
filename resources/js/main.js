import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import axios from 'axios';
import '../css/app.css';
import '../css/recaptcha.css';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import { useFcmToken } from './composables/useFcmToken.js';
import PullToRefreshOverlay from './Components/PullToRefreshOverlay.vue';

const style = document.createElement('style');
style.textContent = '#nprogress .bar{height:4px!important;box-shadow:0 0 10px #22d3ee,0 0 20px #22d3ee}';
document.head.appendChild(style);

// Show progress bar on manual full page reloads (e.g., login, logout)
window.addEventListener('beforeunload', () => {
  NProgress.start();
});

// Configure Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
if (window.BASE_URL) {
  axios.defaults.baseURL = window.BASE_URL;
}
axios.defaults.withCredentials = true;

// Global Axios interceptor for CSRF tokens
axios.interceptors.request.use(config => {
  const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  const csrfName = document.querySelector('meta[name="csrf-name"]')?.content || 'csrf_test_name';
  
  // Ensure AJAX header is sent
  config.headers['X-Requested-With'] = 'XMLHttpRequest';
  
  if (csrfToken && (config.method === 'post' || config.method === 'put' || config.method === 'delete')) {
    config.headers[csrfHeader] = csrfToken;
    
    // If using URLSearchParams, also inject CSRF into the body for redundancy
    if (config.data instanceof URLSearchParams) {
      config.data.set(csrfName, csrfToken);
    }
  }
  return config;
});

// Global Axios response interceptor to update CSRF tokens
axios.interceptors.response.use(response => {
  const csrfHeader = document.querySelector('meta[name="csrf-header"]')?.content || 'X-CSRF-TOKEN';
  const newToken = response.headers?.[csrfHeader.toLowerCase()] || response.data?.token;
  if (newToken) {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) meta.content = newToken;
  }
  return response;
});

// Standardize the initialization process
function initInertia() {
  const el = document.getElementById('app');
  if (!el) {
    console.error('[Inertia] Root element #app not found.');
    return;
  }

  let initialPage = null;
  try {
    initialPage = JSON.parse(el.dataset.page);
    console.log('[Inertia] Successfully parsed initial page data.');
  } catch (e) {
    console.error('[Inertia] Failed to parse page data:', e);
  }

  if (!initialPage) {
    console.error('[Inertia] Page data is null or undefined.');
    return;
  }

  createInertiaApp({
    progress: {
      color: '#22d3ee',
      includeCSS: true,
    },
    page: initialPage, // CRITICAL: Use 'page' property, not 'initialPage'
    resolve: (name) => {
      const pages = import.meta.glob('./Page/**/*.vue');
      const path = `./Page/${name}.vue`;
      const pageImport = pages[path];
      
      if (!pageImport) {
        console.error(`[Inertia] Component not found: ${path}`);
        // Try fallback search
        const match = Object.keys(pages).find(key => key.toLowerCase().endsWith(`${name.toLowerCase()}.vue`));
        if (match) return pages[match]();
        throw new Error(`Component not found: ${name}`);
      }
      
      return pageImport();
    },
    setup({ el, App, props, plugin }) {
      const app = createApp({
        render() {
          return [h(App, props), h(PullToRefreshOverlay)];
        },
      })
        .use(plugin)
        .mount(el);

      if (localStorage.getItem('isLoggedIn') === 'true') {
        const { registerFcmTokenWithRetry } = useFcmToken();
        registerFcmTokenWithRetry();
      }
    },
  });
}

// Run initialization
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initInertia);
} else {
  initInertia();
}
