import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    // Ruta raíz
    {
        path: '/',
        redirect: '/dashboard'
    },

    // Rutas de autenticación (públicas)
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/Login.vue'),
        meta: { requiresGuest: true, title: 'Iniciar Sesión' }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/auth/Register.vue'),
        meta: { requiresGuest: true, title: 'Registro' }
    },

    // Rutas protegidas (requieren autenticación)
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('@/views/Dashboard.vue'),
        meta: { requiresAuth: true, title: 'Dashboard' }
    },

    // Facturas
    {
        path: '/invoices',
        name: 'invoices',
        component: () => import('@/views/invoices/InvoiceList.vue'),
        meta: { requiresAuth: true, title: 'Facturas' }
    },
    {
        path: '/invoices/create',
        name: 'invoices.create',
        component: () => import('@/views/invoices/InvoiceForm.vue'),
        meta: { requiresAuth: true, title: 'Crear Factura' }
    },
    {
        path: '/invoices/:id',
        name: 'invoices.show',
        component: () => import('@/views/invoices/InvoiceDetail.vue'),
        meta: { requiresAuth: true, title: 'Detalle de Factura' }
    },
    {
        path: '/invoices/:id/edit',
        name: 'invoices.edit',
        component: () => import('@/views/invoices/InvoiceForm.vue'),
        meta: { requiresAuth: true, title: 'Editar Factura' }
    },

    // Clientes
    {
        path: '/customers',
        name: 'customers',
        component: () => import('@/views/customers/CustomerList.vue'),
        meta: { requiresAuth: true, title: 'Clientes' }
    },
    {
        path: '/customers/create',
        name: 'customers.create',
        component: () => import('@/views/customers/CustomerForm.vue'),
        meta: { requiresAuth: true, title: 'Crear Cliente' }
    },
    {
        path: '/customers/:id/edit',
        name: 'customers.edit',
        component: () => import('@/views/customers/CustomerForm.vue'),
        meta: { requiresAuth: true, title: 'Editar Cliente' }
    },

    // Usuarios (solo admin)
    {
        path: '/users',
        name: 'users',
        component: () => import('@/views/users/UserList.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Usuarios' }
    },
    {
        path: '/users/:id/edit',
        name: 'users.edit',
        component: () => import('@/views/users/UserForm.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Editar Usuario' }
    },

    // Ruta 404
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/views/NotFound.vue'),
        meta: { title: 'Página no encontrada' }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

// Guards de navegación
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    
    // Inicializar auth desde localStorage si no está cargado
    if (!authStore.user && authStore.token) {
        authStore.initializeAuth();
    }

    const isAuthenticated = authStore.isAuthenticated;
    const isAdmin = authStore.isAdmin;

    // Actualizar título de la página
    document.title = to.meta.title 
        ? `${to.meta.title} - Sistema de Facturación` 
        : 'Sistema de Facturación';

    // Verificar si la ruta requiere autenticación
    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login');
        return;
    }

    // Verificar si la ruta requiere ser invitado (no autenticado)
    if (to.meta.requiresGuest && isAuthenticated) {
        next('/dashboard');
        return;
    }

    // Verificar si la ruta requiere ser admin
    if (to.meta.requiresAdmin && !isAdmin) {
        next('/dashboard');
        return;
    }

    next();
});

export default router;