<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::where('name', 'tu-van')->first();
        $permission = Permission::where('name', 'contracts-consulting.edit')->first();

        if ($role && $permission && $role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $role = Role::where('name', 'tu-van')->first();
        $permission = Permission::where('name', 'contracts-consulting.edit')->first();

        if ($role && $permission && !$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
