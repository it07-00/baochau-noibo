<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalSoftware extends Model
{
    protected $table = 'internal_software';

    protected $fillable = [
        'name',
        'description',
        'url',
        'version',
        'icon',
        'is_active',
        'department_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
