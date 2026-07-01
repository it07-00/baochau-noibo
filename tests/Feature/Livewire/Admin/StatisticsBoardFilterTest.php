<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\StatisticsBoard;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StatisticsBoardFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction(
                'MONTH',
                static fn (?string $date): ?int => $date ? (int) date('n', strtotime($date)) : null
            );
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
