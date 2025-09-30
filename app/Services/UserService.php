<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Listar usuarios con filtros y paginación
     */
    public function list(array $filters = [])
    {
        $query = User::with('roles');

        // Búsqueda
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtro por rol
        if (isset($filters['role'])) {
            $query->role($filters['role']);
        }

        // Ordenamiento
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Actualizar un usuario existente
     */
    public function update(User $user, array $data): User
    {
        try {
            // Si se envía una nueva contraseña, hashearla
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            // Actualizar rol si se envía
            if (isset($data['role'])) {
                $user->syncRoles([$data['role']]);
            }
            
            Log::info('Usuario actualizado', ['user_id' => $user->id]);

            return $user->fresh()->load('roles');
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Eliminar un usuario
     */
    public function delete(User $user): bool
    {
        try {
            // No permitir eliminar el propio usuario
            if (auth()->id() === $user->id) {
                throw new \Exception('No puedes eliminar tu propio usuario.');
            }

            $user->delete();
            
            Log::info('Usuario eliminado', ['user_id' => $user->id]);

            return true;
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}