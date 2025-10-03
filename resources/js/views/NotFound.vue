<template>
    <div class="not-found-container">
        <div class="not-found-content">
            <div class="error-code">404</div>
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h2 class="error-title">Página no encontrada</h2>
            <p class="error-message">
                Lo sentimos, la página que estás buscando no existe o ha sido
                movida.
            </p>
            <div class="error-actions">
                <router-link to="/dashboard" class="btn btn-primary btn-lg">
                    <i class="bi bi-house-door me-2"></i>
                    Volver al Dashboard
                </router-link>
                <button
                    @click="goBack"
                    class="btn btn-outline-secondary btn-lg ms-3"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver Atrás
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { useRouter } from "vue-router";

export default {
    name: "NotFound",
    setup() {
        const router = useRouter();

        const goBack = () => {
            router.go(-1);
        };

        return {
            goBack,
        };
    },
};
</script>

<style scoped>
.not-found-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
}

.not-found-content {
    text-align: center;
    background: white;
    padding: 3rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 600px;
}

.error-code {
    font-size: 8rem;
    font-weight: bold;
    color: #667eea;
    line-height: 1;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

.error-icon {
    font-size: 4rem;
    color: #ffc107;
    margin-bottom: 1.5rem;
}

.error-title {
    font-size: 2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
}

.error-message {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2rem;
}

.error-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border-radius: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

@media (max-width: 576px) {
    .error-code {
        font-size: 5rem;
    }

    .error-title {
        font-size: 1.5rem;
    }

    .error-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }

    .btn.ms-3 {
        margin-left: 0 !important;
    }
}
</style>
