import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

// Bootstrap CSS
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Crear instancia de la aplicación
const app = createApp(App);
const pinia = createPinia();

// Registrar plugins
app.use(pinia);
app.use(router);

// Importar y usar el store de autenticación para inicializar
import { useAuthStore } from '@/stores/auth';

// Montar la aplicación
app.mount('#app');

// Inicializar autenticación desde localStorage
const authStore = useAuthStore();
authStore.initializeAuth();