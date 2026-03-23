<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['contract_waste_id', 'receiver_name', 'receiver_phone', 'bank_account', 'amount', 'referrer_info', 'notes', 'status', 'processed_at', 'user_id'])]
class CommissionRequest extends Model
{
    /** @use HasFactory */
    use HasFactory;

    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:0',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ContractWaste::class, 'contract_waste_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
