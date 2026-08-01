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

class QuotationServiceSelectionTest extends TestCase
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

    public function test_multiple_preset_services_can_be_selected_and_saved(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::TP_KINH_DOANH->value);
        $user->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_CREATE->value);

        $this->actingAs($user);

        Livewire::test(QuotationManager::class)
            ->set('formData.date', '2026-08-01')
            ->set('formData.staff_id', $user->id)
            ->set('formData.company_name', 'Công ty TNHH Bảo Châu Test')
            ->set('formData.status', QuotationStatus::DANG_THEO_DOI->value)
            ->set('selectedServices', ['Quan trắc môi trường lao động', 'Ứng phó sự cố'])
            ->call('save')
            ->assertHasNoErrors();

        $quotation = Quotation::query()->firstOrFail();
        $this->assertEquals('Quan trắc môi trường lao động, Ứng phó sự cố', $quotation->service);
    }

    public function test_combination_of_preset_and_custom_services_is_saved(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::GIAM_DOC->value);
        $user->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_CREATE->value);

        $this->actingAs($user);

        Livewire::test(QuotationManager::class)
            ->set('formData.date', '2026-08-01')
            ->set('formData.staff_id', $user->id)
            ->set('formData.company_name', 'Công ty TNHH ABC')
            ->set('formData.status', QuotationStatus::DANG_THEO_DOI->value)
            ->set('selectedServices', ['Quan trắc môi trường', 'Giảm phát thải'])
            ->set('hasCustomService', true)
            ->set('customServiceText', 'Tư vấn chứng nhận ISO')
            ->call('save')
            ->assertHasNoErrors();

        $quotation = Quotation::query()->firstOrFail();
        $this->assertEquals('Quan trắc môi trường, Giảm phát thải, Tư vấn chứng nhận ISO', $quotation->service);
    }

    public function test_editing_existing_quotation_populates_selected_services_and_custom_text(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::TP_KINH_DOANH->value);
        $user->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_EDIT->value);

        $quotation = Quotation::create([
            'date' => '2026-08-01',
            'staff_id' => $user->id,
            'company_name' => 'Công ty XYZ',
            'status' => QuotationStatus::DANG_THEO_DOI->value,
            'service' => 'Báo cáo công tác bảo vệ môi trường, Phân loại lao động, Dịch vụ đặc biệt',
        ]);

        $this->actingAs($user);

        Livewire::test(QuotationManager::class)
            ->call('edit', $quotation->id)
            ->assertSet('selectedServices', ['Báo cáo công tác bảo vệ môi trường', 'Phân loại lao động'])
            ->assertSet('hasCustomService', true)
            ->assertSet('customServiceText', 'Dịch vụ đặc biệt');
    }

    public function test_unchecking_custom_service_clears_custom_text(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::TP_KINH_DOANH->value);
        $user->givePermissionTo(PermissionEnum::QUOTATION_TRACKING_CREATE->value);

        $this->actingAs($user);

        Livewire::test(QuotationManager::class)
            ->set('hasCustomService', true)
            ->set('customServiceText', 'Tự nhập thêm')
            ->set('hasCustomService', false)
            ->assertSet('customServiceText', '');
    }
}
