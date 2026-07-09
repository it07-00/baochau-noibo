<?php

namespace Tests\Feature\Livewire\Admin\Reports;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Reports\Sales\SalesProjectProgressReport;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SalesProjectProgressReportTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed basic permissions and roles
        Permission::findOrCreate('reports-sales.view');
        
        $kdRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        $kdRole->givePermissionTo('reports-sales.view');

        $tpkdRole = Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value);
        $tpkdRole->givePermissionTo('reports-sales.view');

        Role::findOrCreate(RoleEnum::TU_VAN->value);
        Role::findOrCreate(RoleEnum::KY_THUAT->value);

        // Create a department
        $this->department = Department::create([
            'name' => 'Kinh doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
    }

    public function test_unauthorized_user_cannot_access_project_progress_report(): void
    {
        $user = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('app.reports.sales.project-progress'));
        $response->assertStatus(403);
    }

    public function test_salesperson_only_sees_own_contracts(): void
    {
        $salesperson1 = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $salesperson1->assignRole(RoleEnum::KINH_DOANH->value);

        $salesperson2 = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $salesperson2->assignRole(RoleEnum::KINH_DOANH->value);

        $customer = Customer::create(['name' => 'Test Customer']);
        $handler = Handler::create(['name' => 'Test Handler']);

        // Contract 1: Belonging to salesperson 1
        $contract1 = ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson1->id,
            'department_id' => $this->department->id,
            'value' => 100_000_000,
            'signed_at' => '2026-05-10',
            'submitted_at' => '2026-05-10',
        ]);

        // Contract 2: Belonging to salesperson 2
        $contract2 = ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson2->id,
            'department_id' => $this->department->id,
            'value' => 150_000_000,
            'signed_at' => '2026-05-12',
            'submitted_at' => '2026-05-12',
        ]);

        // When logged in as salesperson 1
        $this->actingAs($salesperson1);

        Livewire::test(SalesProjectProgressReport::class)
            ->set('year', 2026)
            ->assertViewHas('items', function ($items) use ($contract1, $contract2) {
                $ids = collect($items->items())->pluck('id');
                return $ids->contains($contract1->id) && !$ids->contains($contract2->id);
            });
    }

    public function test_sales_manager_can_see_all_contracts(): void
    {
        $salesperson = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $salesperson->assignRole(RoleEnum::KINH_DOANH->value);

        $manager = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $manager->assignRole(RoleEnum::TP_KINH_DOANH->value);

        $customer = Customer::create(['name' => 'Test Customer']);
        $handler = Handler::create(['name' => 'Test Handler']);

        $contract1 = ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson->id,
            'department_id' => $this->department->id,
            'value' => 100_000_000,
            'signed_at' => '2026-05-10',
            'submitted_at' => '2026-05-10',
        ]);

        $contract2 = ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $manager->id,
            'department_id' => $this->department->id,
            'value' => 150_000_000,
            'signed_at' => '2026-05-12',
            'submitted_at' => '2026-05-12',
        ]);

        // When logged in as manager
        $this->actingAs($manager);

        Livewire::test(SalesProjectProgressReport::class)
            ->set('year', 2026)
            ->assertViewHas('items', function ($items) use ($contract1, $contract2) {
                $ids = collect($items->items())->pluck('id');
                return $ids->contains($contract1->id) && $ids->contains($contract2->id);
            });
    }

    public function test_assigned_staff_filter_works(): void
    {
        $manager = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $manager->assignRole(RoleEnum::TP_KINH_DOANH->value);

        $staff1 = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $staff1->assignRole(RoleEnum::TU_VAN->value);

        $staff2 = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $staff2->assignRole(RoleEnum::KY_THUAT->value);

        $customer = Customer::create(['name' => 'Test Customer']);
        $handler = Handler::create(['name' => 'Test Handler']);

        $contract1 = ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $manager->id,
            'department_id' => $this->department->id,
            'value' => 100_000_000,
            'signed_at' => '2026-05-10',
            'submitted_at' => '2026-05-10',
        ]);
        $contract1->assignments()->create([
            'user_id' => $staff1->id,
            'assigned_by' => $manager->id,
        ]);

        $contract2 = ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $manager->id,
            'department_id' => $this->department->id,
            'value' => 150_000_000,
            'signed_at' => '2026-05-12',
            'submitted_at' => '2026-05-12',
        ]);
        $contract2->assignments()->create([
            'user_id' => $staff2->id,
            'assigned_by' => $manager->id,
        ]);

        $this->actingAs($manager);

        // Filter by staff 1
        Livewire::test(SalesProjectProgressReport::class)
            ->set('year', 2026)
            ->set('filter_staff_id', $staff1->id)
            ->assertViewHas('items', function ($items) use ($contract1, $contract2) {
                $ids = collect($items->items())->pluck('id');
                return $ids->contains($contract1->id) && !$ids->contains($contract2->id);
            });
    }
}
