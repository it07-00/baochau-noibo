<?php

namespace Tests\Feature\Livewire\Admin\Contracts;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Contracts\ContractSustainabilityManager;
use App\Models\ContractAssignment;
use App\Models\ContractSustainability;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ContractSustainabilityManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Department $dept;

    private Customer $customer;

    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        // Create department required by ensureDepartmentId
        $this->dept = Department::firstOrCreate(
            ['slug' => 'kinh-doanh'],
            ['name' => 'Phòng Kinh Doanh']
        );

        // Clear Spatie permission cache
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed roles & permissions
        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }
        $role = Role::findByName(RoleEnum::IT->value);
        foreach (PermissionEnum::cases() as $perm) {
            Permission::findOrCreate($perm->value);
        }
        $role->syncPermissions(Permission::all());

        // Create standard admin user
        $this->adminUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $this->dept->id,
        ]);
        $this->adminUser->assignRole($role);

        // Create defaults for testing
        $this->customer = Customer::create(['name' => 'Khách hàng Phát triển bền vững']);
        $this->handler = Handler::create(['name' => 'Nhà thầu phụ Phát triển bền vững']);
    }

    public function test_can_render_component_and_list_contracts(): void
    {
        $contract = ContractSustainability::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'revenue' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ContractSustainabilityManager::class)
            ->assertStatus(200)
            ->assertSee($this->customer->name)
            ->assertSee('50,000,000');
    }

    public function test_assigned_consultant_can_open_sustainability_workflow_from_contract_list(): void
    {
        $consultingRole = Role::findByName(RoleEnum::TU_VAN->value);
        $consultingRole->syncPermissions([PermissionEnum::CONTRACTS_SUSTAINABILITY_VIEW->value]);

        $consultant = User::factory()->create(['is_active' => true]);
        $consultant->assignRole($consultingRole);

        $contract = ContractSustainability::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
        ]);

        ContractAssignment::create([
            'assignable_type' => ContractSustainability::class,
            'assignable_id' => $contract->id,
            'user_id' => $consultant->id,
            'assigned_by' => $this->adminUser->id,
        ]);

        $this->actingAs($consultant);

        Livewire::test(ContractSustainabilityManager::class)
            ->assertSeeHtml('wire:click="openWorkflow('.$contract->id.')"');
    }

    public function test_can_search_contracts(): void
    {
        $customerB = Customer::create(['name' => 'Khách hàng PTBV B']);

        $contractA = ContractSustainability::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 10000000,
            'revenue' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $contractB = ContractSustainability::create([
            'customer_id' => $customerB->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 20000000,
            'revenue' => 20000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ContractSustainabilityManager::class)
            ->set('search', 'Khách hàng PTBV B')
            ->assertViewHas('docs', function ($docs) use ($contractB, $contractA) {
                return $docs->contains($contractB) && ! $docs->contains($contractA);
            });
    }

    public function test_can_create_contract(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ContractSustainabilityManager::class)
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSet('isEditing', false)
            ->set('formData.customer_id', $this->customer->id)
            ->set('formData.handler_id', $this->handler->id)
            ->set('formData.department_id', $this->dept->id)
            ->set('formData.value', '10.000.000')
            ->set('formData.signed_at', '2026-06-06')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('contract_sustainabilities', [
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'value' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);
    }

    public function test_can_edit_and_update_contract(): void
    {
        $contract = ContractSustainability::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ContractSustainabilityManager::class)
            ->call('edit', $contract->id)
            ->assertSet('showModal', true)
            ->assertSet('isEditing', true)
            ->set('formData.value', '60.000.000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertEquals(60000000, $contract->refresh()->value);
    }

    public function test_can_update_status(): void
    {
        $contract = ContractSustainability::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ContractSustainabilityManager::class)
            ->call('updateStatus', $contract->id, 'Đang trình BGĐ ký')
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Đã cập nhật tình trạng!',
            ]);

        $this->assertEquals('Đang trình BGĐ ký', $contract->refresh()->status);
    }

    public function test_can_delete_contract(): void
    {
        $contract = ContractSustainability::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(ContractSustainabilityManager::class)
            ->call('delete', $contract->id)
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Đã xóa hợp đồng!',
            ]);

        $this->assertSoftDeleted('contract_sustainabilities', ['id' => $contract->id]);
    }
}
