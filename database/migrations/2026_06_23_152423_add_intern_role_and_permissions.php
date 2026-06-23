<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Enums\Role as RoleEnum;
use App\Enums\Permission as PermissionEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create the 'thuc-tap' role if it doesn't exist
        $role = Role::firstOrCreate([
            'name' => RoleEnum::THUC_TAP->value,
            'guard_name' => 'web'
        ]);

        // 2. Assign daily report permissions
        $role->syncPermissions([
            PermissionEnum::DAILY_REPORTS_VIEW->value,
            PermissionEnum::DAILY_REPORTS_CREATE->value,
            PermissionEnum::DAILY_REPORTS_EDIT->value,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the role
        $role = Role::where('name', RoleEnum::THUC_TAP->value)->first();
        if ($role) {
            $role->delete();
        }
    }
};
