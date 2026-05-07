<?php

namespace App\Enums;

enum ContractRenewalStatus: string
{
    case CHUA_DEN_HAN = 'CHƯA ĐẾN HẠN';
    case DEN_HAN      = 'ĐẾN HẠN';
    case DA_TAI_KY    = 'ĐÃ TÁI KÝ';
    case KHONG_TAI_KY = 'KHÔNG TÁI KÝ';

    public function label(): string
    {
        return match ($this) {
            self::CHUA_DEN_HAN => 'Chưa đến hạn',
            self::DEN_HAN      => 'Đến hạn',
            self::DA_TAI_KY    => 'Đã tái ký',
            self::KHONG_TAI_KY => 'Không tái ký',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CHUA_DEN_HAN => 'secondary',
            self::DEN_HAN      => 'warning',
            self::DA_TAI_KY    => 'success',
            self::KHONG_TAI_KY => 'danger',
        };
    }

    /** Mảng values — dùng cho validation */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Mảng ['value' => 'label'] dùng cho Blade dropdown */
    public static function map(): array
    {
        return array_column(
            array_map(fn($c) => [$c->value, $c->label()], self::cases()),
            1, 0
        );
    }
}
