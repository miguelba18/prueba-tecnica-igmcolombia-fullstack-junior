<template>
    <div class="invoice-form">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-receipt"></i> 
                {{ isEditMode ? 'Editar Factura' : 'Nueva Factura' }}
            </h2>
            <router-link to="/invoices" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Volver
            </router-link>
        </div>

        <!-- Alertas -->
        <div v-if="error" class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ error }}
            <button type="button" class="btn-close" @click="error = null"></button>
        </div>

        <form @submit.prevent="handleSubmit">
            <!-- Información General -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Cliente -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cliente *</label>
                            <select 
                                class="form-select" 
                                v-model="formData.customer_id"
                                required
                                :disabled="loading"
                            >
                                <option value="">Seleccionar cliente...</option>
                                <option 
                                    v-for="customer in customers" 
                                    :key="customer.id" 
                                    :value="customer.id"
                                >
                                    {{ customer.full_name }} - {{ customer.document_number }}
                                </option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" v-model="formData.status" :disabled="loading">
                                <option value="pending">Pendiente</option>
                                <option value="paid">Pagada</option>
                                <option value="overdue">Vencida</option>
                            </select>
                        </div>

                        <!-- Fecha de Emisión -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Emisión *</label>
                            <input 
                                type="date" 
                                class="form-control" 
                                v-model="formData.issue_date"
                                required
                                :disabled="loading"
                            />
                        </div>

                        <!-- Fecha de Vencimiento -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Vencimiento *</label>
                            <input 
                                type="date" 
                                class="form-control" 
                                v-model="formData.due_date"
                                required
                                :disabled="loading"
                            />
                        </div>

                        <!-- Descripción -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea 
                                class="form-control" 
                                rows="2"
                                v-model="formData.description"
                                placeholder="Descripción de la factura..."
                                :disabled="loading"
                            ></textarea>
                        </div>

                        <!-- Notas -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Notas Adicionales</label>
                            <textarea 
                                class="form-control" 
                                rows="2"
                                v-model="formData.notes"
                                placeholder="Notas adicionales..."
                                :disabled="loading"
                            ></textarea>
                        </div>

                        <!-- Archivo Adjunto -->
                        <div class="col-12">
                            <label class="form-label">Archivo Adjunto (PDF, JPG, PNG - Máx. 5MB)</label>
                            <input 
                                type="file" 
                                class="form-control"
                                @change="handleFileUpload"
                                accept=".pdf,.jpg,.jpeg,.png"
                                :disabled="loading"
                            />
                            <small class="text-muted">Formatos permitidos: PDF, JPG, PNG</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items de la Factura -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Items de la Factura
                    </h5>
                    <button 
                        type="button" 
                        class="btn btn-light btn-sm"
                        @click="addItem"
                        :disabled="loading"
                    >
                        <i class="bi bi-plus-circle me-1"></i>
                        Agregar Item
                    </button>
                </div>
                <div class="card-body">
                    <div v-if="formData.items.length === 0" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2">No hay items agregados</p>
                    </div>

                    <div v-for="(item, index) in formData.items" :key="index" class="item-row mb-3 p-3 border rounded">
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-3 mb-2">
                                <label class="form-label small">Nombre *</label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-sm"
                                    v-model="item.name"
                                    placeholder="Nombre del item"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <!-- Descripción -->
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Descripción</label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-sm"
                                    v-model="item.description"
                                    placeholder="Descripción"
                                    :disabled="loading"
                                />
                            </div>

                            <!-- Cantidad -->
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Cantidad *</label>
                                <input 
                                    type="number" 
                                    class="form-control form-control-sm"
                                    v-model.number="item.quantity"
                                    min="1"
                                    required
                                    @input="calculateItemTotal(item)"
                                    :disabled="loading"
                                />
                            </div>

                            <!-- Precio Unitario -->
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Precio Unit. *</label>
                                <input 
                                    type="number" 
                                    class="form-control form-control-sm"
                                    v-model.number="item.unit_price"
                                    min="0"
                                    step="0.01"
                                    required
                                    @input="calculateItemTotal(item)"
                                    :disabled="loading"
                                />
                            </div>

                            <!-- IVA -->
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">IVA (%) *</label>
                                <input 
                                    type="number" 
                                    class="form-control form-control-sm"
                                    v-model.number="item.tax_rate"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    required
                                    @input="calculateItemTotal(item)"
                                    :disabled="loading"
                                />
                            </div>

                            <!-- Botón Eliminar -->
                            <div class="col-md-1 mb-2 d-flex align-items-end">
                                <button 
                                    type="button" 
                                    class="btn btn-danger btn-sm w-100"
                                    @click="removeItem(index)"
                                    :disabled="loading"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <!-- Total del Item -->
                            <div class="col-12">
                                <div class="text-end text-muted small">
                                    Total: ${{ formatNumber(calculateItemTotal(item)) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totales -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-end">${{ formatNumber(totals.subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>IVA:</strong></td>
                                    <td class="text-end">${{ formatNumber(totals.tax) }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>TOTAL:</strong></td>
                                    <td class="text-end"><strong>${{ formatNumber(totals.total) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="d-flex justify-content-end gap-2">
                <router-link to="/invoices" class="btn btn-outline-secondary" :class="{ disabled: loading }">
                    Cancelar
                </router-link>
                <button type="submit" class="btn btn-primary" :disabled="loading || formData.items.length === 0">
                    <span v-if="loading">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Guardando...
                    </span>
                    <span v-else>
                        <i class="bi bi-save me-2"></i>
                        {{ isEditMode ? 'Actualizar' : 'Guardar' }} Factura
                    </span>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useInvoiceStore } from '@/stores/invoice';
import { useCustomerStore } from '@/stores/customer';

export default {
    name: 'InvoiceForm',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const invoiceStore = useInvoiceStore();
        const customerStore = useCustomerStore();

        const isEditMode = computed(() => !!route.params.id);
        const loading = ref(false);
        const error = ref(null);
        const customers = ref([]);

        const formData = reactive({
            customer_id: '',
            description: '',
            notes: '',
            issue_date: new Date().toISOString().split('T')[0],
            due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            status: 'pending',
            attachment: null,
            items: []
        });

        const totals = computed(() => {
            const subtotal = formData.items.reduce((sum, item) => {
                return sum + (item.quantity * item.unit_price);
            }, 0);

            const tax = formData.items.reduce((sum, item) => {
                const itemSubtotal = item.quantity * item.unit_price;
                return sum + (itemSubtotal * (item.tax_rate / 100));
            }, 0);

            return {
                subtotal,
                tax,
                total: subtotal + tax
            };
        });

        const loadCustomers = async () => {
            customers.value = await customerStore.fetchAllCustomers();
        };

        const loadInvoice = async () => {
            if (isEditMode.value) {
                loading.value = true;
                try {
                    const invoice = await invoiceStore.fetchInvoice(route.params.id);
                    formData.customer_id = invoice.customer.id;
                    formData.description = invoice.description || '';
                    formData.notes = invoice.notes || '';
                    formData.issue_date = invoice.issue_date;
                    formData.due_date = invoice.due_date;
                    formData.status = invoice.status;
                    formData.items = invoice.items.map(item => ({
                        name: item.name,
                        description: item.description || '',
                        quantity: item.quantity,
                        unit_price: parseFloat(item.unit_price),
                        tax_rate: parseFloat(item.tax_rate)
                    }));
                } catch (err) {
                    error.value = 'Error al cargar la factura';
                } finally {
                    loading.value = false;
                }
            }
        };

        const addItem = () => {
            formData.items.push({
                name: '',
                description: '',
                quantity: 1,
                unit_price: 0,
                tax_rate: 19
            });
        };

        const removeItem = (index) => {
            formData.items.splice(index, 1);
        };

        const calculateItemTotal = (item) => {
            const subtotal = item.quantity * item.unit_price;
            const tax = subtotal * (item.tax_rate / 100);
            return subtotal + tax;
        };

        const handleFileUpload = (event) => {
            const file = event.target.files[0];
            if (file) {
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    error.value = 'El archivo no puede superar los 5MB';
                    event.target.value = '';
                    return;
                }
                formData.attachment = file;
            }
        };

        const handleSubmit = async () => {
            if (formData.items.length === 0) {
                error.value = 'Debes agregar al menos un item';
                return;
            }

            loading.value = true;
            error.value = null;

            try {
                if (isEditMode.value) {
                    await invoiceStore.updateInvoice(route.params.id, formData);
                    alert('Factura actualizada exitosamente');
                } else {
                    await invoiceStore.createInvoice(formData);
                    alert('Factura creada exitosamente');
                }
                router.push('/invoices');
            } catch (err) {
                error.value = err.response?.data?.message || 'Error al guardar la factura';
            } finally {
                loading.value = false;
            }
        };

        const formatNumber = (number) => {
            return new Intl.NumberFormat('es-CO').format(number || 0);
        };

        onMounted(() => {
            loadCustomers();
            loadInvoice();
            
            // Agregar un item por defecto si es nuevo
            if (!isEditMode.value && formData.items.length === 0) {
                addItem();
            }
        });

        return {
            isEditMode,
            loading,
            error,
            customers,
            formData,
            totals,
            addItem,
            removeItem,
            calculateItemTotal,
            handleFileUpload,
            handleSubmit,
            formatNumber
        };
    }
};
</script>

<style scoped>
.invoice-form {
    padding: 1rem 0;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
}

.item-row {
    background-color: #f8f9fa;
    transition: all 0.2s;
}

.item-row:hover {
    background-color: #e9ecef;
}

.gap-2 {
    gap: 0.5rem;
}
</style>