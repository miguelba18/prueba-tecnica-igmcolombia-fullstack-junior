<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Usuario normal de prueba
        $user = User::create([
            'name' => 'Usuario de Prueba',
            'email' => 'user@user.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('user');
    }
}