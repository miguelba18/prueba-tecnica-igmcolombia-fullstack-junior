# Sistema de Facturación - IGM Colombia

Sistema de gestión de facturas desarrollado con **Laravel 11** y **Vue 3**.

## 🚀 Stack Tecnológico

**Backend:** Laravel 11, PostgreSQL, Sanctum, Spatie Permission  
**Frontend:** Vue 3, Pinia, Vue Router, Bootstrap 5  
**Base de Datos:** PostgreSQL (Railway)

## 📋 Requisitos

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- PostgreSQL 12+

## ⚡ Instalación Rápida

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-usuario/prueba-tecnica-igmcolombia-fullstack-junior.git
cd prueba-tecnica-igmcolombia-fullstack-junior

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=invoicing_system
DB_USERNAME=postgres
DB_PASSWORD=tu_password

# 5. Ejecutar migraciones y seeders
php artisan migrate --seed

# 6. Crear enlace de storage
php artisan storage:link

# 7. Iniciar servidores
php artisan serve          # Terminal 1
npm run dev                # Terminal 2
```

Accede a: `http://localhost:8000`

## 👤 Credenciales de Prueba

**Administrador:**
- Email: `admin@admin.com`
- Password: `password`

**Usuario:**
- Email: `user@user.com`
- Password: `password`

## 🗂️ Estructura del Proyecto

```
app/
├── Console/Commands/        # Comandos Artisan
├── Http/
│   ├── Controllers/Api/     # Controladores API
│   ├── Requests/            # Validaciones
│   └── Resources/           # Transformadores JSON
├── Models/                  # Modelos Eloquent
└── Services/                # Lógica de negocio

database/
├── migrations/              # Estructura de BD
└── seeders/                 # Datos de prueba

resources/js/
├── views/                   # Páginas Vue
├── components/              # Componentes
├── stores/                  # Pinia stores
└── router/                  # Vue Router
```

## 🔌 API Endpoints Principales

### Autenticación
- `POST /api/auth/register` - Registrar usuario
- `POST /api/auth/login` - Iniciar sesión
- `POST /api/auth/logout` - Cerrar sesión
- `GET /api/auth/me` - Usuario actual

### Facturas (requiere autenticación)
- `GET /api/invoices` - Listar (con filtros)
- `POST /api/invoices` - Crear
- `GET /api/invoices/{id}` - Ver detalle
- `PUT /api/invoices/{id}` - Actualizar
- `DELETE /api/invoices/{id}` - Eliminar

### Clientes (requiere autenticación)
- `GET /api/customers` - Listar
- `POST /api/customers` - Crear
- `GET /api/customers/{id}` - Ver detalle
- `PUT /api/customers/{id}` - Actualizar
- `DELETE /api/customers/{id}` - Eliminar

### Usuarios (solo admin)
- `GET /api/users` - Listar
- `PUT /api/users/{id}` - Actualizar
- `DELETE /api/users/{id}` - Eliminar

## 🎯 Comando Artisan Personalizado

```bash
# Listar facturas desde la terminal
php artisan invoices:list

# Con filtros
php artisan invoices:list --status=pending --limit=10
php artisan invoices:list --customer=1
```

## 🧪 Probar API con Postman

1. **Login:**
```json
POST http://localhost:8000/api/auth/login
Body: {
  "email": "admin@admin.com",
  "password": "password"
}
```

2. **Copiar el token** de la respuesta

3. **Usar en siguientes peticiones:**
```
Header: Authorization: Bearer {token}
```

## 📊 Datos Creados por Seeders

- 2 Roles: admin, user
- 2 Usuarios de prueba
- 3 Clientes de ejemplo
- 4 Facturas con items

## 🛠️ Comandos Útiles

```bash
# Ver rutas API
php artisan route:list --path=api

# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Ejecutar solo un seeder específico
php artisan db:seed --class=InvoiceSeeder

# Refrescar BD (⚠️ elimina datos)
php artisan migrate:fresh --seed
```

## ✨ Características Implementadas

### Backend ✅
- API REST con Laravel 11
- Autenticación con Sanctum
- Roles y permisos (Spatie)
- Arquitectura modular (Services)
- Validaciones robustas
- Caching (listado facturas)
- Logging de eventos
- Comando Artisan personalizado
- Subida de archivos
- Soft deletes

### Frontend ✅
- Vue 3 + Composition API
- Pinia para estado global
- Vue Router con guards
- Bootstrap 5
- Validaciones con Vee-Validate
- Axios para HTTP

## 🔐 Roles y Permisos

**Admin:** Acceso total (facturas, clientes, usuarios)  
**User:** Gestión de facturas y clientes


## 📝 Notas

- Los totales de facturas se calculan automáticamente
- Las facturas pendientes se marcan como vencidas automáticamente
- El cache de facturas se limpia al crear/actualizar/eliminar
- Todos los eventos importantes se registran en logs

