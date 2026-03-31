<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CommissionRequest extends Model
{
    /** @use HasFactory */
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $fillable = [
        'contract_type', 'contract_id', 'receiver_name', 'receiver_phone',
        'bank_account', 'amount', 'referrer_info', 'notes', 'status', 'processed_at', 'user_id',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:0',
    ];

    public const CONTRACT_TYPES = [
        'waste'          => ContractWaste::class,
        'consulting'     => ContractConsulting::class,
        'project'        => ContractProject::class,
        'commercial'     => ContractCommercial::class,
        'sustainability' => ContractSustainability::class,
        'energy'         => ContractEnergy::class,
    ];

    public const CONTRACT_TYPE_LABELS = [
        ContractWaste::class          => 'Chất thải & Tiếng ồn',
        ContractConsulting::class      => 'Pháp lý & Hồ sơ MT',
        ContractProject::class         => 'Kỹ thuật & Ứng phó SC',
        ContractCommercial::class      => 'NC & CĐ Công nghệ',
        ContractSustainability::class  => 'TV & BC PTBV',
        ContractEnergy::class          => 'Phát thải & Năng lượng',
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
        return self::CONTRACT_TYPE_LABELS[$this->contract_type] ?? 'N/A';
    }
}
