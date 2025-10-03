<template>
    <div id="app">
        <!-- Mostrar layout con navbar y sidebar si está autenticado -->
        <div v-if="isAuthenticated">
            <Navbar />
            <div class="container-fluid">
                <div class="row">
                    <Sidebar class="col-md-2 d-none d-md-block bg-light sidebar" />
                    <main class="col-md-10 ms-sm-auto px-md-4">
                        <router-view></router-view>
                    </main>
                </div>
            </div>
        </div>

        <!-- Mostrar solo el contenido si no está autenticado -->
        <div v-else>
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import Navbar from '@/components/Layout/Navbar.vue';
import Sidebar from '@/components/Layout/Sidebar.vue';

export default {
    name: 'App',
    components: {
        Navbar,
        Sidebar
    },
    setup() {
        const authStore = useAuthStore();

        const isAuthenticated = computed(() => authStore.isAuthenticated);

        return {
            isAuthenticated
        };
    }
};
</script>

<style scoped>
#app {
    min-height: 100vh;
    background-color: #f8f9fa;
}

.sidebar {
    position: fixed;
    top: 56px;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    overflow-y: auto;
}

main {
    margin-top: 56px;
    padding-top: 2rem;
    min-height: calc(100vh - 56px);
}

@media (max-width: 767.98px) {
    .sidebar {
        top: 56px;
    }
    
    main {
        padding-top: 1rem;
    }
}
</style>