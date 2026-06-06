<?php

namespace Tests\Feature\Livewire\Admin\Quotations;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuotationManagerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }

        foreach (PermissionEnum::cases() as $permissionEnum) {
            Permission::findOrCreate($permissionEnum->value);
        }

        Role::findByName(RoleEnum::GIAM_DOC->value)
            ->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_VIEW->value);
    }

    public function test_director_can_view_quotation_tracking_page(): void
    {
        $director = User::factory()->create(['is_active' => true]);
        $director->assignRole(RoleEnum::GIAM_DOC->value);

        $this->actingAs($director);

        $this->get(route('app.quotation-tracking.index'))
            ->assertOk()
            ->assertSee('Theo d', false);
    }

    public function test_user_without_quotation_permission_cannot_view_quotation_tracking_page(): void
    {
        $accountant = User::factory()->create(['is_active' => true]);
        $accountant->assignRole(RoleEnum::KE_TOAN->value);

        $this->actingAs($accountant);

        $this->get(route('app.quotation-tracking.index'))
            ->assertStatus(403);
    }
}
