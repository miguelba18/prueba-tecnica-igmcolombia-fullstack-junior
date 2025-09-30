<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con factura
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calcular montos automáticamente antes de guardar
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
            $item->tax_amount = $item->subtotal * ($item->tax_rate / 100);
            $item->total = $item->subtotal + $item->tax_amount;
        });

        static::saved(function ($item) {
            // Recalcular totales de la factura
            $item->invoice->calculateTotals();
        });

        static::deleted(function ($item) {
            // Recalcular totales de la factura
            $item->invoice->calculateTotals();
        });
    }
}