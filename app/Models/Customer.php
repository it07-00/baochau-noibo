<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

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
