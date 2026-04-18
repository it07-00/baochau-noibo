<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Handler extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    public function contracts()
    {
        return $this->hasMany(ContractWaste::class);
    }

    public function contractLegals()
    {
        return $this->hasMany(ContractLegal::class);
    }

    public function contractTechnicals()
    {
        return $this->hasMany(ContractTechnical::class);
    }

    public function contractResearches()
    {
        return $this->hasMany(ContractResearch::class);
    }

    public function contractSustainabilities()
    {
        return $this->hasMany(ContractSustainability::class);
    }

    public function contractEmissions()
    {
        return $this->hasMany(ContractEmission::class);
    }
}
