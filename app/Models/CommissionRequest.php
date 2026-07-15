<?php

namespace App\Models;

use App\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CommissionRequest extends Model
{
    /** @use HasFactory */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'contract_type',
        'contract_id',
        'manual_contract_number',
        'receiver_name',
        'receiver_phone',
        'bank_account',
        'bank_code',
        'bank_number',
        'amount',
        'referrer_info',
        'notes',
        'status',
        'processed_at',
        'user_id',
        'payment_bill_path',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'integer',
        'user_id' => 'integer',
    ];

    public function contract(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'contract_type', 'contract_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getContractTypeLabelAttribute(): string
    {
        return ContractType::fromModelClass($this->contract_type)?->label() ?? 'N/A';
    }

    public function getPaymentBillUrlAttribute(): ?string
    {
        if ($this->payment_bill_path) {
            return Storage::disk(config('filesystems.upload_disk', 'public'))->url($this->payment_bill_path);
        }

        return null;
    }

    public function getQrUrlAttribute(): string
    {
        $bankNumber = preg_replace('/\D+/', '', (string) $this->bank_number);

        if ($this->bank_code && $bankNumber !== '') {
            $contractShd = $this->contract_number;
            $contract = $this->contract;
            if ($contract && isset($contract->shd_bc)) {
                $contractShd = $contract->shd_bc;
            }
            $receiverName = rawurlencode(strtoupper(Str::ascii($this->receiver_name ?: '')));
            $description = rawurlencode("Chi hoa hong HD {$contractShd}");

            return "https://img.vietqr.io/image/{$this->bank_code}-{$bankNumber}-compact2.png?amount={$this->amount}&addInfo={$description}&accountName={$receiverName}";
        }

        return '';
    }

    public function getContractNumberAttribute(): string
    {
        return (string) ($this->contract?->shd_bc ?: $this->manual_contract_number ?: 'Hoa hong');
    }
}
