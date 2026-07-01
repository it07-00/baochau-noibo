<?php

namespace Tests\Feature\Livewire\Admin\Quotations;

use App\Enums\Permission as PermissionEnum;
use App\Enums\QuotationStatus;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Quotations\QuotationManager;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuotationManagerExpectedSigningDateTest extends TestCase
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
    }

    public function test_expected_signing_date_can_be_saved_as_empty_string_and_persisted_as_null(): void
    {
        $salesUser = $this->createSalesUser(PermissionEnum::QUOTATION_TRACKING_CREATE);

        $this->actingAs($salesUser);

        Livewire::test(QuotationManager::class)
            ->set('formData.date', '2026-06-25')
            ->set('formData.staff_id', $salesUser->id)
            ->set('formData.company_name', 'Test Company')
            ->set('formData.status', QuotationStatus::DANG_THEO_DOI->value)
            ->set('formData.expected_signing_date', '')
            ->call('save')
            ->assertHasNoErrors();

        $quotation = Quotation::query()->firstOrFail();
        $this->assertNull($quotation->expected_signing_date);
    }

    public function test_expected_signing_date_can_be_cleared_when_updating_a_quotation(): void
    {
        $salesUser = $this->createSalesUser(PermissionEnum::QUOTATION_TRACKING_EDIT);
        $quotation = Quotation::query()->create([
            'date' => '2026-06-25',
            'expected_signing_date' => '2026-07-15',
            'staff_id' => $salesUser->id,
            'company_name' => 'Test Company',
            'status' => QuotationStatus::DANG_THEO_DOI->value,
        ]);

        $this->actingAs($salesUser);

        Livewire::test(QuotationManager::class)
            ->call('edit', $quotation->id)
            ->set('formData.expected_signing_date', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($quotation->refresh()->expected_signing_date);
    }

    private function createSalesUser(PermissionEnum $permission): User
    {
        $salesUser = User::factory()->create(['is_active' => true]);
        $salesUser->assignRole(RoleEnum::KINH_DOANH->value);
        $salesUser->givePermissionTo($permission->value);

        return $salesUser;
    }
}
