<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case UNPAID    = 'unpaid';
    case PARTIAL   = 'partial';
    case PAID      = 'paid';
    case OVERDUE   = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID    => 'Chưa thanh toán',
            self::PARTIAL   => 'TT một phần',
            self::PAID      => 'Đã thanh toán',
            self::OVERDUE   => 'Quá hạn',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNPAID    => 'warning',
            self::PARTIAL   => 'info',
            self::PAID      => 'success',
            self::OVERDUE   => 'danger',
            self::CANCELLED => 'secondary',
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
}
