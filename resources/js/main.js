import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import '../css/app.css';

// Configure Axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
if (window.BASE_URL) {
  axios.defaults.baseURL = window.BASE_URL;
}
axios.defaults.withCredentials = true; // Required for sessions/cookies across domains

const app = createApp(App);
app.use(router);
app.mount('#app');
