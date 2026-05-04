<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[Fillable(['user_id', 'date', 'content', 'plan', 'status', 'issues'])]
class DailyReport extends Model
{
    /** @use HasFactory */
    use HasFactory;

    protected $casts = [
        'date' => 'date',
    ];

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => clean($value),
            set: fn (string $value) => clean($value),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
