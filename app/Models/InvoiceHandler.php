<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceHandler extends Model
{
    use SoftDeletes;
    protected $table = 'invoice_handlers';



    protected $fillable = [
        'contract_waste_id', 'handler_id', 'invoice_number',
        'issue_date', 'due_date', 'amount', 'vat_percent',
        'vat_amount', 'total_amount', 'status', 'paid_amount',
        'paid_at', 'notes', 'created_by',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'paid_at'     => 'date',
        'amount'      => 'decimal:2',
        'vat_amount'  => 'decimal:2',
        'total_amount'=> 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function contractWaste(): BelongsTo
    {
        return $this->belongsTo(ContractWaste::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Handler::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return InvoiceStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return InvoiceStatus::tryFrom($this->status)?->color() ?? 'secondary';
    }
}
