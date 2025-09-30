<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registrarán todas las rutas de la API REST.
| Estas rutas son cargadas por RouteServiceProvider y asignadas al grupo
| middleware "api" con prefijo automático /api
|
*/

// Ruta de prueba
Route::get('/ping', function () {
    return response()->json([
        'message' => 'API funcionando correctamente',
        'timestamp' => now()
    ]);
});

// Rutas públicas de autenticación
// Se implementarán en Fase 2
Route::prefix('auth')->group(function () {
    // POST /api/auth/register
    // POST /api/auth/login
});

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });
    
    // Logout
    Route::post('/auth/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout exitoso']);
    });
    
    // Rutas de Facturas (Invoices)
    // Se implementarán en Fase 2
    // Route::apiResource('invoices', InvoiceController::class);
    
    // Rutas de Clientes (Customers)
    // Se implementarán en Fase 2
    // Route::apiResource('customers', CustomerController::class);
    
    // Rutas de Usuarios (Users) - Solo Admin
    // Se implementarán en Fase 2
    // Route::middleware('role:admin')->group(function () {
    //     Route::apiResource('users', UserController::class)->except(['store']);
    // });
});