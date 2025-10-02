<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
class InvoiceService
{
    /**
     * Listar facturas con filtros y paginación
     */
    

/**
 * Listar facturas con filtros y paginación
 */
public function list(array $filters = [])
{
    // Si no hay filtros, usar caché para las facturas recientes
    if (empty(array_filter($filters))) {
        return Cache::remember('invoices_recent', 300, function () { 
            return Invoice::with(['customer', 'user', 'items'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        });
    }

    // Si hay filtros, no usar caché
    $query = Invoice::with(['customer', 'user', 'items']);

    if (isset($filters['status'])) {
        $query->status($filters['status']);
    }

    if (isset($filters['customer_id'])) {
        $query->customer($filters['customer_id']);
    }

    if (isset($filters['date_from']) || isset($filters['date_to'])) {
        $query->dateRange(
            $filters['date_from'] ?? null,
            $filters['date_to'] ?? null
        );
    }

    if (isset($filters['search'])) {
        $query->search($filters['search']);
    }

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
        
        $data['invoice_number'] = $this->generateInvoiceNumber();
        
       
        if ($file) {
            $data['attachment_path'] = $file->store('invoices', 'public');
        }

        $data['subtotal'] = 0;
        $data['tax_amount'] = 0;
        $data['total_amount'] = 0;

       
        $invoice = Invoice::create($data);

       
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $invoice->items()->create($itemData);
            }
            
           
            $invoice->calculateTotals();
        }
        Cache::forget('invoices_recent');

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
            
            // Recalcular totales después de actualizar los items
            $invoice->calculateTotals();
        }
        Cache::forget('invoices_recent');

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
            Cache::forget('invoices_recent');
            
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
    $year = now()->year;
    $prefix = "INV-{$year}-";
    
    // Incluir registros eliminados en la búsqueda
    $lastInvoice = Invoice::withTrashed()
        ->where('invoice_number', 'LIKE', "{$prefix}%")
        ->lockForUpdate()
        ->orderBy('invoice_number', 'desc')
        ->first();
    
    if ($lastInvoice) {
        $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }
    
    return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
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