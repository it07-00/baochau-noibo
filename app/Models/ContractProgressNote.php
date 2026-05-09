<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContractProgressNote extends Model
{
    protected $fillable = [
        'contract_type',
        'contract_id',
        'user_id',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contract(): MorphTo
    {
        return $this->morphTo('contract', 'contract_type', 'contract_id');
    }
}
