<template>
    <div class="customer-form">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-person"></i>
                {{ isEditMode ? "Editar Cliente" : "Nuevo Cliente" }}
            </h2>
            <router-link to="/customers" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Volver
            </router-link>
        </div>

        <!-- Alertas -->
        <div
            v-if="error"
            class="alert alert-danger alert-dismissible fade show"
        >
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ error }}
            <button
                type="button"
                class="btn-close"
                @click="error = null"
            ></button>
        </div>

        <div
            v-if="success"
            class="alert alert-success alert-dismissible fade show"
        >
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ success }}
            <button
                type="button"
                class="btn-close"
                @click="success = null"
            ></button>
        </div>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Información del Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="handleSubmit">
                            <div class="row">
                                <!-- Nombre -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="formData.first_name"
                                        placeholder="Nombre"
                                        required
                                        :disabled="loading"
                                    />
                                </div>

                                <!-- Apellido -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Apellido *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="formData.last_name"
                                        placeholder="Apellido"
                                        required
                                        :disabled="loading"
                                    />
                                </div>

                                <!-- Tipo de Documento -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Tipo de Documento *</label
                                    >
                                    <select
                                        class="form-select"
                                        v-model="formData.document_type"
                                        required
                                        :disabled="loading"
                                    >
                                        <option value="">Seleccionar...</option>
                                        <option value="CC">
                                            Cédula de Ciudadanía (CC)
                                        </option>
                                        <option value="NIT">NIT</option>
                                        <option value="CE">
                                            Cédula de Extranjería (CE)
                                        </option>
                                        <option value="PA">
                                            Pasaporte (PA)
                                        </option>
                                    </select>
                                </div>

                                <!-- Número de Documento -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"
                                        >Número de Documento *</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="formData.document_number"
                                        placeholder="1234567890"
                                        required
                                        :disabled="loading"
                                    />
                                    <small class="text-muted">
                                        Sin puntos ni guiones
                                    </small>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email *</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        v-model="formData.email"
                                        placeholder="correo@ejemplo.com"
                                        required
                                        :disabled="loading"
                                    />
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input
                                        type="tel"
                                        class="form-control"
                                        v-model="formData.phone"
                                        placeholder="3001234567"
                                        :disabled="loading"
                                    />
                                    <small class="text-muted">
                                        Opcional - 10 dígitos
                                    </small>
                                </div>

                                <!-- Dirección -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Dirección</label>
                                    <textarea
                                        class="form-control"
                                        rows="3"
                                        v-model="formData.address"
                                        placeholder="Dirección completa..."
                                        :disabled="loading"
                                    ></textarea>
                                    <small class="text-muted"> Opcional </small>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <router-link
                                    to="/customers"
                                    class="btn btn-outline-secondary"
                                    :class="{ disabled: loading }"
                                >
                                    Cancelar
                                </router-link>
                                <button
                                    type="submit"
                                    class="btn btn-success"
                                    :disabled="loading"
                                >
                                    <span v-if="loading">
                                        <span
                                            class="spinner-border spinner-border-sm me-2"
                                        ></span>
                                        Guardando...
                                    </span>
                                    <span v-else>
                                        <i class="bi bi-save me-2"></i>
                                        {{
                                            isEditMode
                                                ? "Actualizar"
                                                : "Guardar"
                                        }}
                                        Cliente
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useCustomerStore } from "@/stores/customer";
import Swal from "sweetalert2";

export default {
    name: "CustomerForm",
    setup() {
        const route = useRoute();
        const router = useRouter();
        const customerStore = useCustomerStore();

        const isEditMode = computed(() => !!route.params.id);
        const loading = ref(false);
        const error = ref(null);
        const success = ref(null);

        const formData = reactive({
            first_name: "",
            last_name: "",
            document_type: "",
            document_number: "",
            email: "",
            phone: "",
            address: "",
        });

        const loadCustomer = async () => {
            if (isEditMode.value) {
                loading.value = true;
                try {
                    const customer = await customerStore.fetchCustomer(
                        route.params.id
                    );
                    formData.first_name = customer.first_name;
                    formData.last_name = customer.last_name;
                    formData.document_type = customer.document_type;
                    formData.document_number = customer.document_number;
                    formData.email = customer.email;
                    formData.phone = customer.phone || "";
                    formData.address = customer.address || "";
                } catch (err) {
                    error.value = "Error al cargar el cliente";
                } finally {
                    loading.value = false;
                }
            }
        };

        const handleSubmit = async () => {
            loading.value = true;
            error.value = null;
            success.value = null;

            try {
                if (isEditMode.value) {
                    await customerStore.updateCustomer(
                        route.params.id,
                        formData
                    );
                    Swal.fire(
                        "✅ Actualizada",
                        "El cliente fue actualizado exitosamente",
                        "success"
                    );
                } else {
                    await customerStore.createCustomer(formData);
                    Swal.fire(
                        "✅ Actualizada",
                        "El cliente fue creado exitosamente",
                        "success"
                    );
                }

                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    router.push("/customers");
                }, 1500);
            } catch (err) {
                error.value =
                    customerStore.error || "Error al guardar el cliente";
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            loadCustomer();
        });

        return {
            isEditMode,
            loading,
            error,
            success,
            formData,
            handleSubmit,
        };
    },
};
</script>

<style scoped>
.customer-form {
    padding: 1rem 0;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control,
.form-select {
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.gap-2 {
    gap: 0.5rem;
}
</style>
