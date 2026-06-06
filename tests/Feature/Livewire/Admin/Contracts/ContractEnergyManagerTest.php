<?php

namespace Tests\Feature\Livewire\Admin\Contracts;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\ContractEmission;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractEnergyManagerTest extends TestCase
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
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

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
        $this->customer = Customer::create(['name' => 'Khách hàng Năng lượng']);
        $this->handler = Handler::create(['name' => 'Nhà thầu phụ Năng lượng']);
    }

    public function test_can_render_component_and_list_contracts(): void
    {
        $contract = ContractEmission::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'revenue' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractEnergyManager::class)
            ->assertStatus(200)
            ->assertSee($this->customer->name)
            ->assertSee('50,000,000');
    }

    public function test_can_search_contracts(): void
    {
        $customerB = Customer::create(['name' => 'Khách hàng Năng lượng B']);
        
        $contractA = ContractEmission::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 10000000,
            'revenue' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $contractB = ContractEmission::create([
            'customer_id' => $customerB->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 20000000,
            'revenue' => 20000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractEnergyManager::class)
            ->set('search', 'Khách hàng Năng lượng B')
            ->assertViewHas('docs', function($docs) use ($contractB, $contractA) {
                return $docs->contains($contractB) && !$docs->contains($contractA);
            });
    }

    public function test_can_create_contract(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractEnergyManager::class)
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

        $this->assertDatabaseHas('contract_energies', [
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'value' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);
    }

    public function test_can_edit_and_update_contract(): void
    {
        $contract = ContractEmission::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractEnergyManager::class)
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
        $contract = ContractEmission::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractEnergyManager::class)
            ->call('updateStatus', $contract->id, 'Đang trình BGĐ ký')
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Đã cập nhật tình trạng!',
            ]);

        $this->assertEquals('Đang trình BGĐ ký', $contract->refresh()->status);
    }

    public function test_can_delete_contract(): void
    {
        $contract = ContractEmission::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractEnergyManager::class)
            ->call('delete', $contract->id)
            ->assertDispatched('swal:toast', [
                'type' => 'success',
                'message' => 'Đã xóa hợp đồng!',
            ]);

        $this->assertSoftDeleted('contract_energies', ['id' => $contract->id]);
    }
}
