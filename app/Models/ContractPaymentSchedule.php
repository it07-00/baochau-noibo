<?php

namespace App\Models;

use App\Enums\PaymentScheduleStatus;
use Illuminate\Database\Eloquent\Model;

class ContractPaymentSchedule extends Model
{
    protected $fillable = [
        'contract_type',
        'contract_id',
        'installment_number',
        'installment_name',
        'percentage',
        'amount',
        'due_date',
        'paid_date',
        'paid_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'percentage'  => 'integer',
        'amount'      => 'integer',
        'paid_amount' => 'integer',
        'due_date'    => 'date',
        'paid_date'   => 'date',
    ];




    public function contract()
    {
        return $this->morphTo('contract', 'contract_type', 'contract_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return PaymentScheduleStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getContractNumberAttribute(): ?string
    {
        $contract = $this->contract;
        if (!$contract) return null;
        // ContractWaste has shd_cxl as primary, others use shd_bc
        return $contract->shd_cxl ?? $contract->shd_bc ?? null;
    }

    public function getStatusColorAttribute(): string
    {
        return PaymentScheduleStatus::tryFrom($this->status)?->color() ?? 'secondary';
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function getIsRevenueRecognizedAttribute(): bool
    {
        return in_array($this->status, ['paid', 'partial']);
    }
}
