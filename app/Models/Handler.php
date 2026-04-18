<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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
        'slug',
        'phone',
        'address',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Handler $handler) {
            if (empty($handler->slug) || $handler->isDirty('name')) {
                $base = Str::slug($handler->name);
                $slug = $base;
                $i = 1;
                while (
                    static::where('slug', $slug)
                        ->when($handler->exists, fn ($q) => $q->where('id', '!=', $handler->id))
                        ->exists()
                ) {
                    $slug = $base . '-' . $i++;
                }
                $handler->slug = $slug;
            }
        });
    }

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
