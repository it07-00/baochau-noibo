<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContractAssignment extends Model
{
    protected $fillable = [
        'assignable_type',
        'assignable_id',
        'user_id',
        'assigned_by',
        'external_assignee',
        'note',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
