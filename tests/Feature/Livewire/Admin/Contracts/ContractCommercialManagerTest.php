<?php

namespace Tests\Feature\Livewire\Admin\Contracts;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\ContractResearch;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractCommercialManagerTest extends TestCase
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
        $this->customer = Customer::create(['name' => 'Khách hàng A']);
        $this->handler = Handler::create(['name' => 'Nhà thầu phụ A']);
    }

    public function test_can_render_component_and_list_contracts(): void
    {
        $contract = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 50000000,
            'revenue' => 50000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->assertStatus(200)
            ->assertSee($this->customer->name)
            ->assertSee('50,000,000');
    }

    public function test_can_search_contracts(): void
    {
        $customerB = Customer::create(['name' => 'Khách hàng B']);
        
        $contractA = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 10000000,
            'revenue' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $contractB = ContractResearch::create([
            'customer_id' => $customerB->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 20000000,
            'revenue' => 20000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->set('search', 'Khách hàng B')
            ->assertViewHas('docs', function($docs) use ($contractB, $contractA) {
                return $docs->contains($contractB) && !$docs->contains($contractA);
            });
    }

    public function test_can_create_contract(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->call('create')
            ->assertSet('showModal', true)
            ->assertSet('isEditing', false)
            ->set('formData.customer_id', $this->customer->id)
            ->set('formData.handler_id', $this->handler->id)
            ->set('formData.department_id', $this->dept->id)
            ->set('formData.value', '10.000.000') // cleaned by cleanMoneyFields
            ->set('formData.signed_at', '2026-06-06')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('contract_commercials', [
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'value' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);
    }

    public function test_can_edit_and_update_contract(): void
    {
        $contract = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 12000000,
            'revenue' => 12000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->call('edit', $contract->id)
            ->assertSet('isEditing', true)
            ->assertSet('showModal', true)
            ->set('formData.value', '15.000.000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(15000000, $contract->refresh()->value);
    }

    public function test_can_update_status(): void
    {
        $contract = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 12000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->call('updateStatus', $contract->id, 'Đã hoàn thành')
            ->assertDispatched('swal:toast');

        $this->assertEquals('Đã hoàn thành', $contract->refresh()->status);
        $this->assertNotNull($contract->submitted_at);
    }

    public function test_can_delete_contract(): void
    {
        $contract = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->call('delete', $contract->id)
            ->assertDispatched('swal:toast');

        $this->assertSoftDeleted('contract_commercials', ['id' => $contract->id]);
    }

    public function test_can_duplicate_contract(): void
    {
        $contract = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->call('duplicate', $contract->id)
            ->assertSet('isDuplicating', true)
            ->assertSet('showModal', true)
            ->assertSet('formData.value', 10000000)
            ->call('save')
            ->assertHasNoErrors();

        // 2 contracts should exist now
        $this->assertEquals(2, ContractResearch::count());
    }

    public function test_can_assign_work(): void
    {
        $contract = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $assignee = User::factory()->create();

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->call('openAssign', $contract->id)
            ->set('assignUserIds', [$assignee->id])
            ->set('assignExternal', 'Đối tác liên kết B')
            ->set('assignDeadline', '2026-07-01')
            ->call('saveAssign')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contract_assignments', [
            'assignable_type' => ContractResearch::class,
            'assignable_id' => $contract->id,
            'user_id' => $assignee->id,
            'deadline' => '2026-07-01 00:00:00',
        ]);

        $this->assertDatabaseHas('contract_assignments', [
            'assignable_type' => ContractResearch::class,
            'assignable_id' => $contract->id,
            'user_id' => null,
            'external_assignee' => 'Đối tác liên kết B',
            'deadline' => '2026-07-01 00:00:00',
        ]);
    }

    public function test_can_add_progress_note(): void
    {
        $contract = ContractResearch::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 10000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractCommercialManager::class)
            ->set('progressNote', 'Hợp đồng tiến triển tốt.')
            ->call('addProgressNote', $contract->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contract_progress_notes', [
            'contract_type' => 'commercial',
            'contract_id' => $contract->id,
            'user_id' => $this->adminUser->id,
            'note' => 'Hợp đồng tiến triển tốt.',
        ]);
    }
}
