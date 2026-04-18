<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'cham-cong.view',
        'cham-cong.edit',
        'cham-cong.export',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Tạo permissions mới
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Gán cho role IT
        $it = Role::where('name', 'it')->first();
        if ($it) {
            $it->givePermissionTo($this->permissions);
        }

        // Xoá permissions cũ (attendance.*) nếu tồn tại
        Permission::whereIn('name', ['attendance.view', 'attendance.edit', 'attendance.export'])
            ->each(function ($permission) {
                $permission->roles()->detach();
                $permission->users()->detach();
                $permission->delete();
            });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Khôi phục permissions cũ
        foreach (['attendance.view', 'attendance.edit', 'attendance.export'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $it = Role::where('name', 'it')->first();
        if ($it) {
            $it->givePermissionTo(['attendance.view', 'attendance.edit', 'attendance.export']);
        }

        // Xoá permissions mới
        Permission::whereIn('name', $this->permissions)
            ->each(function ($permission) {
                $permission->roles()->detach();
                $permission->users()->detach();
                $permission->delete();
            });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
