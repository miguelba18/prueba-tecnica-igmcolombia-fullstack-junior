import { defineStore } from 'pinia';
import { customersAPI } from '@/services/api';

export const useCustomerStore = defineStore('customer', {
    state: () => ({
        customers: [],
        currentCustomer: null,
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
            document_type: '',
            sort_by: 'created_at',
            sort_order: 'desc'
        }
    }),

    getters: {
        // Obtener clientes por tipo de documento
        customersByType: (state) => (type) => {
            return state.customers.filter(customer => customer.document_type === type);
        },
        
        // Buscar cliente por ID
        getCustomerById: (state) => (id) => {
            return state.customers.find(customer => customer.id === id);
        }
    },

    actions: {
        // Listar clientes con filtros
        async fetchCustomers(page = 1) {
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

                const response = await customersAPI.list(params);

                this.customers = response.data.data;
                this.pagination = {
                    currentPage: response.data.meta.current_page,
                    lastPage: response.data.meta.last_page,
                    perPage: response.data.meta.per_page,
                    total: response.data.meta.total
                };

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar clientes';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Obtener todos los clientes sin paginación (para selects)
        async fetchAllCustomers() {
            try {
                const response = await customersAPI.list({ per_page: 1000 });
                return response.data.data;
            } catch (error) {
                console.error('Error al cargar clientes:', error);
                return [];
            }
        },

        // Obtener un cliente específico
        async fetchCustomer(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await customersAPI.get(id);
                this.currentCustomer = response.data.data;
                return response.data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar cliente';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Crear cliente
        async createCustomer(customerData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await customersAPI.create(customerData);
                
                // Recargar lista
                await this.fetchCustomers(this.pagination.currentPage);

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear cliente';
                
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

        // Actualizar cliente
        async updateCustomer(id, customerData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await customersAPI.update(id, customerData);
                
                // Actualizar en la lista
                const index = this.customers.findIndex(cust => cust.id === id);
                if (index !== -1) {
                    this.customers[index] = response.data.data;
                }

                // Actualizar currentCustomer si es el mismo
                if (this.currentCustomer?.id === id) {
                    this.currentCustomer = response.data.data;
                }

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar cliente';
                
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

        // Eliminar cliente
        async deleteCustomer(id) {
            this.loading = true;
            this.error = null;

            try {
                await customersAPI.delete(id);
                
                // Remover de la lista
                this.customers = this.customers.filter(cust => cust.id !== id);
                this.pagination.total--;

                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar cliente';
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
                document_type: '',
                sort_by: 'created_at',
                sort_order: 'desc'
            };
        },

        // Limpiar estado
        clearCurrentCustomer() {
            this.currentCustomer = null;
        }
    }
});