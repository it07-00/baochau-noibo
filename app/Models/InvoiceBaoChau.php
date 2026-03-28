<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceBaoChau extends Model
{
    protected $table = 'invoice_bao_chau';

    const STATUSES = [
        'unpaid'    => 'Chưa thanh toán',
        'partial'   => 'TT một phần',
        'paid'      => 'Đã thanh toán',
        'overdue'   => 'Quá hạn',
        'cancelled' => 'Đã hủy',
    ];

    protected $fillable = [
        'contract_waste_id', 'customer_id', 'invoice_number',
        'issue_date', 'due_date', 'amount', 'vat_percent',
        'vat_amount', 'total_amount', 'status', 'paid_amount',
        'paid_at', 'service_description', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'paid_at'     => 'date',
        'amount'      => 'decimal:0',
        'vat_amount'  => 'decimal:0',
        'total_amount'=> 'decimal:0',
        'paid_amount' => 'decimal:0',
    ];

    public function contractWaste(): BelongsTo
    {
        return $this->belongsTo(ContractWaste::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'unpaid'    => 'warning',
            'partial'   => 'info',
            'paid'      => 'success',
            'overdue'   => 'danger',
            'cancelled' => 'secondary',
            default     => 'secondary',
        };
    }
}
