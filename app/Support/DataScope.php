<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\User;

/**
 * Centralizes data scope & visibility logic across reports, dashboards, and statistics.
 * Prevents scatter of raw hasRole() checks for data query scoping.
 */
class DataScope
{
    /**
     * Check if user can view all sales data or is restricted to their own assignments.
     */
    public static function canViewAllSalesData(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            Role::GIAM_DOC->value,
            Role::TP_KINH_DOANH->value,
            Role::IT->value,
            Role::KE_TOAN->value,
        ]);
    }

    /**
     * Check if consultant is restricted to only their own assigned contracts.
     */
    public static function isRestrictedConsultant(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole(Role::TU_VAN->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
    }

    /**
     * Check if technical staff is restricted to only their own assigned contracts.
     */
    public static function isRestrictedTechnical(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasRole(Role::KY_THUAT->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
    }

    /**
     * Check if user can filter by staff in reports/dashboards.
     */
    public static function canFilterByStaff(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            Role::GIAM_DOC->value,
            Role::TP_KINH_DOANH->value,
            Role::IT->value,
        ]);
    }

    /**
     * Check if user can see finance/revenue sections in rankings & dashboards.
     */
    public static function canSeeFinanceSection(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([Role::KE_TOAN->value, Role::GIAM_DOC->value])
            || ! $user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
    }
}
