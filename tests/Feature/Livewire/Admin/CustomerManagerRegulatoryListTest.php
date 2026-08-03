<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Customers\CustomerManager;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerManagerRegulatoryListTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_tabs_show_the_selected_regulatory_list_only(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(Permission::findOrCreate('customers.view'));

        Customer::create(['name' => 'Khách hàng thông thường']);
        Customer::create(['name' => 'Cơ sở thuộc danh sách KKKNK', 'province' => 'Tây Ninh', 'is_ghg_inventory' => true]);
        Customer::create(['name' => 'Cơ sở thuộc danh sách năng lượng', 'is_energy_audit' => true]);

        Livewire::actingAs($viewer)
            ->test(CustomerManager::class)
            ->assertSee('Khách hàng')
            ->assertSee('KH KKKNK')
            ->assertSee('KH KIỂM TOÁN NĂNG LƯỢNG')
            ->call('selectCustomerList', 'ghg_inventory')
            ->assertViewHas('filterProvinces', fn ($provinces) => $provinces->contains('Tây Ninh'))
            ->assertSee('Cơ sở thuộc danh sách KKKNK')
            ->assertDontSee('Khách hàng thông thường')
            ->assertDontSee('Cơ sở thuộc danh sách năng lượng')
            ->call('selectCustomerList', 'energy_audit')
            ->assertSee('Cơ sở thuộc danh sách năng lượng')
            ->assertDontSee('Cơ sở thuộc danh sách KKKNK');
    }

    public function test_customer_contact_details_can_be_saved_with_a_sales_caretaker(): void
    {
        $creator = User::factory()->create();
        $creator->givePermissionTo(Permission::findOrCreate('customers.create'));

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
            $this->artisan('customers:import-regulatory-lists', [
                'ghg' => $ghgPath,
                'energy' => $energyPath,
            ])->assertSuccessful();
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
        $creator->givePermissionTo(Permission::findOrCreate('customers.create'));

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
                $this->artisan('customers:import-regulatory-lists', [
                    'ghg' => $ghgPath,
                    'energy' => $energyPath,
                ])->assertSuccessful();
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

    public function test_sales_user_can_update_care_status_for_regulatory_facility(): void
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo(Permission::findOrCreate('customers.edit'));

        $facility = Customer::create([
            'name'             => 'Cơ sở KKKNK cần chăm sóc',
            'is_ghg_inventory' => true,
        ]);

        Livewire::actingAs($editor)
            ->test(CustomerManager::class)
            ->call('selectCustomerList', 'ghg_inventory')
            ->call('updateCareStatus', $facility->id, 'contacted')
            ->assertDispatched('swal:toast');

        $this->assertDatabaseHas('customers', [
            'id'          => $facility->id,
            'care_status' => 'contacted',
        ]);
    }

    public function test_it_filters_regulatory_facilities_by_care_status(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(Permission::findOrCreate('customers.view'));

        Customer::create(['name' => 'Cơ sở chưa liên hệ',   'is_ghg_inventory' => true, 'care_status' => 'not_contacted']);
        Customer::create(['name' => 'Cơ sở đã liên hệ',      'is_ghg_inventory' => true, 'care_status' => 'contacted']);
        Customer::create(['name' => 'Cơ sở đang đàm phán',   'is_ghg_inventory' => true, 'care_status' => 'in_progress']);

        Livewire::actingAs($viewer)
            ->test(CustomerManager::class)
            ->call('selectCustomerList', 'ghg_inventory')
            ->set('careStatusFilter', 'contacted')
            ->assertSee('Cơ sở đã liên hệ')
            ->assertDontSee('Cơ sở chưa liên hệ')
            ->assertDontSee('Cơ sở đang đàm phán');
    }
}
