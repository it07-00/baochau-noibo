<?php

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $newPermissions = [
        PermissionEnum::COMMISSIONS_APPROVE->value,
        PermissionEnum::COMMISSIONS_CONFIRM_PAYMENT->value,
        PermissionEnum::COMMISSIONS_VIEW_ALL->value,
        PermissionEnum::INTERNAL_SOFTWARE_MANAGE->value,
        PermissionEnum::WORK_SCHEDULES_MANAGE_ALL->value,
        PermissionEnum::CONTRACTS_EDIT_FINANCE->value,
        PermissionEnum::INTERNAL_NOTIFICATIONS_MANAGE->value,
        PermissionEnum::MARKETING_TARGETS_EDIT->value,
    ];

    private array $roleAssignments = [
        RoleEnum::IT->value => [
            PermissionEnum::COMMISSIONS_APPROVE->value,
            PermissionEnum::COMMISSIONS_CONFIRM_PAYMENT->value,
            PermissionEnum::COMMISSIONS_VIEW_ALL->value,
            PermissionEnum::INTERNAL_SOFTWARE_MANAGE->value,
            PermissionEnum::WORK_SCHEDULES_MANAGE_ALL->value,
            PermissionEnum::CONTRACTS_EDIT_FINANCE->value,
            PermissionEnum::INTERNAL_NOTIFICATIONS_MANAGE->value,
            PermissionEnum::MARKETING_TARGETS_EDIT->value,
        ],
        RoleEnum::GIAM_DOC->value => [
            PermissionEnum::COMMISSIONS_CONFIRM_PAYMENT->value,
            PermissionEnum::COMMISSIONS_VIEW_ALL->value,
            PermissionEnum::WORK_SCHEDULES_MANAGE_ALL->value,
            PermissionEnum::MARKETING_TARGETS_EDIT->value,
        ],
        RoleEnum::KE_TOAN->value => [
            PermissionEnum::COMMISSIONS_APPROVE->value,
            PermissionEnum::COMMISSIONS_CONFIRM_PAYMENT->value,
            PermissionEnum::COMMISSIONS_VIEW_ALL->value,
            PermissionEnum::CONTRACTS_EDIT_FINANCE->value,
        ],
        RoleEnum::TP_KINH_DOANH->value => [
            PermissionEnum::COMMISSIONS_VIEW_ALL->value,
        ],
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->newPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach ($this->roleAssignments as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($perms);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permission sync down logic omitted to prevent breaking custom permissions
    }
};
