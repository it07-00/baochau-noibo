<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalDoc extends Model
{
    protected $fillable = ['title', 'files', 'department_id'];

    protected $casts = [
        'files' => 'array',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
