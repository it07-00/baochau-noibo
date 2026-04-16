<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $newPermissions = [
        'reports-sales.view',
        'reports-consulting.view',
        'reports-technical.view',
        'mail-delivery-admin.view',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Tạo permissions mới
        foreach ($this->newPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Cấp thêm cho từng role — không ảnh hưởng quyền hiện có
        $give = [
            'giam-doc'      => ['reports-sales.view'],
            'quan-ly'       => ['reports-sales.view'],
            'tp-kinh-doanh' => ['reports-sales.view'],
            'kinh-doanh'    => ['reports-sales.view'],
            'tu-van'        => ['reports-consulting.view'],
            'ky-thuat'      => ['reports-technical.view'],
            'it'            => ['mail-delivery-admin.view'],
        ];

        foreach ($give as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->newPermissions as $name) {
            if ($permission = Permission::where('name', $name)->first()) {
                $permission->roles()->detach();
                $permission->delete();
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
