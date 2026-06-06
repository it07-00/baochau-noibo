<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure the permission exists (it should, but safety first)
        $permission = Permission::firstOrCreate(['name' => 'marketing-reports.view', 'guard_name' => 'web']);

        // Grant to kinh-doanh
        $roleKd = Role::where('name', 'kinh-doanh')->first();
        if ($roleKd) {
            $roleKd->givePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleKd = Role::where('name', 'kinh-doanh')->first();
        if ($roleKd) {
            $roleKd->revokePermissionTo('marketing-reports.view');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
