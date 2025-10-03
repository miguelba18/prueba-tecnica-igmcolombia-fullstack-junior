import { defineStore } from 'pinia';
import { usersAPI } from '@/services/api';

export const useUserStore = defineStore('user', {
    state: () => ({
        users: [],
        currentUser: null,
        loading: false,
        error: null,
        pagination: {
            currentPage: 1,
            lastPage: 1,
            perPage: 15,
            total: 0
        },
        filters: {
            search: '',
            role: '',
            sort_by: 'created_at',
            sort_order: 'desc'
        }
    }),

    getters: {
        // Obtener usuarios por rol
        adminUsers: (state) => {
            return state.users.filter(user => user.roles?.includes('admin'));
        },
        regularUsers: (state) => {
            return state.users.filter(user => user.roles?.includes('user'));
        },
        
        // Buscar usuario por ID
        getUserById: (state) => (id) => {
            return state.users.find(user => user.id === id);
        }
    },

    actions: {
        // Listar usuarios con filtros (solo admin)
        async fetchUsers(page = 1) {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    page,
                    per_page: this.pagination.perPage,
                    ...this.filters
                };

                // Limpiar parámetros vacíos
                Object.keys(params).forEach(key => {
                    if (params[key] === '' || params[key] === null) {
                        delete params[key];
                    }
                });

                const response = await usersAPI.list(params);

                this.users = response.data.data;
                this.pagination = {
                    currentPage: response.data.meta.current_page,
                    lastPage: response.data.meta.last_page,
                    perPage: response.data.meta.per_page,
                    total: response.data.meta.total
                };

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar usuarios';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Obtener un usuario específico
        async fetchUser(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await usersAPI.get(id);
                this.currentUser = response.data.data;
                return response.data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar usuario';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Actualizar usuario
        async updateUser(id, userData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await usersAPI.update(id, userData);
                
                // Actualizar en la lista
                const index = this.users.findIndex(user => user.id === id);
                if (index !== -1) {
                    this.users[index] = response.data.data;
                }

                // Actualizar currentUser si es el mismo
                if (this.currentUser?.id === id) {
                    this.currentUser = response.data.data;
                }

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar usuario';
                
                // Manejar errores de validación
                if (error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    const errorMessages = Object.values(errors).flat().join(', ');
                    this.error = errorMessages;
                }
                
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Eliminar usuario
        async deleteUser(id) {
            this.loading = true;
            this.error = null;

            try {
                await usersAPI.delete(id);
                
                // Remover de la lista
                this.users = this.users.filter(user => user.id !== id);
                this.pagination.total--;

                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar usuario';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Establecer filtros
        setFilters(filters) {
            this.filters = { ...this.filters, ...filters };
        },

        // Limpiar filtros
        clearFilters() {
            this.filters = {
                search: '',
                role: '',
                sort_by: 'created_at',
                sort_order: 'desc'
            };
        },

        // Limpiar estado
        clearCurrentUser() {
            this.currentUser = null;
        }
    }
});