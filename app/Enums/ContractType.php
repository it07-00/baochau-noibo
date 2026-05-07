<?php

namespace App\Enums;

use App\Models\ContractWaste;
use App\Models\ContractLegal;
use App\Models\ContractTechnical;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractEmission;

enum ContractType: string
{
    case WASTE          = 'waste';
    case CONSULTING     = 'consulting';
    case PROJECT        = 'project';
    case COMMERCIAL     = 'commercial';
    case SUSTAINABILITY = 'sustainability';
    case ENERGY         = 'energy';

    public function modelClass(): string
    {
        return match ($this) {
            self::WASTE          => ContractWaste::class,
            self::CONSULTING     => ContractLegal::class,
            self::PROJECT        => ContractTechnical::class,
            self::COMMERCIAL     => ContractResearch::class,
            self::SUSTAINABILITY => ContractSustainability::class,
            self::ENERGY         => ContractEmission::class,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::WASTE          => 'Chất thải & Tiếng ồn',
            self::CONSULTING     => 'Pháp lý & Hồ sơ MT',
            self::PROJECT        => 'Kỹ thuật & Ứng phó SC',
            self::COMMERCIAL     => 'NC & CĐ Công nghệ',
            self::SUSTAINABILITY => 'TV & BC PTBV',
            self::ENERGY         => 'Phát thải & Năng lượng',
        };
    }

    public static function fromModelClass(string $class): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->modelClass() === $class) {
                return $case;
            }
        }
        return null;
    }

    /** Returns [modelClass => label] for view dropdowns */
    public static function labelMap(): array
    {
        $map = [];
        foreach (self::cases() as $case) {
            $map[$case->modelClass()] = $case->label();
        }
        return $map;
    }

    /** Returns all model class names */
    public static function modelClasses(): array
    {
        return array_map(fn($case) => $case->modelClass(), self::cases());
    }
}
