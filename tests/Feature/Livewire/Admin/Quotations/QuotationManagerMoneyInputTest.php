<?php

namespace Tests\Feature\Livewire\Admin\Quotations;

use App\Enums\Permission as PermissionEnum;
use App\Enums\QuotationStatus;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Quotations\QuotationManager;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class QuotationManagerMoneyInputTest extends TestCase
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

    public function test_blank_money_fields_are_saved_as_zero(): void
    {
        $salesUser = User::factory()->create(['is_active' => true]);
        $salesUser->assignRole(RoleEnum::KINH_DOANH->value);
        $salesUser->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_CREATE->value);

        $this->actingAs($salesUser);

        Livewire::test(QuotationManager::class)
            ->set('formData.date', '2026-06-25')
            ->set('formData.staff_id', $salesUser->id)
            ->set('formData.company_name', 'Benh vien Da khoa Ba Ria')
            ->set('formData.status', QuotationStatus::DANG_THEO_DOI->value)
            ->set('formData.original_value', '')
            ->set('formData.value_inc_vat', '')
            ->set('formData.commission_value', '')
            ->set('formData.commission_tax', '')
            ->set('formData.total_value', '')
            ->call('save')
            ->assertHasNoErrors();

        $quotation = Quotation::query()->firstOrFail();
        $rawValues = DB::table('quotations')
            ->where('id', $quotation->id)
            ->first([
                'original_value',
                'value_inc_vat',
                'commission_value',
                'commission_tax',
                'total_value',
            ]);

        $this->assertSame(0, $quotation->original_value);
        $this->assertSame(0, $quotation->value_inc_vat);
        $this->assertSame(0, $quotation->commission_value);
        $this->assertSame(0, $quotation->commission_tax);
        $this->assertSame(0, $quotation->total_value);
        $this->assertSame(0, $rawValues->original_value);
        $this->assertSame(0, $rawValues->value_inc_vat);
        $this->assertSame(0, $rawValues->commission_value);
        $this->assertSame(0, $rawValues->commission_tax);
        $this->assertSame(0, $rawValues->total_value);
    }

    public function test_recalculate_totals_on_original_value_change(): void
    {
        $salesUser = User::factory()->create(['is_active' => true]);
        $salesUser->assignRole(RoleEnum::KINH_DOANH->value);

        $this->actingAs($salesUser);

        Livewire::test(QuotationManager::class)
            ->set('formData.original_value', '8.888.888.000')
            ->set('formData.commission_value', '1.000.000')
            ->assertSet('formData.commission_tax', 200000)
            ->assertSet('formData.value_inc_vat', 8890088000)
            ->assertSet('formData.total_value', 9601295040);
    }

    public function test_manual_commission_tax_is_preserved_when_totals_are_recalculated(): void
    {
        $salesUser = User::factory()->create(['is_active' => true]);
        $salesUser->assignRole(RoleEnum::KINH_DOANH->value);
        $salesUser->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_CREATE->value);

        $this->actingAs($salesUser);

        Livewire::test(QuotationManager::class)
            ->set('formData.date', '2026-07-15')
            ->set('formData.staff_id', $salesUser->id)
            ->set('formData.company_name', 'Công ty nhập thuế thủ công')
            ->set('formData.status', QuotationStatus::DANG_THEO_DOI->value)
            ->set('formData.original_value', '12.785.000')
            ->set('formData.commission_value', '3.000.000')
            ->assertSet('formData.commission_tax', 900000)
            ->set('commissionTaxManual', true)
            ->set('formData.commission_tax', '750.000')
            ->assertSet('formData.commission_tax', '750.000')
            ->assertSet('formData.value_inc_vat', 16535000)
            ->assertSet('formData.total_value', 17857800)
            ->set('formData.original_value', '13.000.000')
            ->assertSet('formData.commission_tax', '750.000')
            ->assertSet('formData.value_inc_vat', 16750000)
            ->assertSet('formData.total_value', 18090000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotations', [
            'company_name' => 'Công ty nhập thuế thủ công',
            'original_value' => 13000000,
            'commission_value' => 3000000,
            'commission_tax' => 750000,
            'value_inc_vat' => 16750000,
            'total_value' => 18090000,
        ]);
    }

    public function test_disabling_manual_commission_tax_restores_automatic_calculation(): void
    {
        $salesUser = User::factory()->create(['is_active' => true]);
        $salesUser->assignRole(RoleEnum::KINH_DOANH->value);

        $this->actingAs($salesUser);

        Livewire::test(QuotationManager::class)
            ->set('formData.original_value', '12.785.000')
            ->set('formData.commission_value', '3.000.000')
            ->set('commissionTaxManual', true)
            ->set('formData.commission_tax', '750.000')
            ->set('commissionTaxManual', false)
            ->assertSet('formData.commission_tax', 900000)
            ->assertSet('formData.value_inc_vat', 16685000)
            ->assertSet('formData.total_value', 18019800);
    }

    public function test_it_can_update_status_directly(): void
    {
        $salesUser = User::factory()->create(['is_active' => true]);
        $salesUser->assignRole(RoleEnum::KINH_DOANH->value);
        $salesUser->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_EDIT->value);

        $quotation = Quotation::create([
            'date' => '2026-06-25',
            'staff_id' => $salesUser->id,
            'company_name' => 'Benh vien Da khoa Ba Ria',
            'status' => QuotationStatus::DANG_THEO_DOI->value,
            'original_value' => 0,
            'value_inc_vat' => 0,
            'commission_value' => 0,
            'commission_tax' => 0,
            'total_value' => 0,
        ]);

        Livewire::actingAs($salesUser)
            ->test(QuotationManager::class)
            ->call('updateStatus', $quotation->id, QuotationStatus::KY_HOP_DONG->value)
            ->assertHasNoErrors();

        $this->assertEquals(QuotationStatus::KY_HOP_DONG->value, $quotation->fresh()->status);
    }
}
