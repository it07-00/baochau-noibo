<?php

namespace Tests\Feature;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Handlers\HandlerContractsView;
use App\Models\ContractLegal;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HandlerContractsViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sums_and_displays_subcontractor_commission_instead_of_contract_value(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        $role = Role::findOrCreate(RoleEnum::IT->value);
        $role->syncPermissions(Permission::all());

        $department = Department::create(['name' => 'Kinh doanh', 'slug' => 'kinh-doanh']);
        $user = User::factory()->create([
            'is_active' => true,
            'department_id' => $department->id,
        ]);
        $user->assignRole($role);

        $handler = Handler::create(['name' => 'Nhà thầu phụ An Phát']);
        $otherHandler = Handler::create(['name' => 'Nhà thầu phụ khác']);
        $customer = Customer::create(['name' => 'Khách hàng kiểm thử']);

        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $user->id,
            'department_id' => $department->id,
            'shd_cxl' => 'NTP-001',
            'value' => 50_000_000,
            'commission' => 1_250_000,
            'signed_at' => '2026-07-01',
        ]);

        ContractLegal::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $user->id,
            'department_id' => $department->id,
            'shd_cxl' => 'NTP-002',
            'value' => 70_000_000,
            'commission' => 1_750_000,
            'signed_at' => '2026-07-02',
        ]);

        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $otherHandler->id,
            'staff_id' => $user->id,
            'department_id' => $department->id,
            'value' => 90_000_000,
            'commission' => 9_000_000,
            'signed_at' => '2026-07-03',
        ]);

        $this->actingAs($user);

        Livewire::test(HandlerContractsView::class, ['handler' => $handler])
            ->assertStatus(200)
            ->assertViewHas('totalCommission', 3_000_000.0)
            ->assertViewHas('totalContracts', 2)
            ->assertSee('3.000.000đ')
            ->assertSee('1.250.000')
            ->assertSee('1.750.000')
            ->assertDontSee('50.000.000')
            ->assertDontSee('70.000.000')
            ->assertDontSee('9.000.000');
    }
}
