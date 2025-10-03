<template>
    <div class="user-form">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-person-gear"></i> Editar Usuario
                <span class="badge bg-warning text-dark ms-2">Admin</span>
            </h2>
            <router-link to="/users" class="btn btn-outline-secondary">
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

        <div v-if="success" class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ success }}
            <button type="button" class="btn-close" @click="success = null"></button>
        </div>

        <!-- Loading -->
        <div v-if="initialLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando usuario...</p>
        </div>

        <div v-else class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            Información del Usuario
                        </h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="handleSubmit">
                            <div class="row">
                                <!-- Nombre -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Nombre Completo *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="formData.name"
                                        placeholder="Nombre completo"
                                        required
                                        :disabled="loading"
                                    />
                                </div>

                                <!-- Email -->
                                <div class="col-12 mb-3">
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

                                <!-- Rol -->
                                <div class="col-12 mb-3">
                                    <label class="form-label">Rol *</label>
                                    <select 
                                        class="form-select" 
                                        v-model="formData.role"
                                        required
                                        :disabled="loading || isCurrentUser"
                                    >
                                        <option value="">Seleccionar rol...</option>
                                        <option value="admin">Administrador</option>
                                        <option value="user">Usuario</option>
                                    </select>
                                    <small class="text-muted" v-if="isCurrentUser">
                                        No puedes cambiar tu propio rol
                                    </small>
                                </div>

                                <hr class="my-4">

                                <div class="col-12 mb-3">
                                    <h6 class="text-muted">
                                        <i class="bi bi-shield-lock me-2"></i>
                                        Cambiar Contraseña (opcional)
                                    </h6>
                                    <small class="text-muted">
                                        Deja estos campos vacíos si no deseas cambiar la contraseña
                                    </small>
                                </div>

                                <!-- Nueva Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nueva Contraseña</label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        v-model="formData.password"
                                        placeholder="Mínimo 8 caracteres"
                                        minlength="8"
                                        :disabled="loading"
                                    />
                                    <small class="text-muted">
                                        Opcional - Mínimo 8 caracteres
                                    </small>
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirmar Contraseña</label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        v-model="formData.password_confirmation"
                                        placeholder="Repite la contraseña"
                                        :disabled="loading || !formData.password"
                                    />
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <router-link 
                                    to="/users" 
                                    class="btn btn-outline-secondary"
                                    :class="{ disabled: loading }"
                                >
                                    Cancelar
                                </router-link>
                                <button 
                                    type="submit" 
                                    class="btn btn-primary"
                                    :disabled="loading"
                                >
                                    <span v-if="loading">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Guardando...
                                    </span>
                                    <span v-else>
                                        <i class="bi bi-save me-2"></i>
                                        Actualizar Usuario
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
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useUserStore } from '@/stores/user';
import { useAuthStore } from '@/stores/auth';

export default {
    name: 'UserForm',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const userStore = useUserStore();
        const authStore = useAuthStore();

        const initialLoading = ref(false);
        const loading = ref(false);
        const error = ref(null);
        const success = ref(null);

        const formData = reactive({
            name: '',
            email: '',
            role: '',
            password: '',
            password_confirmation: ''
        });

        const isCurrentUser = computed(() => {
            return authStore.user?.id === parseInt(route.params.id);
        });

        const loadUser = async () => {
            initialLoading.value = true;
            try {
                const user = await userStore.fetchUser(route.params.id);
                formData.name = user.name;
                formData.email = user.email;
                formData.role = user.roles?.[0] || '';
            } catch (err) {
                error.value = 'Error al cargar el usuario';
            } finally {
                initialLoading.value = false;
            }
        };

        const handleSubmit = async () => {
            // Validar contraseñas si se ingresaron
            if (formData.password) {
                if (formData.password.length < 8) {
                    error.value = 'La contraseña debe tener al menos 8 caracteres';
                    return;
                }
                if (formData.password !== formData.password_confirmation) {
                    error.value = 'Las contraseñas no coinciden';
                    return;
                }
            }

            loading.value = true;
            error.value = null;
            success.value = null;

            try {
                const dataToSend = {
                    name: formData.name,
                    email: formData.email,
                    role: formData.role
                };

                // Solo incluir contraseña si se ingresó
                if (formData.password) {
                    dataToSend.password = formData.password;
                    dataToSend.password_confirmation = formData.password_confirmation;
                }

                await userStore.updateUser(route.params.id, dataToSend);
                success.value = 'Usuario actualizado exitosamente';

                // Si el usuario editó su propia información, actualizar el estado de auth
                if (isCurrentUser.value) {
                    await authStore.fetchUser();
                }

                // Redirigir después de 1.5 segundos
                setTimeout(() => {
                    router.push('/users');
                }, 1500);
            } catch (err) {
                error.value = userStore.error || 'Error al actualizar el usuario';
            } finally {
                loading.value = false;
            }
        };

        onMounted(() => {
            loadUser();
        });

        return {
            initialLoading,
            loading,
            error,
            success,
            formData,
            isCurrentUser,
            handleSubmit
        };
    }
};
</script>

<style scoped>
.user-form {
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.gap-2 {
    gap: 0.5rem;
}
</style>