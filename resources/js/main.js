import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import '../css/app.css';

// Configure Axios to send X-Requested-With header for CodeIgniter's isAJAX()
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const app = createApp(App);
app.use(router);
app.mount('#app');
