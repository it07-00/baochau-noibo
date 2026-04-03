<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingDailyReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_date',
        'facebook_count',
        'zalo_count',
        'website_count',
        'tiktok_count',
        'youtube_count',
        'other_count',
        'other_channel_name',
        'content_details',
        'banners',
        'targets_achieved',
        'notes',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalContentAttribute(): int
    {
        return $this->facebook_count + $this->zalo_count + $this->website_count
            + $this->tiktok_count + $this->youtube_count + $this->other_count;
    }
}
