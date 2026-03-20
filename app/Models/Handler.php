<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Handler extends Model
{
    protected $guarded = [];

    public function contracts()
    {
        return $this->hasMany(ContractWaste::class);
    }
}
