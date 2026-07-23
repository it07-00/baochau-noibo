<?php

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => PermissionEnum::MARKETING_REPORTS_VIEW->value,
            'guard_name' => 'web',
        ]);

        $roleGd = Role::where('name', RoleEnum::GIAM_DOC->value)->first();
        if ($roleGd) {
            $roleGd->givePermissionTo($permission);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleGd = Role::where('name', RoleEnum::GIAM_DOC->value)->first();
        if ($roleGd) {
            $roleGd->revokePermissionTo(PermissionEnum::MARKETING_REPORTS_VIEW->value);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
