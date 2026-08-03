<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\StatisticsBoard;
use App\Models\ContractAssignment;
use App\Models\ContractLegal;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StatisticsBoardFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            /** @var \PDO $pdo */
            $pdo = DB::connection()->getPdo();
            if (method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction(
                    'MONTH',
                    static fn (?string $date): ?int => $date ? (int) date('n', strtotime($date)) : null
                );
            } elseif (method_exists($pdo, 'createFunction')) {
                $pdo->createFunction(
                    'MONTH',
                    static fn (?string $date): ?int => $date ? (int) date('n', strtotime($date)) : null
                );
            }
        }

        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value);
        }
    }

    public function test_dashboard_month_filter_applies_to_monthly_contract_and_sales_data(): void
    {
        $user = $this->createDashboardFixtures();

        $data = app(StatisticsService::class)->getDashboardData($user, 2026, '2');

        $this->assertSame(0.0, $data['monthly'][1]['sales']);
        $this->assertSame(200_000_000.0, $data['monthly'][2]['sales']);
        $this->assertSame(0, $data['monthly'][1]['contracts']);
        $this->assertSame(1, $data['monthly'][2]['contracts']);
    }

    public function test_dashboard_date_range_applies_to_monthly_contract_and_sales_data(): void
    {
        $user = $this->createDashboardFixtures();

        $data = app(StatisticsService::class)->getDashboardData(
            $user,
            2026,
            '',
            '2026-01-01',
            '2026-01-31'
        );

        $this->assertSame(100_000_000.0, $data['monthly'][1]['sales']);
        $this->assertSame(0.0, $data['monthly'][2]['sales']);
        $this->assertSame(1, $data['monthly'][1]['contracts']);
        $this->assertSame(0, $data['monthly'][2]['contracts']);
    }

    public function test_dashboard_always_exposes_all_twelve_months(): void
    {
        $component = new StatisticsBoard;
        $component->year = (int) now()->year;

        $this->assertSame(12, $component->maximumVisibleMonth());
    }

    public function test_consultant_progress_counts_assigned_contracts_owned_by_sales_staff(): void
    {
        $department = Department::create([
            'name' => 'Tư vấn',
            'slug' => 'tu-van',
            'is_active' => true,
        ]);
        $salesStaff = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $consultant = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $consultant->assignRole(Role::findByName(RoleEnum::TU_VAN->value));

        $customer = Customer::create(['name' => 'Khách hàng tư vấn dashboard']);
        $handler = Handler::create(['name' => 'Nhà thầu tư vấn dashboard']);

        $contracts = collect([40_000_000, 60_000_000])->map(function (int $value) use ($customer, $handler, $salesStaff, $department, $consultant) {
            $contract = ContractTechnical::create([
                'shd_bc' => '05/2026/HĐKT.BC-TIENDO',
                'customer_id' => $customer->id,
                'handler_id' => $handler->id,
                'staff_id' => $salesStaff->id,
                'department_id' => $department->id,
                'value' => $value,
                'signed_at' => now()->startOfYear()->addMonth(),
            ]);

            ContractAssignment::create([
                'assignable_type' => ContractTechnical::class,
                'assignable_id' => $contract->id,
                'user_id' => $consultant->id,
                'assigned_by' => $salesStaff->id,
            ]);

            return $contract;
        });

        ContractWorkflowStep::create([
            'contract_type' => ContractTechnical::class,
            'contract_id' => $contracts->last()->id,
            'user_id' => $consultant->id,
            'step_name' => 'finished',
            'action' => 'complete',
        ]);

        $this->actingAs($consultant);

        Livewire::test(StatisticsBoard::class)
            ->assertSet('filter_staff', (string) $consultant->id)
            ->assertViewHas('consultingSummary', fn (array $summary) => $summary['total'] === 1
                && $summary['completed'] === 1
                && $summary['processing'] === 0)
            ->assertViewHas('consultingStats', fn ($stats) => $stats->contains(
                fn (array $row) => $row['label'] === 'Ứng phó sự cố'
                    && $row['count'] === 1
                    && $row['completed'] === 1
                    && $row['processing'] === 0
            ));
    }

    public function test_technical_progress_counts_payment_rows_with_the_same_bao_chau_number_once(): void
    {
        $department = Department::create([
            'name' => 'Kỹ thuật',
            'slug' => 'ky-thuat',
            'is_active' => true,
        ]);
        $technical = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $technical->assignRole(Role::findByName(RoleEnum::KY_THUAT->value));

        $customer = Customer::create(['name' => 'Khách hàng kỹ thuật dashboard']);

        foreach ([45_000_000, 55_000_000] as $value) {
            $contract = ContractLegal::create([
                'shd_bc' => '06/2026/HĐKT.BC-TIENDO',
                'customer_id' => $customer->id,
                'staff_id' => $technical->id,
                'department_id' => $department->id,
                'value' => $value,
                'signed_at' => now()->startOfYear()->addMonth(),
            ]);

            ContractAssignment::create([
                'assignable_type' => ContractLegal::class,
                'assignable_id' => $contract->id,
                'user_id' => $technical->id,
                'assigned_by' => $technical->id,
            ]);
        }

        $this->actingAs($technical);

        Livewire::test(StatisticsBoard::class)
            ->assertViewHas('technicalSummary', fn (array $summary): bool => $summary['total'] === 1
                && $summary['processing'] === 1)
            ->assertViewHas('technicalStats', fn ($stats): bool => $stats->contains(
                fn (array $row): bool => $row['count'] === 1 && $row['completed'] === 0
            ));
    }

    private function createDashboardFixtures(): User
    {
        $role = Role::findOrCreate(RoleEnum::GIAM_DOC->value);
        $department = Department::create([
            'name' => 'Kinh doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $user->assignRole($role);

        $customer = Customer::create(['name' => 'Khách hàng dashboard']);
        $handler = Handler::create(['name' => 'Nhà thầu dashboard']);

        foreach ([
            ['date' => '2026-01-15', 'revenue' => 100_000_000],
            ['date' => '2026-02-15', 'revenue' => 200_000_000],
        ] as $contract) {
            ContractWaste::create([
                'customer_id' => $customer->id,
                'handler_id' => $handler->id,
                'staff_id' => $user->id,
                'department_id' => $department->id,
                'value' => $contract['revenue'],
                'revenue' => $contract['revenue'],
                'signed_at' => $contract['date'],
                'submitted_at' => $contract['date'],
                'is_renewal' => false,
            ]);
        }

        return $user;
    }
}
