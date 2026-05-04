<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
