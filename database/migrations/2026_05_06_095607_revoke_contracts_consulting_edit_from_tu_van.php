<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::findByName('tu-van');
        $permission = Permission::findByName('contracts-consulting.edit');

        if ($role && $permission && $role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $role = Role::findByName('tu-van');
        $permission = Permission::findByName('contracts-consulting.edit');

        if ($role && $permission && !$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
