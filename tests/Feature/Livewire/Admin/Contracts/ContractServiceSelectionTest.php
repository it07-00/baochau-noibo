<?php

namespace Tests\Feature\Livewire\Admin\Contracts;

use App\Enums\Permission as PermissionEnum;
use App\Enums\QuotationStatus;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Contracts\ContractConsultingManager;
use App\Livewire\Admin\Contracts\ContractWasteManager;
use App\Models\ContractLegal;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ContractServiceSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected Department $dept;

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

        $this->dept = Department::firstOrCreate(
            ['id' => 3],
            ['name' => 'Phòng Kinh doanh', 'slug' => 'phong-kinh-doanh']
        );
    }

    public function test_converting_quotation_to_contract_prefills_multi_selected_services(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::TP_KINH_DOANH->value);
        $user->givePermissionTo(PermissionEnum::CONTRACTS_CONSULTING_CREATE->value);

        $quotation = Quotation::create([
            'date' => '2026-08-01',
            'staff_id' => $user->id,
            'company_name' => 'Công ty TNHH Môi Trường Mới',
            'status' => QuotationStatus::DANG_THEO_DOI->value,
            'service' => 'Quan trắc môi trường lao động, Ứng phó sự cố',
            'total_value' => 50000000,
        ]);

        $this->actingAs($user);

        Livewire::withQueryParams(['quotation_id' => $quotation->id])
            ->test(ContractConsultingManager::class)
            ->assertSet('selectedServices', ['Quan trắc môi trường lao động', 'Ứng phó sự cố'])
            ->assertSet('formData.service_content', 'Quan trắc môi trường lao động, Ứng phó sự cố');
    }

    public function test_contract_multi_service_selection_and_custom_text_saving(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::GIAM_DOC->value);
        $user->givePermissionTo(PermissionEnum::CONTRACTS_WASTE_CREATE->value);

        $customer = Customer::create(['name' => 'Công ty Khách Hàng AAA']);

        $this->actingAs($user);

        Livewire::test(ContractWasteManager::class)
            ->set('formData.customer_id', $customer->id)
            ->set('formData.staff_id', $user->id)
            ->set('formData.department_id', $this->dept->id)
            ->set('formData.signed_at', '2026-08-01')
            ->set('formData.value', 20000000)
            ->set('selectedServices', ['Quan trắc môi trường', 'Phân loại lao động'])
            ->set('hasCustomService', true)
            ->set('customServiceText', 'Xử lý chất thải nguy hại')
            ->call('save')
            ->assertHasNoErrors();

        $contract = ContractWaste::query()->firstOrFail();
        $this->assertEquals('Quan trắc môi trường, Phân loại lao động, Xử lý chất thải nguy hại', $contract->service_content);
    }

    public function test_editing_contract_populates_selected_services_and_custom_text(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(RoleEnum::TP_KINH_DOANH->value);
        $user->givePermissionTo(PermissionEnum::CONTRACTS_CONSULTING_EDIT->value);

        $customer = Customer::create(['name' => 'Công ty BBB']);
        $contract = ContractLegal::create([
            'customer_id' => $customer->id,
            'staff_id' => $user->id,
            'department_id' => $this->dept->id,
            'signed_at' => '2026-08-01',
            'status' => 'PTH đang kiểm tra',
            'service_content' => 'Báo cáo công tác bảo vệ môi trường, Giảm phát thải, Đánh giá tác động',
        ]);

        $this->actingAs($user);

        Livewire::test(ContractConsultingManager::class)
            ->call('edit', $contract->id)
            ->assertSet('selectedServices', ['Báo cáo công tác bảo vệ môi trường', 'Giảm phát thải'])
            ->assertSet('hasCustomService', true)
            ->assertSet('customServiceText', 'Đánh giá tác động');
    }
}
