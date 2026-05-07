<?php

namespace App\Enums;

enum ContractVoucherStatus: string
{
    case DE_NGHI_THANH_TOAN = 'Đã đề nghị thanh toán/tạm ứng';
    case XUAT_HOA_DON       = 'Đã xuất hóa đơn';
    case BIEN_BAN_BAN_GIAO  = 'Đã làm biên bản bàn giao hồ sơ';
    case NGHIEM_THU_KET_THUC = 'Đã làm BB bàn giao và nghiệm thu kết thúc hợp đồng';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::DE_NGHI_THANH_TOAN  => 'info',
            self::XUAT_HOA_DON        => 'primary',
            self::BIEN_BAN_BAN_GIAO   => 'warning',
            self::NGHIEM_THU_KET_THUC => 'success',
        };
    }

    /** Mảng values — dùng cho validation */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
