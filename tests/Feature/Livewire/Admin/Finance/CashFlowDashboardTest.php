<?php

namespace Tests\Feature\Livewire\Admin\Finance;

use App\Enums\ContractType;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Finance\CashFlowDashboard;
use App\Models\ContractLegal;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CashFlowDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_accountant_can_assign_the_same_bao_chau_invoice_number_to_multiple_contracts(): void
    {
        foreach (\App\Enums\Permission::cases() as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission->value);
        }
        $accountantRole = Role::findOrCreate(RoleEnum::KE_TOAN->value);
        $accountantRole->syncPermissions(\Spatie\Permission\Models\Permission::all());
        $accountant = User::factory()->create();
        $accountant->assignRole($accountantRole);
        $customer = Customer::create(['name' => 'Khách hàng']);

        $department = \App\Models\Department::create(['name' => 'Phòng kỹ thuật', 'slug' => 'ky-thuat']);
        $firstContract = ContractLegal::create([
            'customer_id' => $customer->id,
            'staff_id' => $accountant->id,
            'department_id' => $department->id,
            'value' => 10_000_000,
            'revenue' => 10_000_000,
            'shd_bc' => '17/2026/HĐKT.BC-SAMDL',
        ]);
        $secondContract = ContractLegal::create([
            'customer_id' => $customer->id,
            'staff_id' => $accountant->id,
            'department_id' => $department->id,
            'value' => 10_000_000,
            'revenue' => 10_000_000,
        ]);

        $this->actingAs($accountant);

        Livewire::test(CashFlowDashboard::class)
            ->call(
                'updateBaoChauInvoiceNumber',
                ContractType::CONSULTING->value,
                $secondContract->id,
                $firstContract->shd_bc,
            );

        $this->assertSame(
            $firstContract->shd_bc,
            $secondContract->fresh()->shd_bc,
        );
    }
}
