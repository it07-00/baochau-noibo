<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    // Thêm quyền xem BC tư vấn + kỹ thuật
    private array $grant = [
        'reports-consulting.view',
        'reports-technical.view',
    ];

    // Loại bỏ quyền truy cập bộ phận
    private array $revoke = [
        'sales-renewal.view',
        'consulting-requests.view',
        'technical-requests.view',
        'marketing-reports.view',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::where('name', 'giam-doc')->first();
        if (!$role) return;

        $role->givePermissionTo($this->grant);
        $role->revokePermissionTo($this->revoke);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::where('name', 'giam-doc')->first();
        if (!$role) return;

        $role->revokePermissionTo($this->grant);
        $role->givePermissionTo($this->revoke);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
