<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'quotation-tracking.view',
            'guard_name' => 'web',
        ]);

        $role = Role::where('name', 'giam-doc')->first();
        if ($role) {
            $role->givePermissionTo($permission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::where('name', 'giam-doc')->first();
        if ($role) {
            $role->revokePermissionTo('quotation-tracking.view');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
