<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private const PERMISSIONS = [
        'regulatory-facilities.view',
        'regulatory-facilities.edit',
    ];

    private const ROLES = [
        'it',
        'giam-doc',
        'tp-kinh-doanh',
        'kinh-doanh',
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        foreach (self::ROLES as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->givePermissionTo(self::PERMISSIONS);
        }
    }

    public function down(): void
    {
        foreach (self::ROLES as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            $role?->revokePermissionTo(self::PERMISSIONS);
        }

        foreach (self::PERMISSIONS as $perm) {
            Permission::query()
                ->where('name', $perm)
                ->where('guard_name', 'web')
                ->delete();
        }
    }
};
