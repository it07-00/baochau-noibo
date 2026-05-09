<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['year', 'month', 'staff_id', 'target_amount', 'notes'])]
class SalesTarget extends Model
{
    protected $casts = [
        'year'          => 'integer',
        'month'         => 'integer',
        'target_amount' => 'integer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
