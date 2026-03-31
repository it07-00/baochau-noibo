<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
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
        'tax_code',
        'phone',
        'email',
        'address',
        'province',
        'representative',
    ];

    public function contracts()
    {
        return $this->hasMany(ContractWaste::class);
    }

    public function contractsConsulting()
    {
        return $this->hasMany(ContractConsulting::class);
    }

    public function contractsCommercial()
    {
        return $this->hasMany(ContractCommercial::class);
    }

    public function contractsProject()
    {
        return $this->hasMany(ContractProject::class);
    }

    public function contractsEnergy()
    {
        return $this->hasMany(ContractEnergy::class);
    }

    public function contractsSustainability()
    {
        return $this->hasMany(ContractSustainability::class);
    }
}
