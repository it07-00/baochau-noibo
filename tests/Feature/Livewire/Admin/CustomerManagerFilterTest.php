<?php

namespace Tests\Feature\Livewire\Admin;

use App\Livewire\Admin\Customers\CustomerManager;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerManagerFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_detects_the_new_region_fields_from_the_address(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('customers.view'));

        Livewire::actingAs($user)
            ->test(CustomerManager::class)
            ->set(
                'formData.address',
                'Ô 04, Khu công nghiệp Đông An, Phường Bình Hòa, TP Hồ Chí Minh'
            )
            ->call('detectAddressRegion')
            ->assertSet('formData.province', 'TP. Hồ Chí Minh')
            ->assertSet('formData.ward', 'Phường Bình Hòa')
            ->assertSet('formData.industrial_park', 'Khu công nghiệp Đông An');
    }

    public function test_it_filters_customers_by_new_region_and_service_and_shows_matching_counts(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('customers.view'));

        $department = Department::create([
            'name' => 'Kinh doanh',
            'slug' => 'kinh-doanh-customer-test',
            'is_active' => true,
        ]);
        $handler = Handler::create(['name' => 'Nhà thầu kiểm thử']);

        $matchingCustomer = Customer::create([
            'name' => 'Công ty KCN Long Hậu',
            'province' => 'Tây Ninh',
            'ward' => 'Xã Long Hậu',
            'industrial_park' => 'KCN Long Hậu',
        ]);
        Customer::create([
            'name' => 'Công ty tại Đồng Nai',
            'province' => 'Đồng Nai',
            'ward' => 'Phường Trấn Biên',
        ]);

        Quotation::create([
            'date' => '2026-07-01',
            'staff_id' => $user->id,
            'company_name' => $matchingCustomer->name,
            'service' => 'Thu gom CTNH',
        ]);
        ContractWaste::create([
            'customer_id' => $matchingCustomer->id,
            'handler_id' => $handler->id,
            'staff_id' => $user->id,
            'department_id' => $department->id,
            'loai_dich_vu' => 'Thu gom CTNH',
        ]);

        Livewire::actingAs($user)
            ->test(CustomerManager::class)
            ->set('provinceFilter', 'Tây Ninh')
            ->set('wardFilter', 'Xã Long Hậu')
            ->set('industrialParkFilter', 'KCN Long Hậu')
            ->set('serviceFilter', 'Thu gom CTNH')
            ->assertSee('Công ty KCN Long Hậu')
            ->assertDontSee('Công ty tại Đồng Nai')
            ->assertSee('1 BG')
            ->assertSee('1 HĐ')
            ->assertSee('1 hợp đồng của Công ty KCN Long Hậu');
    }

    public function test_it_normalizes_and_deduplicates_service_names_in_dropdown_and_filters_case_insensitively(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findOrCreate('customers.view'));

        $customer = Customer::create([
            'name' => 'Công ty Việt Nam',
            'province' => 'TP. Hồ Chí Minh',
        ]);

        Quotation::create([
            'date' => '2026-07-01',
            'staff_id' => $user->id,
            'company_name' => $customer->name,
            'service' => 'QTMT',
        ]);

        $department = Department::create([
            'name' => 'Kinh doanh',
            'slug' => 'kinh-doanh-service-case-test',
            'is_active' => true,
        ]);
        $handler = Handler::create(['name' => 'Nhà thầu phụ A']);

        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $user->id,
            'department_id' => $department->id,
            'loai_dich_vu' => 'quan trắc môi trường',
        ]);

        Livewire::actingAs($user)
            ->test(CustomerManager::class)
            ->assertViewHas('serviceOptions', function ($options) {
                return $options->contains('Quan trắc môi trường') && !$options->contains('QTMT') && !$options->contains('quan trắc môi trường');
            })
            ->set('serviceFilter', 'Quan trắc môi trường')
            ->assertSee('Quan trắc môi trường')
            ->assertSee('1 BG')
            ->assertSee('1 HĐ');
    }
}
