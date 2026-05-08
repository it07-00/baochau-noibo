<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'title', 'description', 'start_date', 'end_date', 'color'])]
class WorkSchedule extends Model
{
    /** @use HasFactory */
    use HasFactory;

    public const COLORS = [
        'primary'   => ['label' => 'Xanh dương', 'hex' => '#3b82f6'],
        'success'   => ['label' => 'Xanh lá',    'hex' => '#10b981'],
        'warning'   => ['label' => 'Vàng cam',   'hex' => '#f59e0b'],
        'danger'    => ['label' => 'Đỏ',         'hex' => '#ef4444'],
        'info'      => ['label' => 'Xanh lơ',    'hex' => '#06b6d4'],
        'secondary' => ['label' => 'Xám',        'hex' => '#6b7280'],
        'pink'      => ['label' => 'Hồng',       'hex' => '#ec4899'],
        'purple'    => ['label' => 'Tím',        'hex' => '#8b5cf6'],
        'indigo'    => ['label' => 'Chàm',       'hex' => '#6366f1'],
        'teal'      => ['label' => 'Ngọc',       'hex' => '#14b8a6'],
        'orange'    => ['label' => 'Cam',        'hex' => '#f97316'],
        'lime'      => ['label' => 'Xanh nõn',   'hex' => '#84cc16'],
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'work_schedule_participants')->withTimestamps();
    }

    /**
     * Get the effective end date (defaults to start_date if null).
     */
    public function getEffectiveEndDateAttribute(): \Carbon\Carbon
    {
        return $this->end_date ?? $this->start_date;
    }

    /**
     * Check if this event covers a given date.
     */
    public function coversDate(\Carbon\Carbon $date): bool
    {
        return $date->between($this->start_date, $this->effective_end_date);
    }
}
