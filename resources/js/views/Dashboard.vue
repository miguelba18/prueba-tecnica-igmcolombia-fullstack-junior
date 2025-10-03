<template>
    <div class="dashboard">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-speedometer2"></i> Dashboard
            </h2>
            <span class="text-muted">Bienvenido, {{ user?.name }}</span>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4">
            <!-- Total de Facturas -->
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-primary text-white">
                    <div class="stat-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ stats.totalInvoices }}</h3>
                        <p>Total Facturas</p>
                    </div>
                </div>
            </div>

            <!-- Facturas Pendientes -->
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-warning text-white">
                    <div class="stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ stats.pendingInvoices }}</h3>
                        <p>Pendientes</p>
                    </div>
                </div>
            </div>

            <!-- Facturas Pagadas -->
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-success text-white">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ stats.paidInvoices }}</h3>
                        <p>Pagadas</p>
                    </div>
                </div>
            </div>

            <!-- Facturas Vencidas -->
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-danger text-white">
                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>{{ stats.overdueInvoices }}</h3>
                        <p>Vencidas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-charge"></i> Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <router-link to="/invoices/create" class="btn btn-primary w-100">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Nueva Factura
                                </router-link>
                            </div>
                            <div class="col-md-4 mb-2">
                                <router-link to="/customers/create" class="btn btn-success w-100">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Nuevo Cliente
                                </router-link>
                            </div>
                            <div class="col-md-4 mb-2">
                                <router-link to="/invoices" class="btn btn-info w-100">
                                    <i class="bi bi-search me-2"></i>
                                    Ver Facturas
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Facturas Recientes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Facturas Recientes
                        </h5>
                        <router-link to="/invoices" class="btn btn-sm btn-outline-primary">
                            Ver todas
                        </router-link>
                    </div>
                    <div class="card-body">
                        <!-- Loading -->
                        <div v-if="loading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>

                        <!-- Sin facturas -->
                        <div v-else-if="recentInvoices.length === 0" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-3">No hay facturas registradas</p>
                            <router-link to="/invoices/create" class="btn btn-primary">
                                Crear primera factura
                            </router-link>
                        </div>

                        <!-- Tabla de facturas -->
                        <div v-else class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Cliente</th>
                                        <th>Fecha Emisión</th>
                                        <th>Vencimiento</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="invoice in recentInvoices" 
                                        :key="invoice.id"
                                        @click="goToInvoice(invoice.id)"
                                        style="cursor: pointer;"
                                    >
                                        <td><strong>{{ invoice.invoice_number }}</strong></td>
                                        <td>{{ invoice.customer?.full_name }}</td>
                                        <td>{{ formatDate(invoice.issue_date) }}</td>
                                        <td>{{ formatDate(invoice.due_date) }}</td>
                                        <td><strong>${{ formatNumber(invoice.total_amount) }}</strong></td>
                                        <td>
                                            <span :class="getStatusClass(invoice.status)">
                                                {{ getStatusText(invoice.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useInvoiceStore } from '@/stores/invoice';

export default {
    name: 'Dashboard',
    setup() {
        const router = useRouter();
        const authStore = useAuthStore();
        const invoiceStore = useInvoiceStore();

        const user = computed(() => authStore.user);
        const loading = ref(false);
        const recentInvoices = ref([]);

        const stats = computed(() => ({
            totalInvoices: recentInvoices.value.length,
            pendingInvoices: recentInvoices.value.filter(i => i.status === 'pending').length,
            paidInvoices: recentInvoices.value.filter(i => i.status === 'paid').length,
            overdueInvoices: recentInvoices.value.filter(i => i.status === 'overdue').length
        }));

        const loadRecentInvoices = async () => {
            loading.value = true;
            try {
                await invoiceStore.fetchInvoices(1);
                recentInvoices.value = invoiceStore.invoices.slice(0, 10);
            } catch (error) {
                console.error('Error al cargar facturas:', error);
            } finally {
                loading.value = false;
            }
        };

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

        const goToInvoice = (id) => {
            router.push(`/invoices/${id}`);
        };

        onMounted(() => {
            loadRecentInvoices();
        });

        return {
            user,
            loading,
            stats,
            recentInvoices,
            formatDate,
            formatNumber,
            getStatusClass,
            getStatusText,
            goToInvoice
        };
    }
};
</script>

<style scoped>
.dashboard {
    padding: 1rem 0;
}

.stat-card {
    border-radius: 1rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    font-size: 2.5rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.stat-content h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
}

.stat-content p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .stat-card {
        margin-bottom: 1rem;
    }
}
</style>