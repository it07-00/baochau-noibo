<?php

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        PermissionEnum::DAILY_REPORTS_VIEW->value,
        PermissionEnum::DAILY_REPORTS_CREATE->value,
        PermissionEnum::DAILY_REPORTS_EDIT->value,
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // 1. Create the 'thuc-tap' role if it doesn't exist
        $role = Role::firstOrCreate([
            'name' => RoleEnum::THUC_TAP->value,
            'guard_name' => 'web',
        ]);

        // 2. Assign daily report permissions
        $role->syncPermissions($this->permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
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
