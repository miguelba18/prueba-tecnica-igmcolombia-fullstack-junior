<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class InvoiceService
{
    /**
     * Listar facturas con filtros y paginación
     */
    public function list(array $filters = [])
    {
        $query = Invoice::with(['customer', 'user', 'items']);

        // Filtro por estado
        if (isset($filters['status'])) {
            $query->status($filters['status']);
        }

        // Filtro por cliente
        if (isset($filters['customer_id'])) {
            $query->customer($filters['customer_id']);
        }

        // Filtro por rango de fechas
        if (isset($filters['date_from']) || isset($filters['date_to'])) {
            $query->dateRange(
                $filters['date_from'] ?? null,
                $filters['date_to'] ?? null
            );
        }

        // Búsqueda por número de factura
        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        // Ordenamiento
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Crear una nueva factura con sus items
     */
    public function create(array $data, ?UploadedFile $file = null): Invoice
    {
        DB::beginTransaction();
        
        try {
            // Generar número de factura único
            $data['invoice_number'] = $this->generateInvoiceNumber();
            
            // Guardar archivo adjunto si existe
            if ($file) {
                $data['attachment_path'] = $file->store('invoices', 'public');
            }

            // Crear la factura
            $invoice = Invoice::create($data);

            // Crear los items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $invoice->items()->create($itemData);
                }
            }

            DB::commit();
            
            Log::info('Factura creada', ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number]);

            return $invoice->load(['customer', 'items']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Eliminar archivo si se subió
            if (isset($data['attachment_path'])) {
                Storage::disk('public')->delete($data['attachment_path']);
            }
            
            Log::error('Error al crear factura', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Actualizar una factura existente
     */
    public function update(Invoice $invoice, array $data, ?UploadedFile $file = null): Invoice
    {
        DB::beginTransaction();
        
        try {
            // Actualizar archivo adjunto si se envía uno nuevo
            if ($file) {
                // Eliminar archivo anterior
                if ($invoice->attachment_path) {
                    Storage::disk('public')->delete($invoice->attachment_path);
                }
                $data['attachment_path'] = $file->store('invoices', 'public');
            }

            // Actualizar factura
            $invoice->update($data);

            // Actualizar items si se envían
            if (isset($data['items']) && is_array($data['items'])) {
                // Eliminar items existentes
                $invoice->items()->delete();
                
                // Crear nuevos items
                foreach ($data['items'] as $itemData) {
                    $invoice->items()->create($itemData);
                }
            }

            DB::commit();
            
            Log::info('Factura actualizada', ['invoice_id' => $invoice->id]);

            return $invoice->load(['customer', 'items']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar factura', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Eliminar una factura
     */
    public function delete(Invoice $invoice): bool
    {
        try {
            // Eliminar archivo adjunto
            if ($invoice->attachment_path) {
                Storage::disk('public')->delete($invoice->attachment_path);
            }

            $invoice->delete();
            
            Log::info('Factura eliminada', ['invoice_id' => $invoice->id]);

            return true;
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar factura', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generar número de factura único
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, -5)) + 1 : 1;

        return sprintf('%s-%s-%05d', $prefix, $year, $number);
    }

    /**
     * Cambiar estado de una factura
     */
    public function changeStatus(Invoice $invoice, string $status): Invoice
    {
        $invoice->update(['status' => $status]);
        
        Log::info('Estado de factura cambiado', [
            'invoice_id' => $invoice->id,
            'status' => $status
        ]);

        return $invoice;
    }
}