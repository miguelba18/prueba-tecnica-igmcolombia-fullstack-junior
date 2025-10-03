<template>
    <div class="invoice-list">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-receipt"></i> Facturas
            </h2>
            <router-link to="/invoices/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Nueva Factura
            </router-link>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Búsqueda -->
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Número de factura..."
                            v-model="filters.search"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Estado -->
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select" v-model="filters.status" @change="applyFilters">
                            <option value="">Todos</option>
                            <option value="pending">Pendiente</option>
                            <option value="paid">Pagada</option>
                            <option value="overdue">Vencida</option>
                        </select>
                    </div>

                    <!-- Cliente -->
                    <div class="col-md-3">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" v-model="filters.customer_id" @change="applyFilters">
                            <option value="">Todos</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                                {{ customer.full_name }}
                            </option>
                        </select>
                    </div>

                    <!-- Fecha desde -->
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input
                            type="date"
                            class="form-control"
                            v-model="filters.date_from"
                            @change="applyFilters"
                        />
                    </div>

                    <!-- Fecha hasta -->
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input
                            type="date"
                            class="form-control"
                            v-model="filters.date_to"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button @click="clearFilters" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i>
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de facturas -->
        <div class="card">
            <div class="card-body">
                <!-- Loading -->
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando facturas...</p>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ error }}
                </div>

                <!-- Sin resultados -->
                <div v-else-if="invoices.length === 0" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-3">No se encontraron facturas</p>
                    <router-link to="/invoices/create" class="btn btn-primary">
                        Crear primera factura
                    </router-link>
                </div>

                <!-- Tabla -->
                <div v-else>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Fecha Emisión</th>
                                    <th>Vencimiento</th>
                                    <th>Monto Total</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in invoices" :key="invoice.id">
                                    <td>
                                        <strong>{{ invoice.invoice_number }}</strong>
                                    </td>
                                    <td>{{ invoice.customer?.full_name }}</td>
                                    <td>{{ formatDate(invoice.issue_date) }}</td>
                                    <td>{{ formatDate(invoice.due_date) }}</td>
                                    <td>
                                        <strong>${{ formatNumber(invoice.total_amount) }}</strong>
                                    </td>
                                    <td>
                                        <span :class="getStatusClass(invoice.status)">
                                            {{ getStatusText(invoice.status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <router-link
                                                :to="`/invoices/${invoice.id}`"
                                                class="btn btn-outline-info"
                                                title="Ver detalle"
                                            >
                                                <i class="bi bi-eye">Ver</i>
                                            </router-link>
                                            <router-link
                                                :to="`/invoices/${invoice.id}/edit`"
                                                class="btn btn-outline-warning"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil">Editar</i>
                                            </router-link>
                                            <button
                                                @click="confirmDelete(invoice)"
                                                class="btn btn-outline-danger"
                                                title="Eliminar"
                                            >
                                                <i class="bi bi-trash">Eliminar</i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Mostrando {{ invoices.length }} de {{ pagination.total }} facturas
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li class="page-item" :class="{ disabled: pagination.currentPage === 1 }">
                                    <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage - 1)">
                                        Anterior
                                    </a>
                                </li>
                                <li
                                    v-for="page in displayPages"
                                    :key="page"
                                    class="page-item"
                                    :class="{ active: page === pagination.currentPage }"
                                >
                                    <a class="page-link" href="#" @click.prevent="changePage(page)">
                                        {{ page }}
                                    </a>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination.currentPage === pagination.lastPage }">
                                    <a class="page-link" href="#" @click.prevent="changePage(pagination.currentPage + 1)">
                                        Siguiente
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue';
import { useInvoiceStore } from '@/stores/invoice';
import { useCustomerStore } from '@/stores/customer';

export default {
    name: 'InvoiceList',
    setup() {
        const invoiceStore = useInvoiceStore();
        const customerStore = useCustomerStore();

        const invoices = computed(() => invoiceStore.invoices);
        const loading = computed(() => invoiceStore.loading);
        const error = computed(() => invoiceStore.error);
        const pagination = computed(() => invoiceStore.pagination);

        const customers = ref([]);
        const filters = reactive({
            search: '',
            status: '',
            customer_id: '',
            date_from: '',
            date_to: ''
        });

        let searchTimeout = null;

        const loadInvoices = async (page = 1) => {
            await invoiceStore.fetchInvoices(page);
        };

        const loadCustomers = async () => {
            customers.value = await customerStore.fetchAllCustomers();
        };

        const applyFilters = () => {
            invoiceStore.setFilters(filters);
            loadInvoices(1);
        };

        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        };

        const clearFilters = () => {
            filters.search = '';
            filters.status = '';
            filters.customer_id = '';
            filters.date_from = '';
            filters.date_to = '';
            invoiceStore.clearFilters();
            loadInvoices(1);
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.lastPage) {
                loadInvoices(page);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };

        const displayPages = computed(() => {
            const current = pagination.value.currentPage;
            const last = pagination.value.lastPage;
            const pages = [];

            if (last <= 7) {
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 4) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                    pages.push('...');
                    pages.push(last);
                } else if (current >= last - 3) {
                    pages.push(1);
                    pages.push('...');
                    for (let i = last - 4; i <= last; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                    pages.push('...');
                    pages.push(last);
                }
            }

            return pages;
        });

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString('es-CO');
        };

        const formatNumber = (number) => {
            return new Intl.NumberFormat('es-CO').format(number);
        };

        const getStatusClass = (status) => {
            const classes = {
                pending: 'badge bg-warning text-dark',
                paid: 'badge bg-success',
                overdue: 'badge bg-danger'
            };
            return classes[status] || 'badge bg-secondary';
        };

        const getStatusText = (status) => {
            const texts = {
                pending: 'Pendiente',
                paid: 'Pagada',
                overdue: 'Vencida'
            };
            return texts[status] || status;
        };

        const confirmDelete = async (invoice) => {
            if (confirm(`¿Estás seguro de eliminar la factura ${invoice.invoice_number}?`)) {
                try {
                    await invoiceStore.deleteInvoice(invoice.id);
                    alert('Factura eliminada exitosamente');
                } catch (error) {
                    alert('Error al eliminar la factura: ' + error.message);
                }
            }
        };

        onMounted(() => {
            loadInvoices();
            loadCustomers();
        });

        return {
            invoices,
            loading,
            error,
            pagination,
            customers,
            filters,
            displayPages,
            applyFilters,
            debouncedSearch,
            clearFilters,
            changePage,
            formatDate,
            formatNumber,
            getStatusClass,
            getStatusText,
            confirmDelete
        };
    }
};
</script>

<style scoped>
.invoice-list {
    padding: 1rem 0;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
}

.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #667eea;
}

.page-item.active .page-link {
    background-color: #ffffff;
    border-color: #002fff;
}
.page-item.active .page-link:hover {
    background-color: #1540ff;
    color: #ffffff;
}
</style>