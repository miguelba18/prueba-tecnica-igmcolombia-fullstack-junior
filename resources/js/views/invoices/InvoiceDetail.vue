<template>
    <div class="invoice-detail">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando factura...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ error }}
        </div>

        <!-- Contenido de la factura -->
        <div v-else-if="invoice">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="bi bi-receipt"></i> 
                    Factura {{ invoice.invoice_number }}
                </h2>
                <div class="btn-group">
                    <router-link to="/invoices" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver
                    </router-link>
                    <router-link 
                        :to="`/invoices/${invoice.id}/edit`" 
                        class="btn btn-warning"
                    >
                        <i class="bi bi-pencil me-2"></i>
                        Editar
                    </router-link>
                    <button 
                        @click="confirmDelete" 
                        class="btn btn-danger"
                    >
                        <i class="bi bi-trash me-2"></i>
                        Eliminar
                    </button>
                </div>
            </div>

            <!-- Estado y acciones rápidas -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5>
                                Estado Actual:
                                <span :class="getStatusClass(invoice.status)" class="ms-2">
                                    {{ getStatusText(invoice.status) }}
                                </span>
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group">
                                <button 
                                    @click="changeStatus('pending')"
                                    class="btn btn-sm btn-warning"
                                    :disabled="invoice.status === 'pending' || changingStatus"
                                >
                                    Marcar Pendiente
                                </button>
                                <button 
                                    @click="changeStatus('paid')"
                                    class="btn btn-sm btn-success"
                                    :disabled="invoice.status === 'paid' || changingStatus"
                                >
                                    Marcar Pagada
                                </button>
                                <button 
                                    @click="changeStatus('overdue')"
                                    class="btn btn-sm btn-danger"
                                    :disabled="invoice.status === 'overdue' || changingStatus"
                                >
                                    Marcar Vencida
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Información General -->
                <div class="col-md-8">
                    <!-- Datos de la Factura -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Información de la Factura
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Número de Factura:</strong><br>
                                    <span class="text-primary fs-5">{{ invoice.invoice_number }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Estado:</strong><br>
                                    <span :class="getStatusClass(invoice.status)">
                                        {{ getStatusText(invoice.status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Fecha de Emisión:</strong><br>
                                    {{ formatDate(invoice.issue_date) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Fecha de Vencimiento:</strong><br>
                                    {{ formatDate(invoice.due_date) }}
                                    <span v-if="invoice.is_overdue" class="badge bg-danger ms-2">
                                        Vencida
                                    </span>
                                </div>
                            </div>

                            <div class="row mb-3" v-if="invoice.description">
                                <div class="col-12">
                                    <strong>Descripción:</strong><br>
                                    {{ invoice.description }}
                                </div>
                            </div>

                            <div class="row" v-if="invoice.notes">
                                <div class="col-12">
                                    <strong>Notas:</strong><br>
                                    {{ invoice.notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Cliente -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>
                                Información del Cliente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <strong>Nombre:</strong><br>
                                    {{ invoice.customer.full_name }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Documento:</strong><br>
                                    {{ invoice.customer.document_type }} - {{ invoice.customer.document_number }}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <strong>Email:</strong><br>
                                    <a :href="`mailto:${invoice.customer.email}`">{{ invoice.customer.email }}</a>
                                </div>
                                <div class="col-md-6" v-if="invoice.customer.phone">
                                    <strong>Teléfono:</strong><br>
                                    {{ invoice.customer.phone }}
                                </div>
                            </div>
                            <div class="row" v-if="invoice.customer.address">
                                <div class="col-12">
                                    <strong>Dirección:</strong><br>
                                    {{ invoice.customer.address }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items de la Factura -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul me-2"></i>
                                Items de la Factura
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Descripción</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio Unit.</th>
                                            <th class="text-center">IVA</th>
                                            <th class="text-end">Subtotal</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in invoice.items" :key="item.id">
                                            <td>
                                                <strong>{{ item.name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ item.description }}</small>
                                            </td>
                                            <td class="text-center">{{ item.quantity }}</td>
                                            <td class="text-end">${{ formatNumber(item.unit_price) }}</td>
                                            <td class="text-center">{{ item.tax_rate }}%</td>
                                            <td class="text-end">${{ formatNumber(item.subtotal) }}</td>
                                            <td class="text-end"><strong>${{ formatNumber(item.total) }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Totales -->
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-calculator me-2"></i>
                                Totales
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-end">${{ formatNumber(invoice.subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>IVA:</strong></td>
                                    <td class="text-end">${{ formatNumber(invoice.tax_amount) }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>TOTAL:</strong></td>
                                    <td class="text-end">
                                        <strong class="fs-5">${{ formatNumber(invoice.total_amount) }}</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Archivo Adjunto -->
                    <div class="card mb-4" v-if="invoice.attachment_path">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-paperclip me-2"></i>
                                Archivo Adjunto
                            </h6>
                        </div>
                        <div class="card-body">
                            <a 
                                :href="invoice.attachment_path" 
                                target="_blank" 
                                class="btn btn-outline-primary w-100"
                            >
                                <i class="bi bi-download me-2"></i>
                                Descargar Archivo
                            </a>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Información Adicional
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Creado por:</strong><br>
                                {{ invoice.user?.name }}
                            </p>
                            <p class="mb-2">
                                <strong>Fecha de creación:</strong><br>
                                {{ formatDateTime(invoice.created_at) }}
                            </p>
                            <p class="mb-0">
                                <strong>Última actualización:</strong><br>
                                {{ formatDateTime(invoice.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useInvoiceStore } from '@/stores/invoice';

export default {
    name: 'InvoiceDetail',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const invoiceStore = useInvoiceStore();

        const invoice = ref(null);
        const loading = ref(false);
        const error = ref(null);
        const changingStatus = ref(false);

        const loadInvoice = async () => {
            loading.value = true;
            error.value = null;

            try {
                invoice.value = await invoiceStore.fetchInvoice(route.params.id);
            } catch (err) {
                error.value = 'Error al cargar la factura';
            } finally {
                loading.value = false;
            }
        };

        const changeStatus = async (newStatus) => {
            if (confirm(`¿Estás seguro de cambiar el estado a ${getStatusText(newStatus)}?`)) {
                changingStatus.value = true;
                try {
                    await invoiceStore.changeInvoiceStatus(invoice.value.id, newStatus);
                    invoice.value.status = newStatus;
                    alert('Estado actualizado exitosamente');
                } catch (err) {
                    alert('Error al cambiar el estado');
                } finally {
                    changingStatus.value = false;
                }
            }
        };

        const confirmDelete = async () => {
            if (confirm('¿Estás seguro de eliminar esta factura? Esta acción no se puede deshacer.')) {
                try {
                    await invoiceStore.deleteInvoice(invoice.value.id);
                    alert('Factura eliminada exitosamente');
                    router.push('/invoices');
                } catch (err) {
                    alert('Error al eliminar la factura');
                }
            }
        };

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString('es-CO', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        };

        const formatDateTime = (datetime) => {
            return new Date(datetime).toLocaleString('es-CO', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        const formatNumber = (number) => {
            return new Intl.NumberFormat('es-CO').format(number || 0);
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

        onMounted(() => {
            loadInvoice();
        });

        return {
            invoice,
            loading,
            error,
            changingStatus,
            changeStatus,
            confirmDelete,
            formatDate,
            formatDateTime,
            formatNumber,
            getStatusClass,
            getStatusText
        };
    }
};
</script>

<style scoped>
.invoice-detail {
    padding: 1rem 0;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.btn-group .btn {
    margin-right: 0;
}
</style>