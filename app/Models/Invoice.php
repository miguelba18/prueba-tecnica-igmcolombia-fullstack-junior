<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'invoice_number',
        'description',
        'notes',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $appends = ['is_overdue'];

    /**
     * Relación con cliente
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con usuario que creó la factura
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con items de la factura
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Accessor para verificar si está vencida
     */
    public function getIsOverdueAttribute()
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por cliente
     */
    public function scopeCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('issue_date', '>=', $from);
        }
        if ($to) {
            $query->where('issue_date', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope para búsqueda por número de factura
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('invoice_number', 'like', "%{$search}%");
    }

    /**
     * Actualizar automáticamente estado a vencido
     */
    public static function boot()
    {
        parent::boot();

        static::retrieved(function ($invoice) {
            if ($invoice->status === 'pending' && $invoice->due_date->isPast()) {
                $invoice->status = 'overdue';
                $invoice->saveQuietly();
            }
        });
    }

    /**
     * Calcular totales de la factura
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->items->sum('total');
        $this->save();
    }
}