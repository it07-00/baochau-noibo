<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'cash-flow.view',
            'cash-flow.export',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Kế toán: xem + xuất
        $keeToan = Role::where('name', 'ke-toan')->first();
        if ($keeToan) {
            $keeToan->givePermissionTo($permissions);
        }

        // Giám đốc: xem + xuất
        $giamDoc = Role::where('name', 'giam-doc')->first();
        if ($giamDoc) {
            $giamDoc->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'cash-flow.view',
            'cash-flow.export',
        ];

        foreach (Role::all() as $role) {
            $role->revokePermissionTo($permissions);
        }

        Permission::whereIn('name', $permissions)->delete();
    }
};
