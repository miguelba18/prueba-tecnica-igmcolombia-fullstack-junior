import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Interceptor para agregar el token automáticamente
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Interceptor para manejar errores globalmente
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            // Token inválido o expirado
            if (error.response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
            
            // Error de validación
            if (error.response.status === 422) {
                console.error('Errores de validación:', error.response.data.errors);
            }
        }
        return Promise.reject(error);
    }
);

// Auth API
export const authAPI = {
    register(data) {
        return api.post('/auth/register', data);
    },
    login(data) {
        return api.post('/auth/login', data);
    },
    logout() {
        return api.post('/auth/logout');
    },
    me() {
        return api.get('/auth/me');
    }
};

// Invoices API
export const invoicesAPI = {
    list(params = {}) {
        return api.get('/invoices', { params });
    },
    get(id) {
        return api.get(`/invoices/${id}`);
    },
    create(data) {
        return api.post('/invoices', data, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
    },
    update(id, data) {
  
    if (data instanceof FormData) {
        data.append('_method', 'PUT');
        return api.post(`/invoices/${id}`, data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    }

    
    return api.put(`/invoices/${id}`, data);
}
,
    delete(id) {
        return api.delete(`/invoices/${id}`);
    },
    changeStatus(id, status) {
        return api.post(`/invoices/${id}/change-status`, { status });
    }
};

// Customers API
export const customersAPI = {
    list(params = {}) {
        return api.get('/customers', { params });
    },
    get(id) {
        return api.get(`/customers/${id}`);
    },
    create(data) {
        return api.post('/customers', data);
    },
    update(id, data) {
        return api.put(`/customers/${id}`, data);
    },
    delete(id) {
        return api.delete(`/customers/${id}`);
    }
};

// Users API
export const usersAPI = {
    list(params = {}) {
        return api.get('/users', { params });
    },
    get(id) {
        return api.get(`/users/${id}`);
    },
    update(id, data) {
        return api.put(`/users/${id}`, data);
    },
    delete(id) {
        return api.delete(`/users/${id}`);
    }
};

export default api;