<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
}
