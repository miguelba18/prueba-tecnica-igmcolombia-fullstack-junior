import { defineStore } from 'pinia';
import { invoicesAPI } from '@/services/api';

export const useInvoiceStore = defineStore('invoice', {
    state: () => ({
        invoices: [],
        currentInvoice: null,
        loading: false,
        error: null,
        pagination: {
            currentPage: 1,
            lastPage: 1,
            perPage: 15,
            total: 0
        },
        filters: {
            status: '',
            customer_id: '',
            search: '',
            date_from: '',
            date_to: '',
            sort_by: 'created_at',
            sort_order: 'desc'
        }
    }),

    getters: {
        pendingInvoices: (state) => {
            return state.invoices.filter(invoice => invoice.status === 'pending');
        },
        paidInvoices: (state) => {
            return state.invoices.filter(invoice => invoice.status === 'paid');
        },
        overdueInvoices: (state) => {
            return state.invoices.filter(invoice => invoice.status === 'overdue');
        },
        totalAmount: (state) => {
            return state.invoices.reduce((sum, invoice) => sum + parseFloat(invoice.total_amount), 0);
        }
    },

    actions: {
        // Listar facturas con filtros
        async fetchInvoices(page = 1) {
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

                const response = await invoicesAPI.list(params);

                this.invoices = response.data.data;
                this.pagination = {
                    currentPage: response.data.meta.current_page,
                    lastPage: response.data.meta.last_page,
                    perPage: response.data.meta.per_page,
                    total: response.data.meta.total
                };

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar facturas';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Obtener una factura específica
        async fetchInvoice(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await invoicesAPI.get(id);
                this.currentInvoice = response.data.data;
                return response.data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cargar factura';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Crear factura
        async createInvoice(invoiceData) {
            this.loading = true;
            this.error = null;

            try {
                const formData = new FormData();

                // Agregar campos principales
                Object.keys(invoiceData).forEach(key => {
                    if (key !== 'items' && key !== 'attachment') {
                        formData.append(key, invoiceData[key]);
                    }
                });

                // Agregar items
                if (invoiceData.items && invoiceData.items.length > 0) {
                    invoiceData.items.forEach((item, index) => {
                        Object.keys(item).forEach(key => {
                            formData.append(`items[${index}][${key}]`, item[key]);
                        });
                    });
                }

                // Agregar archivo adjunto
                if (invoiceData.attachment) {
                    formData.append('attachment', invoiceData.attachment);
                }

                const response = await invoicesAPI.create(formData);
                
                // Recargar lista
                await this.fetchInvoices(this.pagination.currentPage);

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al crear factura';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Actualizar factura
        async updateInvoice(id, invoiceData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await invoicesAPI.update(id, invoiceData);
                
                // Actualizar en la lista
                const index = this.invoices.findIndex(inv => inv.id === id);
                if (index !== -1) {
                    this.invoices[index] = response.data.data;
                }

                // Actualizar currentInvoice si es la misma
                if (this.currentInvoice?.id === id) {
                    this.currentInvoice = response.data.data;
                }

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al actualizar factura';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Eliminar factura
        async deleteInvoice(id) {
            this.loading = true;
            this.error = null;

            try {
                await invoicesAPI.delete(id);
                
                // Remover de la lista
                this.invoices = this.invoices.filter(inv => inv.id !== id);
                this.pagination.total--;

                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al eliminar factura';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Cambiar estado de factura
        async changeInvoiceStatus(id, status) {
            this.loading = true;
            this.error = null;

            try {
                const response = await invoicesAPI.changeStatus(id, status);
                
                // Actualizar en la lista
                const index = this.invoices.findIndex(inv => inv.id === id);
                if (index !== -1) {
                    this.invoices[index] = response.data.data;
                }

                // Actualizar currentInvoice si es la misma
                if (this.currentInvoice?.id === id) {
                    this.currentInvoice = response.data.data;
                }

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al cambiar estado';
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
                status: '',
                customer_id: '',
                search: '',
                date_from: '',
                date_to: '',
                sort_by: 'created_at',
                sort_order: 'desc'
            };
        },

        // Limpiar estado
        clearCurrentInvoice() {
            this.currentInvoice = null;
        }
    }
});