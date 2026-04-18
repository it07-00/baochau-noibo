<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceEmployee extends Model
{
    protected $fillable = ['device_uid', 'name'];

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'employee_id');
    }
}
