<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingContent extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'platforms',
        'scheduled_at',
        'status',
        'reviewer_id',
        'reviewer_note',
        'reviewed_at',
        'images',
    ];

    protected $casts = [
        'platforms'   => 'array',
        'images'      => 'array',
        'scheduled_at' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public static array $platforms = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok'    => 'TikTok',
        'zalo'      => 'Zalo',
    ];

    public static array $statusLabels = [
        'draft'    => 'Nháp',
        'pending'  => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];

    public static array $statusColors = [
        'draft'    => 'secondary',
        'pending'  => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
