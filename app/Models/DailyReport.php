<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'user_id', 'date', 'content', 'plan', 'status', 'issues',
    'support_status', 'support_handler_id', 'support_response',
    'support_started_at', 'support_resolved_at',
])]
class DailyReport extends Model
{
    /** @use HasFactory */
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    protected $casts = [
        'date' => 'date',
        'support_started_at' => 'datetime',
        'support_resolved_at' => 'datetime',
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

    public function supportHandler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'support_handler_id');
    }
}
