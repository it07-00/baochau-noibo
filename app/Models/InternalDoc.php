<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalDoc extends Model
{
    protected $fillable = ['title', 'files'];

    protected $casts = [
        'files' => 'array',
    ];
}
