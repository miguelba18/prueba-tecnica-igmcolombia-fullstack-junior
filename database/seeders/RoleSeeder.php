<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Crear permisos
        $permissions = [
            // Permisos de facturas
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            
            // Permisos de clientes
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            
            // Permisos de usuarios (solo admin)
            'users.view',
            'users.edit',
            'users.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Asignar todos los permisos al admin
        $adminRole->givePermissionTo(Permission::all());

        // Asignar permisos limitados al usuario normal
        $userRole->givePermissionTo([
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
        ]);
    }
}