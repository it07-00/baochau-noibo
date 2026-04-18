<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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
        'slug',
        'tax_code',
        'phone',
        'email',
        'address',
        'province',
        'representative',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Customer $customer) {
            if (empty($customer->slug) || $customer->isDirty('name')) {
                $base = Str::slug($customer->name);
                if (empty($base)) {
                    $base = 'khach-hang';
                }
                $slug = $base;
                $i = 1;
                while (
                    static::where('slug', $slug)
                        ->when($customer->exists, fn ($q) => $q->where('id', '!=', $customer->id))
                        ->exists()
                ) {
                    $slug = $base . '-' . $i++;
                }
                $customer->slug = $slug;
            }
        });
    }

    public function contracts()
    {
        return $this->hasMany(ContractWaste::class);
    }

    public function contractsConsulting()
    {
        return $this->hasMany(ContractLegal::class);
    }

    public function contractsCommercial()
    {
        return $this->hasMany(ContractResearch::class);
    }

    public function contractsProject()
    {
        return $this->hasMany(ContractTechnical::class);
    }

    public function contractsEnergy()
    {
        return $this->hasMany(ContractEmission::class);
    }

    public function contractsSustainability()
    {
        return $this->hasMany(ContractSustainability::class);
    }
}
