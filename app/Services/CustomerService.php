<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    /**
     * Listar clientes con filtros y paginación
     */
    public function list(array $filters = [])
    {
        $query = Customer::query();

        // Búsqueda
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filtro por tipo de documento
        if (isset($filters['document_type'])) {
            $query->documentType($filters['document_type']);
        }

        // Ordenamiento
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear un nuevo cliente
     */
    public function create(array $data): Customer
    {
        try {
            $customer = Customer::create($data);
            
            Log::info('Cliente creado', ['customer_id' => $customer->id, 'email' => $customer->email]);

            return $customer;
            
        } catch (\Exception $e) {
            Log::error('Error al crear cliente', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Actualizar un cliente existente
     */
    public function update(Customer $customer, array $data): Customer
    {
        try {
            $customer->update($data);
            
            Log::info('Cliente actualizado', ['customer_id' => $customer->id]);

            return $customer->fresh();
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Eliminar un cliente
     */
    public function delete(Customer $customer): bool
    {
        try {
            // Verificar si tiene facturas asociadas
            if ($customer->invoices()->count() > 0) {
                throw new \Exception('No se puede eliminar el cliente porque tiene facturas asociadas.');
            }

            $customer->delete();
            
            Log::info('Cliente eliminado', ['customer_id' => $customer->id]);

            return true;
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar cliente', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Obtener cliente con sus facturas
     */
    public function getWithInvoices(Customer $customer)
    {
        return $customer->load(['invoices' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);
    }
}