<?php

namespace App\Models;

use App\Enums\ContractType;
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
        'amount' => 'integer',
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
}
