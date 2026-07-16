<?php

namespace Tests\Feature\Livewire\Admin\Reports;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Reports\Sales\SalesProjectProgressReport;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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

                return $ids->contains($contract1->id) && ! $ids->contains($contract2->id);
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

    public function test_service_column_uses_contract_service_instead_of_repeating_contract_group(): void
    {
        $manager = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $manager->assignRole(RoleEnum::TP_KINH_DOANH->value);

        $customer = Customer::create(['name' => 'Khách hàng dịch vụ']);
        $handler = Handler::create(['name' => 'Nhà thầu dịch vụ']);

        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $manager->id,
            'department_id' => $this->department->id,
            'loai_dich_vu' => 'Quan trắc môi trường lao động',
            'value' => 100_000_000,
            'signed_at' => '2026-05-10',
            'submitted_at' => '2026-05-10',
        ]);

        $this->actingAs($manager);

        Livewire::test(SalesProjectProgressReport::class)
            ->set('year', 2026)
            ->assertViewHas('items', function ($items): bool {
                $item = collect($items->items())->first();

                return $item['type'] === 'Quan trắc môi trường lao động'
                    && $item['contract_type'] === 'BC Chất thải';
            })
            ->assertSee('Quan trắc môi trường lao động')
            ->assertSee('BC Chất thải');
    }

    public function test_contracts_can_be_filtered_by_service_type(): void
    {
        $manager = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $manager->assignRole(RoleEnum::TP_KINH_DOANH->value);

        $handler = Handler::create(['name' => 'Nhà thầu lọc dịch vụ']);
        foreach (['Quan trắc lao động', 'Xử lý chất thải'] as $service) {
            $customer = Customer::create(['name' => 'Khách '.$service]);
            ContractWaste::create([
                'customer_id' => $customer->id,
                'handler_id' => $handler->id,
                'staff_id' => $manager->id,
                'department_id' => $this->department->id,
                'loai_dich_vu' => $service,
                'value' => 100_000_000,
                'signed_at' => '2026-05-10',
                'submitted_at' => '2026-05-10',
            ]);
        }

        $this->actingAs($manager);

        Livewire::test(SalesProjectProgressReport::class)
            ->set('year', 2026)
            ->assertViewHas('serviceOptions', fn (array $options): bool => $options === ['Quan trắc lao động', 'Xử lý chất thải'])
            ->set('filter_service', 'Xử lý chất thải')
            ->assertViewHas('items', fn ($items): bool => $items->total() === 1
                && $items->items()[0]['type'] === 'Xử lý chất thải')
            ->assertViewHas('summary', fn ($summary): bool => $summary->total === 1);
    }

    public function test_report_uses_contract_tabs_and_six_workflow_columns(): void
    {
        $manager = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $manager->assignRole(RoleEnum::TP_KINH_DOANH->value);

        $customer = Customer::create(['name' => 'Khách hàng ma trận']);
        $handler = Handler::create(['name' => 'Nhà thầu ma trận']);
        $contract = ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $manager->id,
            'department_id' => $this->department->id,
            'loai_dich_vu' => 'Quan trắc định kỳ',
            'value' => 100_000_000,
            'signed_at' => '2026-05-10',
            'submitted_at' => '2026-05-10',
        ]);
        $contract->workflowSteps()->create([
            'user_id' => $manager->id,
            'step_name' => 'receiving',
            'action' => 'completed',
        ]);

        $this->actingAs($manager);

        Livewire::test(SalesProjectProgressReport::class)
            ->set('year', 2026)
            ->assertSet('filter_contract_type', 'waste')
            ->assertViewHas('items', function ($items): bool {
                $steps = collect($items->items())->first()['workflow_steps'];

                return count($steps) === 6
                    && $steps[0]['state'] === 'completed'
                    && $steps[1]['state'] === 'current'
                    && $steps[5]['key'] === ContractWorkflowStep::STEP_KEYS[5];
            })
            ->assertViewHas('pipeline', function (array $pipeline): bool {
                return count($pipeline) === 6
                    && count($pipeline['survey']) === 1
                    && $pipeline['survey'][0]['customer'] === 'Khách hàng ma trận';
            })
            ->assertSee('Nhóm hợp đồng')
            ->assertSee('Pipeline tiến độ hợp đồng')
            ->assertSee('Đang thực hiện');
    }

    public function test_pipeline_paginates_ten_contracts_and_keeps_summary_for_all_results(): void
    {
        $manager = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $manager->assignRole(RoleEnum::TP_KINH_DOANH->value);

        $handler = Handler::create(['name' => 'Nhà thầu phân trang']);

        foreach (range(1, 11) as $index) {
            $customer = Customer::create(['name' => 'Khách hàng phân trang '.$index]);
            ContractWaste::create([
                'customer_id' => $customer->id,
                'handler_id' => $handler->id,
                'staff_id' => $manager->id,
                'department_id' => $this->department->id,
                'loai_dich_vu' => 'Quan trắc '.$index,
                'value' => 100_000_000,
                'signed_at' => '2026-05-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'submitted_at' => '2026-05-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $this->actingAs($manager);

        Livewire::test(SalesProjectProgressReport::class)
            ->set('year', 2026)
            ->assertViewHas('items', fn ($items): bool => $items->perPage() === 10
                && $items->count() === 10
                && $items->total() === 11)
            ->assertViewHas('summary', fn ($summary): bool => $summary->total === 11
                && $summary->not_started === 11)
            ->assertViewHas('pipeline', fn (array $pipeline): bool => collect($pipeline)->flatten(1)->count() === 10);
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

                return $ids->contains($contract1->id) && ! $ids->contains($contract2->id);
            });
    }
}
