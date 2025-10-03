<template>
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <router-link 
                    to="/dashboard" 
                    class="nav-link"
                    :class="{ active: $route.path === '/dashboard' }"
                >
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </router-link>
            </li>

            <li class="nav-divider"></li>

            <!-- Facturas -->
            <li class="nav-item">
                <router-link 
                    to="/invoices" 
                    class="nav-link"
                    :class="{ active: $route.path.startsWith('/invoices') }"
                >
                    <i class="bi bi-receipt"></i>
                    Facturas
                </router-link>
            </li>

            <!-- Clientes -->
            <li class="nav-item">
                <router-link 
                    to="/customers" 
                    class="nav-link"
                    :class="{ active: $route.path.startsWith('/customers') }"
                >
                    <i class="bi bi-people"></i>
                    Clientes
                </router-link>
            </li>

            <li class="nav-divider"></li>

            <!-- Usuarios (solo admin) -->
            <li class="nav-item" v-if="isAdmin">
                <router-link 
                    to="/users" 
                    class="nav-link"
                    :class="{ active: $route.path.startsWith('/users') }"
                >
                    <i class="bi bi-person-gear"></i>
                    Usuarios
                    <span class="badge bg-warning text-dark ms-1">Admin</span>
                </router-link>
            </li>
        </ul>

        <!-- Footer del sidebar -->
        <div class="sidebar-footer mt-auto p-3">
            <small class="text-muted">
                Sistema de Facturación<br>
                v1.0.0
            </small>
        </div>
    </div>
</template>

<script>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export default {
    name: 'Sidebar',
    setup() {
        const authStore = useAuthStore();
        const isAdmin = computed(() => authStore.isAdmin);

        return {
            isAdmin
        };
    }
};
</script>

<style scoped>
.sidebar-sticky {
    position: sticky;
    top: 56px;
    height: calc(100vh - 56px);
    padding-top: 0.5rem;
    overflow-x: hidden;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
    border-radius: 0.25rem;
    margin: 0.25rem 0.5rem;
}

.nav-link i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.nav-link:hover {
    background-color: #e9ecef;
    color: #0d6efd;
}

.nav-link.active {
    background-color: #0d6efd;
    color: white;
}

.nav-link.active:hover {
    background-color: #0b5ed7;
}

.nav-divider {
    height: 1px;
    background-color: #dee2e6;
    margin: 0.5rem 1rem;
}

.sidebar-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
    margin-top: auto;
}

.badge {
    font-size: 0.65rem;
    padding: 0.25rem 0.5rem;
}
</style>