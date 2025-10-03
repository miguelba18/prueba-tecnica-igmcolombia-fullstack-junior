<template>
    <div class="user-list">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="bi bi-person-gear"></i> Usuarios
                <span class="badge bg-warning text-dark ms-2">Admin</span>
            </h2>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Búsqueda -->
                    <div class="col-md-6">
                        <label class="form-label">Buscar</label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Nombre o email..."
                            v-model="filters.search"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Rol -->
                    <div class="col-md-4">
                        <label class="form-label">Rol</label>
                        <select
                            class="form-select"
                            v-model="filters.role"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="admin">Administrador</option>
                            <option value="user">Usuario</option>
                        </select>
                    </div>

                    <!-- Limpiar -->
                    <div class="col-md-2 d-flex align-items-end">
                        <button
                            @click="clearFilters"
                            class="btn btn-outline-secondary w-100"
                        >
                            <i class="bi bi-x-circle me-1"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card">
            <div class="card-body">
                <!-- Loading -->
                <div v-if="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando usuarios...</p>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ error }}
                </div>

                <!-- Sin resultados -->
                <div
                    v-else-if="users.length === 0"
                    class="text-center py-5 text-muted"
                >
                    <i class="bi bi-person-x" style="font-size: 3rem"></i>
                    <p class="mt-3">No se encontraron usuarios</p>
                </div>

                <!-- Tabla -->
                <div v-else>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Fecha de Registro</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id">
                                    <td>{{ user.id }}</td>
                                    <td>
                                        <strong>{{ user.name }}</strong>
                                    </td>
                                    <td>
                                        <a :href="`mailto:${user.email}`">{{
                                            user.email
                                        }}</a>
                                    </td>
                                    <td>
                                        <span
                                            v-for="role in user.roles"
                                            :key="role"
                                            :class="getRoleClass(role)"
                                            class="badge me-1"
                                        >
                                            {{ getRoleText(role) }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate(user.created_at) }}</td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <router-link
                                                :to="`/users/${user.id}/edit`"
                                                class="btn btn-outline-warning"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil"
                                                    >Editar</i
                                                >
                                            </router-link>
                                            <button
                                                @click="confirmDelete(user)"
                                                class="btn btn-outline-danger"
                                                title="Eliminar"
                                                :disabled="
                                                    user.id === currentUserId
                                                "
                                            >
                                                <i class="bi bi-trash"
                                                    >Eliminar</i
                                                >
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        class="d-flex justify-content-between align-items-center mt-3"
                    >
                        <div class="text-muted">
                            Mostrando {{ users.length }} de
                            {{ pagination.total }} usuarios
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li
                                    class="page-item"
                                    :class="{
                                        disabled: pagination.currentPage === 1,
                                    }"
                                >
                                    <a
                                        class="page-link"
                                        href="#"
                                        @click.prevent="
                                            changePage(
                                                pagination.currentPage - 1
                                            )
                                        "
                                    >
                                        Anterior
                                    </a>
                                </li>
                                <li
                                    v-for="page in displayPages"
                                    :key="page"
                                    class="page-item"
                                    :class="{
                                        active: page === pagination.currentPage,
                                    }"
                                >
                                    <a
                                        class="page-link"
                                        href="#"
                                        @click.prevent="changePage(page)"
                                    >
                                        {{ page }}
                                    </a>
                                </li>
                                <li
                                    class="page-item"
                                    :class="{
                                        disabled:
                                            pagination.currentPage ===
                                            pagination.lastPage,
                                    }"
                                >
                                    <a
                                        class="page-link"
                                        href="#"
                                        @click.prevent="
                                            changePage(
                                                pagination.currentPage + 1
                                            )
                                        "
                                    >
                                        Siguiente
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { computed, onMounted, reactive } from "vue";
import { useUserStore } from "@/stores/user";
import { useAuthStore } from "@/stores/auth";
import Swal from "sweetalert2";

export default {
    name: "UserList",
    setup() {
        const userStore = useUserStore();
        const authStore = useAuthStore();

        const users = computed(() => userStore.users);
        const loading = computed(() => userStore.loading);
        const error = computed(() => userStore.error);
        const pagination = computed(() => userStore.pagination);
        const currentUserId = computed(() => authStore.user?.id);

        const filters = reactive({
            search: "",
            role: "",
        });

        let searchTimeout = null;

        const loadUsers = async (page = 1) => {
            await userStore.fetchUsers(page);
        };

        const applyFilters = () => {
            userStore.setFilters(filters);
            loadUsers(1);
        };

        const debouncedSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        };

        const clearFilters = () => {
            filters.search = "";
            filters.role = "";
            userStore.clearFilters();
            loadUsers(1);
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.lastPage) {
                loadUsers(page);
                window.scrollTo({ top: 0, behavior: "smooth" });
            }
        };

        const displayPages = computed(() => {
            const current = pagination.value.currentPage;
            const last = pagination.value.lastPage;
            const pages = [];

            if (last <= 7) {
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 4) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                    pages.push("...");
                    pages.push(last);
                } else if (current >= last - 3) {
                    pages.push(1);
                    pages.push("...");
                    for (let i = last - 4; i <= last; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push("...");
                    for (let i = current - 1; i <= current + 1; i++)
                        pages.push(i);
                    pages.push("...");
                    pages.push(last);
                }
            }

            return pages;
        });

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString("es-CO", {
                year: "numeric",
                month: "long",
                day: "numeric",
            });
        };

        const getRoleClass = (role) => {
            return role === "admin" ? "bg-warning text-dark" : "bg-info";
        };

        const getRoleText = (role) => {
            return role === "admin" ? "Administrador" : "Usuario";
        };

        const confirmDelete = async (user) => {
            if (user.id === currentUserId.value) {
                Swal.fire(
                    "Acción no permitida",
                    "No puedes eliminar tu propio usuario.",
                    "warning"
                );
                return;
            }

            const result = await Swal.fire({
                title: "¿Estás seguro?",
                text: `Se eliminará al usuario ${user.name}. Esta acción no se puede deshacer.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            });

            if (result.isConfirmed) {
                try {
                    await userStore.deleteUser(user.id);
                    await Swal.fire(
                        "Eliminado",
                        `El usuario ${user.name} fue eliminado exitosamente.`,
                        "success"
                    );
                } catch (error) {
                    Swal.fire(
                        "Error",
                        userStore.error ||
                            "Hubo un problema al eliminar el usuario.",
                        "error"
                    );
                }
            }
        };

        onMounted(() => {
            loadUsers();
        });

        return {
            users,
            loading,
            error,
            pagination,
            currentUserId,
            filters,
            displayPages,
            applyFilters,
            debouncedSearch,
            clearFilters,
            changePage,
            formatDate,
            getRoleClass,
            getRoleText,
            confirmDelete,
        };
    },
};
</script>

<style scoped>
.user-list {
    padding: 1rem 0;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
}

.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #667eea;
}

.page-item.active .page-link {
    background-color: #ffffff;
    border-color: #667eea;
}
</style>
