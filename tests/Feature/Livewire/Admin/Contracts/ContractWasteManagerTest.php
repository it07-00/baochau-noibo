<?php

namespace Tests\Feature\Livewire\Admin\Contracts;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
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

class ContractWasteManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Department $dept;
    private Customer $customer;
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        // Create department
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
        $adminRole = Role::findByName(RoleEnum::IT->value);

        foreach (PermissionEnum::cases() as $perm) {
            Permission::findOrCreate($perm->value);
        }
        $adminRole->syncPermissions(Permission::all());

        // Create user
        $this->adminUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $this->dept->id,
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->customer = Customer::create(['name' => 'Khách hàng Waste']);
        $this->handler = Handler::create(['name' => 'Nhà thầu phụ Waste']);
    }

    public function test_can_render_waste_manager(): void
    {
        $contract = ContractWaste::create([
            'customer_id' => $this->customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $this->adminUser->id,
            'department_id' => $this->dept->id,
            'value' => 25000000,
            'revenue' => 25000000,
            'status' => 'PTH đang kiểm tra',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(\App\Livewire\Admin\Contracts\ContractWasteManager::class)
            ->assertStatus(200)
            ->assertSee($this->customer->name)
            ->assertSee('25,000,000');
    }

    public function test_can_crud_waste_contract(): void
    {
        $this->actingAs($this->adminUser);

        // Create
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWasteManager::class)
            ->call('create')
            ->set('formData.customer_id', $this->customer->id)
            ->set('formData.handler_id', $this->handler->id)
            ->set('formData.department_id', $this->dept->id)
            ->set('formData.value', '35.000.000')
            ->set('formData.signed_at', '2026-06-01')
            ->set('formData.effective_at', '2026-06-02')
            ->set('formData.end_at', '2026-12-31')
            ->call('save')
            ->assertHasNoErrors();

        $contract = ContractWaste::first();
        $this->assertNotNull($contract);
        $this->assertEquals(35000000, $contract->value);

        // Update
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWasteManager::class)
            ->call('edit', $contract->id)
            ->set('formData.value', '37.000.000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(37000000, $contract->refresh()->value);

        // Delete
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWasteManager::class)
            ->call('delete', $contract->id);

        $this->assertSoftDeleted('contract_wastes', ['id' => $contract->id]);
    }

    public function test_effective_date_validation(): void
    {
        $this->actingAs($this->adminUser);

        // effective_at cannot be before signed_at
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWasteManager::class)
            ->call('create')
            ->set('formData.customer_id', $this->customer->id)
            ->set('formData.handler_id', $this->handler->id)
            ->set('formData.department_id', $this->dept->id)
            ->set('formData.value', '35.000.000')
            ->set('formData.signed_at', '2026-06-05')
            ->set('formData.effective_at', '2026-06-01') // Invalid: before signed_at
            ->call('save')
            ->assertHasErrors(['formData.effective_at']);
    }

    public function test_end_date_validation(): void
    {
        $this->actingAs($this->adminUser);

        // end_at cannot be before effective_at
        Livewire::test(\App\Livewire\Admin\Contracts\ContractWasteManager::class)
            ->call('create')
            ->set('formData.customer_id', $this->customer->id)
            ->set('formData.handler_id', $this->handler->id)
            ->set('formData.department_id', $this->dept->id)
            ->set('formData.value', '35.000.000')
            ->set('formData.signed_at', '2026-06-01')
            ->set('formData.effective_at', '2026-06-02')
            ->set('formData.end_at', '2026-06-01') // Invalid: before effective_at
            ->call('save')
            ->assertHasErrors(['formData.end_at']);
    }
}
