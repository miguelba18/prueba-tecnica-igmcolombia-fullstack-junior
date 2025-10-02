<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Listar facturas con filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status',
                'customer_id',
                'date_from',
                'date_to',
                'search',
                'sort_by',
                'sort_order',
                'per_page'
            ]);

            $invoices = $this->invoiceService->list($filters);

            return response()->json([
                'data' => InvoiceResource::collection($invoices),
                'meta' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva factura
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();

            $file = $request->hasFile('attachment') ? $request->file('attachment') : null;

            $invoice = $this->invoiceService->create($data, $file);

            return response()->json([
                'message' => 'Factura creada exitosamente',
                'data' => new InvoiceResource($invoice)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de factura
     */
    public function show(Invoice $invoice): JsonResponse
    {
        try {
            $invoice->load(['customer', 'user', 'items']);

            return response()->json([
                'data' => new InvoiceResource($invoice)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar factura
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        try {
            $data = $request->validated();
            $file = $request->hasFile('attachment') ? $request->file('attachment') : null;

            $invoice = $this->invoiceService->update($invoice, $data, $file);

            return response()->json([
                'message' => 'Factura actualizada exitosamente',
                'data' => new InvoiceResource($invoice)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar factura
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            $this->invoiceService->delete($invoice);

            return response()->json([
                'message' => 'Factura eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de factura
     */
    public function changeStatus(Request $request, Invoice $invoice): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,paid,overdue'
        ]);

        try {
            $invoice = $this->invoiceService->changeStatus($invoice, $request->status);

            return response()->json([
                'message' => 'Estado actualizado exitosamente',
                'data' => new InvoiceResource($invoice->load(['customer', 'items']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cambiar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}