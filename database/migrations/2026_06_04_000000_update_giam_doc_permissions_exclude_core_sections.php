<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $revoke = [
        // Phòng ban
        'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
        // Quản lý hợp đồng
        'contracts-waste.view', 'contracts-waste.create', 'contracts-waste.edit', 'contracts-waste.delete',
        'contracts-consulting.view', 'contracts-consulting.create', 'contracts-consulting.edit', 'contracts-consulting.delete',
        'contracts-project.view', 'contracts-project.create', 'contracts-project.edit', 'contracts-project.delete',
        'contracts-commercial.view', 'contracts-commercial.create', 'contracts-commercial.edit', 'contracts-commercial.delete',
        'contracts-sustainability.view', 'contracts-sustainability.create', 'contracts-sustainability.edit', 'contracts-sustainability.delete',
        'contracts-energy.view', 'contracts-energy.create', 'contracts-energy.edit', 'contracts-energy.delete',
        // Lịch thanh toán
        'payment-schedules.view', 'payment-schedules.create', 'payment-schedules.edit', 'payment-schedules.delete',
        // Bộ phận kinh doanh
        'sales-progressive.view', 'sales-progressive.create', 'sales-progressive.edit', 'sales-progressive.delete',
        'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleGd = Role::where('name', 'giam-doc')->first();
        if ($roleGd) {
            $roleGd->revokePermissionTo($this->revoke);
        }

        $roleTpkd = Role::where('name', 'tp-kinh-doanh')->first();
        if ($roleTpkd) {
            $roleTpkd->givePermissionTo('cash-flow.view');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleGd = Role::where('name', 'giam-doc')->first();
        if ($roleGd) {
            $roleGd->givePermissionTo($this->revoke);
        }

        $roleTpkd = Role::where('name', 'tp-kinh-doanh')->first();
        if ($roleTpkd) {
            $roleTpkd->revokePermissionTo('cash-flow.view');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
