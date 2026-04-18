<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = ['employee_id', 'checked_at'];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(AttendanceEmployee::class, 'employee_id');
    }
}
