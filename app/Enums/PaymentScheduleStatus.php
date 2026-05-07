<?php

namespace App\Enums;

enum PaymentScheduleStatus: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID    = 'paid';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Chờ thanh toán',
            self::PARTIAL => 'Thanh toán 1 phần',
            self::PAID    => 'Đã thanh toán',
            self::OVERDUE => 'Quá hạn',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'secondary',
            self::PARTIAL => 'warning',
            self::PAID    => 'success',
            self::OVERDUE => 'danger',
        };
    }

    /** Returns ['key' => 'label'] map — drop-in for STATUSES constant in Blade dropdowns */
    public static function map(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }
        return $result;
    }

    /** Returns comma-separated values for validation rules */
    public static function validationRule(): string
    {
        return implode(',', array_column(self::cases(), 'value'));
    }
}
