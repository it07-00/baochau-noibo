<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'title', 'description', 'start_date', 'start_time', 'end_date', 'end_time', 'color', 'is_private'])]
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
        'is_private' => 'boolean',
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

    public function getFormattedStartTimeAttribute(): ?string
    {
        return $this->normalizeTimeString($this->start_time);
    }

    public function getFormattedEndTimeAttribute(): ?string
    {
        return $this->normalizeTimeString($this->end_time);
    }

    public function getStartsAtAttribute(): Carbon
    {
        if ($this->formatted_start_time !== null) {
            return Carbon::parse($this->start_date->toDateString() . ' ' . $this->formatted_start_time);
        }

        return $this->start_date->copy()->startOfDay();
    }

    public function getEndsAtAttribute(): Carbon
    {
        if ($this->formatted_end_time !== null) {
            return Carbon::parse($this->effective_end_date->toDateString() . ' ' . $this->formatted_end_time);
        }

        if ($this->end_date !== null && $this->end_date->ne($this->start_date)) {
            return $this->effective_end_date->copy()->endOfDay();
        }

        if ($this->formatted_start_time !== null) {
            return $this->starts_at->copy()->addHour();
        }

        return $this->effective_end_date->copy()->endOfDay();
    }

    public function getTimeRangeLabelAttribute(): string
    {
        if ($this->formatted_start_time !== null && $this->formatted_end_time !== null) {
            return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
        }

        if ($this->formatted_start_time !== null) {
            return $this->formatted_start_time;
        }

        return 'Cả ngày';
    }

    /**
     * Check if this event covers a given date.
     */
    public function coversDate(\Carbon\Carbon $date): bool
    {
        return $date->between($this->start_date, $this->effective_end_date);
    }

    private function normalizeTimeString(mixed $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        return substr((string) $time, 0, 5);
    }
}
