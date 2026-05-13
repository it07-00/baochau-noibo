<?php

namespace App\Enums;

enum ContractRenewalStatus: string
{
    case CHUA_DEN_HAN = 'CHƯA ĐẾN HẠN';
    case DEN_HAN      = 'ĐẾN HẠN';
    case CHUA_TAI_KY  = 'CHƯA TÁI KÝ';
    case DANG_TAI_KY  = 'ĐANG TÁI KÝ';
    case DA_TAI_KY    = 'ĐÃ TÁI KÝ';
    case KHONG_TAI_KY = 'KHÔNG TÁI KÝ';
    case ROT_TAI_KY   = 'RỚT TÁI KÝ';
    case CHO_XAC_NHAN = 'CHỜ XÁC NHẬN';

    public function label(): string
    {
        return match ($this) {
            self::CHUA_DEN_HAN => 'Chưa đến hạn',
            self::DEN_HAN      => 'Đến hạn',
            self::CHUA_TAI_KY  => 'Chưa tái ký',
            self::DANG_TAI_KY  => 'Đang tái ký',
            self::DA_TAI_KY    => 'Đã tái ký',
            self::KHONG_TAI_KY => 'Không tái ký',
            self::ROT_TAI_KY   => 'Rớt tái ký',
            self::CHO_XAC_NHAN => 'Chờ xác nhận',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CHUA_DEN_HAN => 'secondary',
            self::DEN_HAN      => 'warning',
            self::CHUA_TAI_KY  => 'secondary',
            self::DANG_TAI_KY  => 'info',
            self::DA_TAI_KY    => 'success',
            self::KHONG_TAI_KY => 'danger',
            self::ROT_TAI_KY   => 'danger',
            self::CHO_XAC_NHAN => 'warning',
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
