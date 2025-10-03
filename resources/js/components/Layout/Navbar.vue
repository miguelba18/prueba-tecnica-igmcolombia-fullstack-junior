<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <router-link to="/dashboard" class="navbar-brand">
                <i class="bi bi-receipt"></i> Sistema de Facturación
            </router-link>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Usuario actual -->
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="userDropdown"
                            role="button"
                            data-bs-toggle="dropdown"
                        >
                            <i class="bi bi-person-circle"></i>
                            {{ user?.name || "Usuario" }}
                            <span
                                v-if="isAdmin"
                                class="badge bg-warning text-dark ms-1"
                            >
                                Admin
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text">
                                    <small class="text-muted">{{
                                        user?.email
                                    }}</small>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                                <a
                                    class="dropdown-item text-danger"
                                    href="#"
                                    @click.prevent="handleLogout"
                                >
                                    <i class="bi bi-box-arrow-right"></i>
                                    Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import Swal from "sweetalert2";

export default {
    name: "Navbar",
    setup() {
        const router = useRouter();
        const authStore = useAuthStore();

        const user = computed(() => authStore.user);
        const isAdmin = computed(() => authStore.isAdmin);

        const handleLogout = async () => {
            const result = await Swal.fire({
                title: "Cerrar sesión",
                text: "¿Estás seguro de que deseas cerrar sesión?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, cerrar sesión",
                cancelButtonText: "Cancelar",
            });

            if (result.isConfirmed) {
                await authStore.logout();
                await Swal.fire(
                    "Sesión cerrada",
                    "Has cerrado sesión correctamente.",
                    "success"
                );
                router.push("/login");
            }
        };

        return {
            user,
            isAdmin,
            handleLogout,
        };
    },
};
</script>

<style scoped>
.navbar {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
    font-weight: 600;
}

.nav-link {
    font-weight: 500;
}

.dropdown-item-text {
    padding: 0.25rem 1rem;
}
</style>
