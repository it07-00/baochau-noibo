<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class ContractMilestoneFile extends Model
{
    protected $fillable = [
        'contract_type',
        'contract_id',
        'milestone',
        'file_path',
        'original_name',
        'uploader_id',
    ];

    public function contract(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        $uploadDisk = config('filesystems.upload_disk', 'public');

        if (Storage::disk($uploadDisk)->exists($this->file_path)) {
            return Storage::disk($uploadDisk)->url($this->file_path);
        }

        if ($uploadDisk !== 'public' && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }
}
