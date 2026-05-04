<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Create HR permissions
        $permissions = [
            'hr-profiles.view',
            'hr-profiles.edit',
            'hr-profiles.create',
            'hr-profiles.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Create HCNS role
        $hcns = Role::firstOrCreate(['name' => 'hcns', 'guard_name' => 'web']);
        $hcns->syncPermissions($permissions);

        // Give giam-doc view-only access
        $giamDoc = Role::where('name', 'giam-doc')->first();
        if ($giamDoc) {
            $giamDoc->givePermissionTo('hr-profiles.view');
        }
    }

    public function down(): void
    {
        $role = Role::where('name', 'hcns')->first();
        if ($role) {
            $role->delete();
        }

        Permission::whereIn('name', [
            'hr-profiles.view',
            'hr-profiles.edit',
            'hr-profiles.create',
            'hr-profiles.delete',
        ])->delete();
    }
};
