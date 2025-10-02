<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Listar clientes con filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search',
                'document_type',
                'sort_by',
                'sort_order',
                'per_page'
            ]);

            $customers = $this->customerService->list($filters);

            return response()->json([
                'data' => CustomerResource::collection($customers),
                'meta' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo cliente
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        try {
            $customer = $this->customerService->create($request->validated());

            return response()->json([
                'message' => 'Cliente creado exitosamente',
                'data' => new CustomerResource($customer)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de cliente
     */
    public function show(Customer $customer): JsonResponse
    {
        try {
            return response()->json([
                'data' => new CustomerResource($customer)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cliente
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        try {
            $customer = $this->customerService->update($customer, $request->validated());

            return response()->json([
                'message' => 'Cliente actualizado exitosamente',
                'data' => new CustomerResource($customer)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar cliente
     */
    public function destroy(Customer $customer): JsonResponse
    {
        try {
            $this->customerService->delete($customer);

            return response()->json([
                'message' => 'Cliente eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}