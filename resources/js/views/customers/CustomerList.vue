<template>
    <div class="customer-list">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-people"></i> Clientes
            </h2>
            <router-link to="/customers/create" class="btn btn-success">
                <i class="bi bi-person-plus me-2"></i>
                Nuevo Cliente
            </router-link>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Búsqueda -->
                    <div class="col-md-6">
                        <label class="form-label">Buscar</label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Nombre, email o documento..."
                            v-model="filters.search"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Tipo de Documento -->
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Documento</label>
                        <select class="form-select" v-model="filters.document_type" @change="applyFilters">
                            <option value="">Todos</option>
                            <option value="CC">Cédula de Ciudadanía (CC)</option>
                            <option value="NIT">NIT</option>
                            <option value="CE">Cédula de Extranjería (CE)</option>
                            <option value="PA">Pasaporte (PA)</option>
                        </select>
                    </div>

                    <!-- Limpiar -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button @click="clearFilters" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de clientes -->
        <div class="card">
            <div class="card-body">
                <!-- Loading -->
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando clientes...</p>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ error }}
                </div>

                <!-- Sin resultados -->
                <div v-else-if="customers.length === 0" class="text-center py-5 text-muted">
                    <i class="bi bi-person-x" style="font-size: 3rem;"></i>
                    <p class="mt-3">No se encontraron clientes</p>
                    <router-link to="/customers/create" class="btn btn-success">
                        Crear primer cliente
                    </router-link>
                </div>

                <!-- Tabla -->
                <div v-else>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th>Tipo Doc.</th>
                                    <th>Documento</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="customer in customers" :key="customer.id">
                                    <td>
                                        <strong>{{ customer.full_name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ customer.document_type }}</span>
                                    </td>
                                    <td>{{ customer.document_number }}</td>
                                    <td>
                                        <a :href="`mailto:${customer.email}`">{{ customer.email }}</a>
                                    </td>
                                    <td>{{ customer.phone || '-' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <router-link
                                                :to="`/customers/${customer.id}/edit`"
                                                class="btn btn-outline-warning"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil">Editar</i>
                                            </router-link>
                                            <button
                                                @click="confirmDelete(customer)"
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
                            Mostrando {{ customers.length }} de {{ pagination.total }} clientes
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
import { useCustomerStore } from '@/stores/customer';

export default {
    name: 'CustomerList',
    setup() {
        const customerStore = useCustomerStore();

        const customers = computed(() => customerStore.customers);
        const loading = computed(() => customerStore.loading);
        const error = computed(() => customerStore.error);
        const pagination = computed(() => customerStore.pagination);

        const filters = reactive({
            search: '',
            document_type: ''
        });

        let searchTimeout = null;

        const loadCustomers = async (page = 1) => {
            await customerStore.fetchCustomers(page);
        };

        const applyFilters = () => {
            customerStore.setFilters(filters);
            loadCustomers(1);
        };

        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        };

        const clearFilters = () => {
            filters.search = '';
            filters.document_type = '';
            customerStore.clearFilters();
            loadCustomers(1);
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.lastPage) {
                loadCustomers(page);
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

        const confirmDelete = async (customer) => {
            if (confirm(`¿Estás seguro de eliminar al cliente ${customer.full_name}?\n\nNota: No se puede eliminar si tiene facturas asociadas.`)) {
                try {
                    await customerStore.deleteCustomer(customer.id);
                    alert('Cliente eliminado exitosamente');
                } catch (error) {
                    alert(customerStore.error || 'Error al eliminar el cliente');
                }
            }
        };

        onMounted(() => {
            loadCustomers();
        });

        return {
            customers,
            loading,
            error,
            pagination,
            filters,
            displayPages,
            applyFilters,
            debouncedSearch,
            clearFilters,
            changePage,
            confirmDelete
        };
    }
};
</script>

<style scoped>
.customer-list {
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
    color: #198754;
}

.page-item.active .page-link {
    background-color: #ffffff;
    border-color: #198754;
}
.page-item.active .page-link:hover {
    background-color: #198754;
    color: #ffffff;
}
</style>