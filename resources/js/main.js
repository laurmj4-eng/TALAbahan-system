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

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
    return pages[`./pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
});
