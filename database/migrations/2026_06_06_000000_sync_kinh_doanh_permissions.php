<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $kdPermissions = [
        'customers.view', 'customers.create', 'customers.edit',
        'handlers.view',
        'contracts-waste.view', 'contracts-waste.create', 'contracts-waste.edit',
        'contracts-consulting.view', 'contracts-consulting.create', 'contracts-consulting.edit',
        'contracts-project.view', 'contracts-project.create', 'contracts-project.edit',
        'contracts-commercial.view', 'contracts-commercial.create', 'contracts-commercial.edit',
        'contracts-sustainability.view', 'contracts-sustainability.create', 'contracts-sustainability.edit',
        'contracts-energy.view', 'contracts-energy.create', 'contracts-energy.edit',
        'payment-schedules.view', 'payment-schedules.create', 'payment-schedules.edit',
        'sales-progressive.view', 'sales-progressive.create', 'sales-progressive.edit', 'sales-progressive.delete',
        'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
        'commissions.view', 'commissions.create',
        'advance-requests.view', 'advance-requests.create',
        'mail-delivery.view', 'mail-delivery.create',
        'rankings.view', 'statistics.view', 'reports.view', 'reports-sales.view',
        'internal-docs.view',
        'daily-reports.view', 'daily-reports.create', 'daily-reports.edit',
        'marketing-reports.view',
    ];

    private array $tpkdPermissions = [
        'customers.view', 'customers.create', 'customers.edit',
        'handlers.view', 'handlers.create', 'handlers.edit',
        'contracts-waste.view', 'contracts-waste.create', 'contracts-waste.edit', 'contracts-waste.delete',
        'contracts-consulting.view', 'contracts-consulting.create', 'contracts-consulting.edit', 'contracts-consulting.delete',
        'contracts-project.view', 'contracts-project.create', 'contracts-project.edit', 'contracts-project.delete',
        'contracts-commercial.view', 'contracts-commercial.create', 'contracts-commercial.edit', 'contracts-commercial.delete',
        'contracts-sustainability.view', 'contracts-sustainability.create', 'contracts-sustainability.edit', 'contracts-sustainability.delete',
        'contracts-energy.view', 'contracts-energy.create', 'contracts-energy.edit', 'contracts-energy.delete',
        'payment-schedules.view', 'payment-schedules.create', 'payment-schedules.edit', 'payment-schedules.delete',
        'sales-progressive.view', 'sales-progressive.create', 'sales-progressive.edit', 'sales-progressive.delete',
        'quotation-tracking.view', 'quotation-tracking.create', 'quotation-tracking.edit', 'quotation-tracking.delete',
        'commissions.view', 'commissions.create', 'commissions.edit',
        'advance-requests.view', 'advance-requests.create',
        'cash-flow.view',
        'mail-delivery.view', 'mail-delivery.create', 'mail-delivery.edit',
        'rankings.view', 'statistics.view', 'reports.view', 'reports-sales.view',
        'internal-docs.view',
        'daily-reports.view', 'daily-reports.view-all', 'daily-reports.create', 'daily-reports.edit',
        'marketing-reports.view', 'marketing-reports.view-all',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Ensure all permissions exist in database
        $allPermissions = array_unique(array_merge($this->kdPermissions, $this->tpkdPermissions));
        foreach ($allPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Assign permissions to kinh-doanh role
        $roleKd = Role::where('name', 'kinh-doanh')->first();
        if ($roleKd) {
            $roleKd->givePermissionTo($this->kdPermissions);
        }

        // 3. Assign permissions to tp-kinh-doanh role
        $roleTpkd = Role::where('name', 'tp-kinh-doanh')->first();
        if ($roleTpkd) {
            $roleTpkd->givePermissionTo($this->tpkdPermissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // No down migration logic needed for syncing permissions as it does not break anything.
    }
};
