<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(array $data): array
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Asignar rol de usuario por defecto
            $user->assignRole('user');

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Usuario registrado', ['user_id' => $user->id, 'email' => $user->email]);

            return [
                'user' => $user->load('roles'),
                'token' => $token,
            ];
        } catch (\Exception $e) {
            Log::error('Error en registro de usuario', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Iniciar sesión
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('Usuario inició sesión', ['user_id' => $user->id, 'email' => $user->email]);

        return [
            'user' => $user->load('roles'),
            'token' => $token,
        ];
    }

    /**
     * Cerrar sesión
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
        
        Log::info('Usuario cerró sesión', ['user_id' => $user->id]);
    }
}