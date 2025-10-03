<template>
    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-4">
                <i class="bi bi-receipt login-icon"></i>
                <h3 class="mt-3">Iniciar Sesión</h3>
                <p class="text-muted">Sistema de Facturación</p>
            </div>

            <!-- Alerta de error -->
            <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ error }}
                <button type="button" class="btn-close" @click="error = null"></button>
            </div>

            <form @submit.prevent="handleLogin">
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
                        placeholder="••••••••"
                        required
                        :disabled="loading"
                    />
                </div>

                <!-- Botón de login -->
                <button 
                    type="submit" 
                    class="btn btn-primary w-100 mb-3"
                    :disabled="loading"
                >
                    <span v-if="loading">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Iniciando sesión...
                    </span>
                    <span v-else>
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Ingresar
                    </span>
                </button>

                <!-- Link a registro -->
                <p class="text-center mb-0">
                    ¿No tienes cuenta? 
                    <router-link to="/register" class="text-decoration-none">
                        Regístrate aquí
                    </router-link>
                </p>
            </form>

            <!-- Credenciales de prueba -->
            <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted d-block mb-2">
                    <strong>Credenciales de prueba:</strong>
                </small>
                <small class="text-muted d-block">
                    <strong>Admin:</strong> admin@admin.com / password
                </small>
                <small class="text-muted d-block">
                    <strong>Usuario:</strong> user@user.com / password
                </small>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

export default {
    name: 'Login',
    setup() {
        const router = useRouter();
        const authStore = useAuthStore();

        const formData = reactive({
            email: '',
            password: ''
        });

        const loading = ref(false);
        const error = ref(null);

        const handleLogin = async () => {
            loading.value = true;
            error.value = null;

            try {
                await authStore.login(formData);
                router.push('/dashboard');
            } catch (err) {
                error.value = err.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.';
            } finally {
                loading.value = false;
            }
        };

        return {
            formData,
            loading,
            error,
            handleLogin
        };
    }
};
</script>

<style scoped>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem;
}

.login-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
}

.login-icon {
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
    .login-card {
        padding: 1.5rem;
    }
}
</style>