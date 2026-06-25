<?php

namespace App\Support;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DailyReportVisibility
{
    public static function canManage(User $viewer): bool
    {
        return $viewer->hasAnyRole([
            Role::IT->value,
            Role::GIAM_DOC->value,
            Role::TP_KINH_DOANH->value,
        ]) || $viewer->can(Permission::DAILY_REPORTS_VIEW_ALL->value);
    }

    public static function visibleUsersQuery(User $viewer): Builder
    {
        $query = User::query()->where('is_active', true);

        if (self::isSalesManagerScope($viewer)) {
            return $query->role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]);
        }

        if (self::canSeeCompanyWide($viewer)) {
            return $query;
        }

        return $query->whereKey($viewer->id);
    }

    public static function visibleReportingUsersQuery(User $viewer): Builder
    {
        return self::visibleUsersQuery($viewer)
            ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', Role::GIAM_DOC->value));
    }

    private static function isSalesManagerScope(User $viewer): bool
    {
        return $viewer->hasRole(Role::TP_KINH_DOANH->value)
            && ! $viewer->hasAnyRole([Role::IT->value, Role::GIAM_DOC->value]);
    }

    private static function canSeeCompanyWide(User $viewer): bool
    {
        return $viewer->hasAnyRole([Role::IT->value, Role::GIAM_DOC->value])
            || (
                $viewer->can(Permission::DAILY_REPORTS_VIEW_ALL->value)
                && ! $viewer->hasRole(Role::TP_KINH_DOANH->value)
            );
    }
}
