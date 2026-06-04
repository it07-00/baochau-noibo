<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'contracts-waste.view',
        'contracts-consulting.view',
        'contracts-project.view',
        'contracts-commercial.view',
        'contracts-sustainability.view',
        'contracts-energy.view',
        'payment-schedules.view',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleGd = Role::where('name', 'giam-doc')->first();
        if ($roleGd) {
            $roleGd->givePermissionTo($this->permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleGd = Role::where('name', 'giam-doc')->first();
        if ($roleGd) {
            $roleGd->revokePermissionTo($this->permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
