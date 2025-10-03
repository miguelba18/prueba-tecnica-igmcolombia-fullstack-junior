import { defineStore } from 'pinia';
import { authAPI } from '@/services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('auth_token') || null,
        isAuthenticated: !!localStorage.getItem('auth_token'),
        loading: false,
        error: null
    }),

    getters: {
        currentUser: (state) => state.user,
        isAdmin: (state) => {
            return state.user?.roles?.includes('admin') || false;
        },
        isUser: (state) => {
            return state.user?.roles?.includes('user') || false;
        }
    },

    actions: {
        // Registrar usuario
        async register(userData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await authAPI.register(userData);
                
                this.token = response.data.token;
                this.user = response.data.user;
                this.isAuthenticated = true;

                // Guardar en localStorage
                localStorage.setItem('auth_token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al registrar usuario';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Iniciar sesión
        async login(credentials) {
            this.loading = true;
            this.error = null;

            try {
                const response = await authAPI.login(credentials);
                
                this.token = response.data.token;
                this.user = response.data.user;
                this.isAuthenticated = true;

                // Guardar en localStorage
                localStorage.setItem('auth_token', this.token);
                localStorage.setItem('user', JSON.stringify(this.user));

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Credenciales incorrectas';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Cerrar sesión
        async logout() {
            try {
                await authAPI.logout();
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
            } finally {
                // Limpiar estado
                this.user = null;
                this.token = null;
                this.isAuthenticated = false;

                // Limpiar localStorage
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
            }
        },

        // Obtener usuario autenticado
        async fetchUser() {
            if (!this.token) return;

            this.loading = true;

            try {
                const response = await authAPI.me();
                this.user = response.data.user;
                localStorage.setItem('user', JSON.stringify(this.user));
            } catch (error) {
                console.error('Error al obtener usuario:', error);
                this.logout();
            } finally {
                this.loading = false;
            }
        },

        // Inicializar desde localStorage
        initializeAuth() {
            const token = localStorage.getItem('auth_token');
            const user = localStorage.getItem('user');

            if (token && user) {
                this.token = token;
                this.user = JSON.parse(user);
                this.isAuthenticated = true;
            }
        }
    }
});