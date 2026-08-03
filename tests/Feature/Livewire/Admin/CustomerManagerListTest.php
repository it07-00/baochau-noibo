<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Customers\CustomerListManager;
use App\Livewire\Admin\Customers\CustomerManager;
use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerRegulatoryListImporter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerManagerListTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_customer_directory_and_imported_customer_lists_are_separate(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo([
            Permission::findOrCreate(PermissionEnum::CUSTOMERS_VIEW->value),
            Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_VIEW->value),
        ]);

        Customer::create(['name' => 'Khách hàng thông thường']);
        Customer::create(['name' => 'Khách hàng thuộc danh sách KKKNK', 'province' => 'Tây Ninh', 'is_ghg_inventory' => true]);
        Customer::create(['name' => 'Khách hàng thuộc danh sách KTNL', 'is_energy_audit' => true]);

        Livewire::actingAs($viewer)
            ->test(CustomerManager::class)
            ->assertSee('Danh sách khách hàng')
            ->assertSee('Khách hàng thông thường')
            ->assertDontSee('Khách hàng thuộc danh sách KKKNK')
            ->assertDontSee('Khách hàng thuộc danh sách KTNL')
            ->assertDontSee('Nhóm danh sách khách hàng');

        Livewire::actingAs($viewer)
            ->test(CustomerListManager::class, ['customerListType' => 'ghg_inventory'])
            ->assertSee('Dữ liệu khách hàng KKKNK')
            ->assertViewHas('filterProvinces', fn ($provinces) => $provinces->contains('Tây Ninh'))
            ->assertSee('Khách hàng thuộc danh sách KKKNK')
            ->assertDontSee('Khách hàng thông thường')
            ->assertDontSee('Khách hàng thuộc danh sách KTNL');

        Livewire::actingAs($viewer)
            ->test(CustomerListManager::class, ['customerListType' => 'energy_audit'])
            ->assertSee('Dữ liệu khách hàng KTNL')
            ->assertSee('Khách hàng thuộc danh sách KTNL')
            ->assertDontSee('Khách hàng thuộc danh sách KKKNK');
    }

    public function test_it_filters_imported_customers_by_sector_and_appendix(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_VIEW->value));

        Customer::create([
            'name' => 'Khách hàng xi măng phụ lục I',
            'is_ghg_inventory' => true,
            'sector' => 'Sản xuất xi măng',
            'appendix' => 'Phụ lục I',
        ]);
        Customer::create([
            'name' => 'Khách hàng xi măng phụ lục II',
            'is_ghg_inventory' => true,
            'sector' => 'Sản xuất xi măng',
            'appendix' => 'Phụ lục II',
        ]);
        Customer::create([
            'name' => 'Khách hàng thép phụ lục I',
            'is_ghg_inventory' => true,
            'sector' => 'Sản xuất thép',
            'appendix' => 'Phụ lục I',
        ]);

        Livewire::actingAs($viewer)
            ->test(CustomerListManager::class, ['customerListType' => 'ghg_inventory'])
            ->assertViewHas('sectorOptions', fn ($options) => $options->contains('Sản xuất xi măng') && $options->contains('Sản xuất thép'))
            ->assertViewHas('appendixOptions', fn ($options) => $options->contains('Phụ lục I') && $options->contains('Phụ lục II'))
            ->set('sectorFilter', 'Sản xuất xi măng')
            ->assertSee('Khách hàng xi măng phụ lục I')
            ->assertSee('Khách hàng xi măng phụ lục II')
            ->assertDontSee('Khách hàng thép phụ lục I')
            ->set('appendixFilter', 'Phụ lục I')
            ->assertSee('Khách hàng xi măng phụ lục I')
            ->assertDontSee('Khách hàng xi măng phụ lục II')
            ->assertViewHas('summary', fn (array $summary) => $summary['customers'] === 1)
            ->call('resetFilters')
            ->assertSet('sectorFilter', '')
            ->assertSet('appendixFilter', '');
    }

    public function test_customer_list_routes_use_their_own_permission_and_sidebar_menu(): void
    {
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->givePermissionTo(Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_VIEW->value));

        $this->actingAs($viewer)
            ->get(route('app.customer-lists.ghg-inventory'))
            ->assertOk()
            ->assertSee('Dữ liệu khách hàng')
            ->assertSee('KH KKKNK')
            ->assertSee('KH KTNL')
            ->assertDontSee(route('app.customers.index'), false);

        $this->actingAs(User::factory()->create(['is_active' => true]))
            ->get(route('app.customer-lists.energy-audit'))
            ->assertForbidden();
    }

    public function test_each_directory_rejects_records_from_the_other_scope(): void
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo([
            Permission::findOrCreate(PermissionEnum::CUSTOMERS_VIEW->value),
            Permission::findOrCreate(PermissionEnum::CUSTOMERS_EDIT->value),
            Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_VIEW->value),
            Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_EDIT->value),
        ]);

        $customer = Customer::create(['name' => 'Khách hàng độc lập']);
        $listedCustomer = Customer::create(['name' => 'Khách hàng thuộc dữ liệu KKKNK', 'is_ghg_inventory' => true]);

        $attempts = [
            fn () => Livewire::actingAs($editor)
                ->test(CustomerManager::class)
                ->call('openEdit', $listedCustomer->id),
            fn () => Livewire::actingAs($editor)
                ->test(CustomerListManager::class, ['customerListType' => 'ghg_inventory'])
                ->call('openEdit', $customer->id),
        ];

        foreach ($attempts as $attempt) {
            try {
                $attempt();
                $this->fail('Thao tác ngoài phạm vi phải bị từ chối.');
            } catch (ModelNotFoundException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_customer_contact_details_can_be_saved_with_a_sales_caretaker(): void
    {
        $creator = User::factory()->create();
        $creator->givePermissionTo([
            Permission::findOrCreate(PermissionEnum::CUSTOMERS_VIEW->value),
            Permission::findOrCreate(PermissionEnum::CUSTOMERS_CREATE->value),
        ]);

        $salesRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        $salesCaretaker = User::factory()->create(['name' => 'Nhân viên chăm sóc', 'is_active' => true]);
        $salesCaretaker->assignRole($salesRole);

        Livewire::actingAs($creator)
            ->test(CustomerManager::class)
            ->call('openCreate')
            ->set('formData.name', 'Khách hàng có thông tin liên hệ')
            ->set('formData.phone', '0909 123 456')
            ->set('formData.email', 'lienhe@example.com')
            ->set('formData.contact_person', 'Nguyễn Văn Liên Hệ')
            ->set('formData.caretaker_id', $salesCaretaker->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('0909 123 456')
            ->assertSee('lienhe@example.com')
            ->assertSee('Nguyễn Văn Liên Hệ')
            ->assertSee('Nhân viên chăm sóc');

        $this->assertDatabaseHas('customers', [
            'name' => 'Khách hàng có thông tin liên hệ',
            'phone' => '0909 123 456',
            'email' => 'lienhe@example.com',
            'contact_person' => 'Nguyễn Văn Liên Hệ',
            'caretaker_id' => $salesCaretaker->id,
        ]);
    }

    public function test_import_command_merges_customers_that_belong_to_both_regulatory_lists(): void
    {
        $ghgPath = storage_path('app/test-ghg-customers.csv');
        $energyPath = storage_path('app/test-energy-customers.csv');

        $ghg = fopen($ghgPath, 'wb');
        fputcsv($ghg, ['STT Tổng hợp', 'STT', 'Tên cơ sở', 'Địa chỉ', 'Ngành nghề/ Loại hình kinh doanh', 'Tiêu thụ năng lượng (TOE) / Công suất', 'Tỉnh / Thành phố', 'Vùng', 'Ngành / Lĩnh vực', 'Phụ lục']);
        fputcsv($ghg, [1, 1, 'Công ty thuộc cả hai danh sách', 'KCN Đông An, tỉnh Bình Dương', '', '', 'Tỉnh Bình Dương', '', '', '']);
        fputcsv($ghg, [2, 2, 'Công ty chỉ thuộc KKKNK', 'Tỉnh Long An', '', '', 'Tỉnh Long An', '', '', '']);
        fclose($ghg);

        $energy = fopen($energyPath, 'wb');
        fputcsv($energy, ['STT', 'Tên cơ sở', 'Địa chỉ', 'Lĩnh vực', 'Ngành nghề sản xuất', 'Tiêu thụ NL quy đổi (TOE)', 'Ghi chú']);
        fputcsv($energy, ['1. Tỉnh Bình Dương', '', '', '', '', '', '']);
        fputcsv($energy, [1, 'Công ty thuộc cả hai danh sách', 'KCN Đông An', '', '', '', '']);
        fputcsv($energy, [2, 'Công ty chỉ thuộc năng lượng', 'Thành phố Thủ Dầu Một', '', '', '', '']);
        fclose($energy);

        try {
            app(CustomerRegulatoryListImporter::class)->import($ghgPath, $energyPath);
        } finally {
            @unlink($ghgPath);
            @unlink($energyPath);
        }

        $this->assertDatabaseHas('customers', [
            'name' => 'Công ty thuộc cả hai danh sách',
            'is_ghg_inventory' => true,
            'is_energy_audit' => true,
            'province' => 'TP. Hồ Chí Minh',
        ]);
        $this->assertDatabaseHas('customers', [
            'name' => 'Công ty chỉ thuộc KKKNK',
            'is_ghg_inventory' => true,
            'is_energy_audit' => false,
        ]);
        $this->assertDatabaseHas('customers', [
            'name' => 'Công ty chỉ thuộc năng lượng',
            'is_ghg_inventory' => false,
            'is_energy_audit' => true,
        ]);
        $this->assertDatabaseCount('customers', 3);
    }

    public function test_non_sales_users_cannot_be_assigned_as_customer_caretakers(): void
    {
        $creator = User::factory()->create();
        $creator->givePermissionTo([
            Permission::findOrCreate(PermissionEnum::CUSTOMERS_VIEW->value),
            Permission::findOrCreate(PermissionEnum::CUSTOMERS_CREATE->value),
        ]);

        $technicalRole = Role::findOrCreate(RoleEnum::KY_THUAT->value);
        $technicalUser = User::factory()->create(['name' => 'Nhân viên kỹ thuật', 'is_active' => true]);
        $technicalUser->assignRole($technicalRole);

        Livewire::actingAs($creator)
            ->test(CustomerManager::class)
            ->assertViewHas('caretakerOptions', fn ($options) => ! $options->contains('id', $technicalUser->id))
            ->set('formData.name', 'Khách hàng phân công sai vai trò')
            ->set('formData.caretaker_id', $technicalUser->id)
            ->call('save')
            ->assertHasErrors(['formData.caretaker_id']);

        $this->assertDatabaseMissing('customers', ['name' => 'Khách hàng phân công sai vai trò']);
    }

    public function test_reimport_keeps_manually_maintained_customer_contact_data(): void
    {
        Customer::create([
            'name' => 'Công ty đã được chăm sóc',
            'address' => 'Địa chỉ đã xác minh',
            'phone' => '028 1234 5678',
            'email' => 'daxacminh@example.com',
            'contact_person' => 'Người liên hệ đã xác minh',
        ]);

        $ghgPath = storage_path('app/test-ghg-reimport.csv');
        $energyPath = storage_path('app/test-energy-reimport.csv');

        $ghg = fopen($ghgPath, 'wb');
        fputcsv($ghg, ['STT Tổng hợp', 'STT', 'Tên cơ sở', 'Địa chỉ', 'Ngành nghề/ Loại hình kinh doanh', 'Tiêu thụ năng lượng (TOE) / Công suất', 'Tỉnh / Thành phố', 'Vùng', 'Ngành / Lĩnh vực', 'Phụ lục']);
        fputcsv($ghg, [1, 1, 'Công ty đã được chăm sóc', 'Địa chỉ từ CSV', '', '', 'Tỉnh Long An', '', '', '']);
        fclose($ghg);

        $energy = fopen($energyPath, 'wb');
        fputcsv($energy, ['STT', 'Tên cơ sở', 'Địa chỉ', 'Lĩnh vực', 'Ngành nghề sản xuất', 'Tiêu thụ NL quy đổi (TOE)', 'Ghi chú']);
        fclose($energy);

        try {
            foreach ([1, 2] as $run) {
                app(CustomerRegulatoryListImporter::class)->import($ghgPath, $energyPath);
            }
        } finally {
            @unlink($ghgPath);
            @unlink($energyPath);
        }

        $this->assertDatabaseHas('customers', [
            'name' => 'Công ty đã được chăm sóc',
            'address' => 'Địa chỉ đã xác minh',
            'phone' => '028 1234 5678',
            'email' => 'daxacminh@example.com',
            'contact_person' => 'Người liên hệ đã xác minh',
            'is_ghg_inventory' => true,
        ]);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_sales_user_can_update_care_status_for_imported_customer(): void
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo([
            Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_VIEW->value),
            Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_EDIT->value),
        ]);

        $facility = Customer::create([
            'name' => 'Khách hàng KKKNK cần chăm sóc',
            'is_ghg_inventory' => true,
        ]);

        Livewire::actingAs($editor)
            ->test(CustomerListManager::class, ['customerListType' => 'ghg_inventory'])
            ->call('updateCareStatus', $facility->id, 'contacted')
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('customers', [
            'id' => $facility->id,
            'care_status' => 'contacted',
        ]);
    }

    public function test_it_filters_imported_customers_by_care_status(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_VIEW->value));

        Customer::create(['name' => 'Khách hàng chưa liên hệ', 'is_ghg_inventory' => true, 'care_status' => 'not_contacted']);
        Customer::create(['name' => 'Khách hàng đã liên hệ', 'is_ghg_inventory' => true, 'care_status' => 'contacted']);
        Customer::create(['name' => 'Khách hàng đang đàm phán', 'is_ghg_inventory' => true, 'care_status' => 'in_progress']);

        Livewire::actingAs($viewer)
            ->test(CustomerListManager::class, ['customerListType' => 'ghg_inventory'])
            ->set('careStatusFilter', 'contacted')
            ->assertSee('Khách hàng đã liên hệ')
            ->assertDontSee('Khách hàng chưa liên hệ')
            ->assertDontSee('Khách hàng đang đàm phán');
    }

    public function test_it_filters_imported_customers_strictly_by_caretaker(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(Permission::findOrCreate(PermissionEnum::CUSTOMER_LISTS_VIEW->value));

        $salesRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        $sanSan = User::factory()->create(['name' => 'San San', 'is_active' => true]);
        $sanSan->assignRole($salesRole);

        Customer::create([
            'name' => 'Khách hàng được San San chăm sóc',
            'is_ghg_inventory' => true,
            'caretaker_id' => $sanSan->id,
        ]);

        Customer::create([
            'name' => 'Khách hàng chưa được phân công',
            'is_ghg_inventory' => true,
            'caretaker_id' => null,
        ]);

        Livewire::actingAs($viewer)
            ->test(CustomerListManager::class, ['customerListType' => 'ghg_inventory'])
            ->set('staffFilter', (string) $sanSan->id)
            ->assertSee('Khách hàng được San San chăm sóc')
            ->assertDontSee('Khách hàng chưa được phân công');
    }
}
