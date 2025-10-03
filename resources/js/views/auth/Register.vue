<template>
    <div class="register-container">
        <div class="register-card">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus register-icon"></i>
                <h3 class="mt-3">Crear Cuenta</h3>
                <p class="text-muted">Sistema de Facturación</p>
            </div>

            <!-- Alerta de error -->
            <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ error }}
                <button type="button" class="btn-close" @click="error = null"></button>
            </div>

            <!-- Alerta de éxito -->
            <div v-if="success" class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ success }}
                <button type="button" class="btn-close" @click="success = null"></button>
            </div>

            <form @submit.prevent="handleRegister">
                <!-- Nombre -->
                <div class="mb-3">
                    <label for="name" class="form-label">
                        <i class="bi bi-person"></i> Nombre Completo
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="name"
                        v-model="formData.name"
                        placeholder="Tu nombre completo"
                        required
                        :disabled="loading"
                    />
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i> Email
                    </label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        v-model="formData.email"
                        placeholder="correo@ejemplo.com"
                        required
                        :disabled="loading"
                    />
                </div>

                <!-- Contraseña -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> Contraseña
                    </label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        v-model="formData.password"
                        placeholder="Mínimo 8 caracteres"
                        required
                        minlength="8"
                        :disabled="loading"
                    />
                    <small class="text-muted">Mínimo 8 caracteres</small>
                </div>

                <!-- Confirmar Contraseña -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">
                        <i class="bi bi-lock-fill"></i> Confirmar Contraseña
                    </label>
                    <input
                        type="password"
                        class="form-control"
                        id="password_confirmation"
                        v-model="formData.password_confirmation"
                        placeholder="Repite tu contraseña"
                        required
                        :disabled="loading"
                    />
                </div>

                <!-- Botón de registro -->
                <button 
                    type="submit" 
                    class="btn btn-primary w-100 mb-3"
                    :disabled="loading"
                >
                    <span v-if="loading">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Registrando...
                    </span>
                    <span v-else>
                        <i class="bi bi-person-check me-2"></i>
                        Crear Cuenta
                    </span>
                </button>

                <!-- Link a login -->
                <p class="text-center mb-0">
                    ¿Ya tienes cuenta? 
                    <router-link to="/login" class="text-decoration-none">
                        Inicia sesión aquí
                    </router-link>
                </p>
            </form>
        </div>
    </div>
</template>

<script>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export default {
    name: 'Register',
    setup() {
        const router = useRouter();
        const authStore = useAuthStore();

        const formData = reactive({
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        });

        const loading = ref(false);
        const error = ref(null);
        const success = ref(null);

        const handleRegister = async () => {
            // Validar que las contraseñas coincidan
            if (formData.password !== formData.password_confirmation) {
                error.value = 'Las contraseñas no coinciden';
                return;
            }

            loading.value = true;
            error.value = null;
            success.value = null;

            try {
                await authStore.register(formData);
                success.value = 'Cuenta creada exitosamente. Redirigiendo...';
                
                // Redirigir al dashboard después de 1.5 segundos
                setTimeout(() => {
                    router.push('/dashboard');
                }, 1500);
            } catch (err) {
                if (err.response?.data?.errors) {
                    // Errores de validación del backend
                    const errors = err.response.data.errors;
                    const errorMessages = Object.values(errors).flat().join(' ');
                    error.value = errorMessages;
                } else {
                    error.value = err.response?.data?.message || 'Error al crear la cuenta. Intenta nuevamente.';
                }
            } finally {
                loading.value = false;
            }
        };

        return {
            formData,
            loading,
            error,
            success,
            handleRegister
        };
    }
};
</script>

<style scoped>
.register-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem;
}

.register-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    padding: 2.5rem;
    width: 100%;
    max-width: 450px;
}

.register-icon {
    font-size: 3rem;
    color: #667eea;
}

.form-label {
    font-weight: 500;
    color: #495057;
}

.form-control {
    padding: 0.75rem;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    padding: 0.75rem;
    font-weight: 600;
    border-radius: 0.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

@media (max-width: 576px) {
    .register-card {
        padding: 1.5rem;
    }
}
</style>