<?php

namespace App\Enums;

enum Role: string
{
    case IT             = 'it';
    case GIAM_DOC       = 'giam-doc';
    case TP_KINH_DOANH  = 'tp-kinh-doanh';
    case KINH_DOANH     = 'kinh-doanh';
    case TU_VAN         = 'tu-van';
    case KY_THUAT       = 'ky-thuat';
    case MARKETING      = 'marketing';
    case KE_TOAN        = 'ke-toan';
    case HCNS           = 'hcns';

    public function label(): string
    {
        return match($this) {
            self::IT            => 'IT / Quản trị',
            self::GIAM_DOC      => 'Giám đốc',
            self::TP_KINH_DOANH => 'Trưởng phòng KD',
            self::KINH_DOANH    => 'Nhân viên KD',
            self::TU_VAN        => 'Tư vấn',
            self::KY_THUAT      => 'Kỹ thuật',
            self::MARKETING     => 'Marketing',
            self::KE_TOAN       => 'Kế toán',
            self::HCNS          => 'Hành chính NS',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::IT            => '#6366f1',
            self::GIAM_DOC      => '#f59e0b',
            self::TP_KINH_DOANH => '#3b82f6',
            self::KINH_DOANH    => '#10b981',
            self::TU_VAN        => '#06b6d4',
            self::KY_THUAT      => '#f97316',
            self::MARKETING     => '#ec4899',
            self::KE_TOAN       => '#84cc16',
            self::HCNS          => '#64748b',
        };
    }

    /** Returns role values in display-priority order (highest first). */
    public static function priorityList(): array
    {
        return [
            self::IT->value,
            self::GIAM_DOC->value,
            self::TP_KINH_DOANH->value,
            self::HCNS->value,
            self::KE_TOAN->value,
            self::MARKETING->value,
            self::TU_VAN->value,
            self::KY_THUAT->value,
            self::KINH_DOANH->value,
        ];
    }

    public static function toMiddleware(self ...$roles): string
    {
        return 'role:' . implode(',', array_map(fn($r) => $r->value, $roles));
    }

    /** Giám đốc + Quản lý — roles that use the dashboard as home. */
    public static function directorRoles(): array
    {
        return [self::GIAM_DOC->value];
    }

    /** IT + Giám đốc + Quản lý — roles that redirect to dashboard in the logo link. */
    public static function dashboardAccessRoles(): array
    {
        return [self::IT->value, self::GIAM_DOC->value];
    }

    /** Kinh doanh + TP Kinh doanh — sales staff. */
    public static function salesRoles(): array
    {
        return [self::KINH_DOANH->value, self::TP_KINH_DOANH->value];
    }

    /** Tư vấn + Kỹ thuật — execution / consulting staff. */
    public static function technicalConsultingRoles(): array
    {
        return [self::TU_VAN->value, self::KY_THUAT->value];
    }
}
