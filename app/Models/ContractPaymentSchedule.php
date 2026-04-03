<?php

namespace App\Models;

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
        'percentage'  => 'decimal:2',
        'amount'      => 'decimal:0',
        'paid_amount' => 'decimal:0',
        'due_date'    => 'date',
        'paid_date'   => 'date',
    ];

    public const STATUSES = [
        'pending' => 'Chờ thanh toán',
        'partial' => 'Thanh toán 1 phần',
        'paid'    => 'Đã thanh toán',
        'overdue' => 'Quá hạn',
    ];

    public const MODEL_MAP = [
        'waste'          => ContractWaste::class,
        'consulting'     => ContractConsulting::class,
        'project'        => ContractProject::class,
        'commercial'     => ContractCommercial::class,
        'sustainability' => ContractSustainability::class,
        'energy'         => ContractEnergy::class,
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
        return self::STATUSES[$this->status] ?? $this->status;
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
        return match ($this->status) {
            'paid'    => 'success',
            'partial' => 'warning',
            'overdue' => 'danger',
            default   => 'secondary',
        };
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
