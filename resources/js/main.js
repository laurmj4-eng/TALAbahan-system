import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import axios from 'axios';
import '../css/app.css';

// Configure Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
if (window.BASE_URL) {
  axios.defaults.baseURL = window.BASE_URL;
}
axios.defaults.withCredentials = true;

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
    page: initialPage, // CRITICAL: Use 'page' property, not 'initialPage'
    resolve: (name) => {
      const pages = import.meta.glob('./Page/**/*.vue', { eager: true });
      const path = `./Page/${name}.vue`;
      const pageComponent = pages[path];
      
      if (!pageComponent) {
        console.error(`[Inertia] Component not found: ${path}`);
        // Try fallback search
        const match = Object.keys(pages).find(key => key.toLowerCase().endsWith(`${name.toLowerCase()}.vue`));
        if (match) return pages[match].default || pages[match];
        throw new Error(`Component not found: ${name}`);
      }
      
      return pageComponent.default || pageComponent;
    },
    setup({ el, App, props, plugin }) {
      createApp({ render: () => h(App, props) })
        .use(plugin)
        .mount(el);
    },
  });
}

// Run initialization
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initInertia);
} else {
  initInertia();
}
