<?php

namespace Tests\Feature\Livewire\Admin\Reports;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Reports\Sales\PersonalSalesReport;
use App\Livewire\Admin\Reports\Sales\SalesSummaryReport;
use App\Livewire\Admin\Reports\Sales\SalesTargetReport;
use App\Livewire\Admin\Sales\SalesTargetRegistration;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesReportsConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            if (method_exists($pdo, 'sqliteCreateFunction')) {
                $pdo->sqliteCreateFunction(
                    'MONTH',
                    static fn (?string $date): ?int => $date ? (int) date('n', strtotime($date)) : null
                );
            }
        }
    }

    public function test_all_sales_reports_use_the_same_contract_revenue_date(): void
    {
        $salesRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value);
        $department = Department::create([
            'name' => 'Phong Kinh Doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
        $salesperson = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $salesperson->assignRole($salesRole);

        $customer = Customer::create(['name' => 'Khach hang bao cao']);
        $handler = Handler::create(['name' => 'Nha thau phu bao cao']);

        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson->id,
            'department_id' => $department->id,
            'value' => 100_000_000,
            'revenue' => 100_000_000,
            'signed_at' => '2026-02-10',
            'submitted_at' => '2026-02-20',
            'is_renewal' => false,
        ]);

        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson->id,
            'department_id' => $department->id,
            'value' => 200_000_000,
            'revenue' => 200_000_000,
            'signed_at' => '2026-01-15',
            'submitted_at' => '2026-03-05',
            'is_renewal' => true,
        ]);

        // Non-invoiced contract (should be ignored in reports)
        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson->id,
            'department_id' => $department->id,
            'value' => 50_000_000,
            'revenue' => 50_000_000,
            'signed_at' => '2026-04-10',
            'submitted_at' => null,
            'is_renewal' => false,
        ]);

        $this->actingAs($salesperson);

        Livewire::test(SalesSummaryReport::class)
            ->set('year', 2026)
            ->assertViewHas('months', function (array $months): bool {
                return $months[2]['progressive'] === 100_000_000.0
                    && $months[3]['renewal'] === 200_000_000.0;
            })
            ->assertViewHas(
                'totals',
                fn (array $totals): bool => $totals['contract_total'] === 300_000_000.0
            )
            ->set('filter_month', 2)
            ->assertViewHas(
                'detail',
                fn (Collection $detail): bool => $detail->count() === 1
                    && $detail->sum('value') === 100_000_000.0
                    && $detail->first()['date']->toDateString() === '2026-02-20'
            );

        Livewire::test(SalesTargetReport::class)
            ->set('year', 2026)
            ->assertViewHas('months', function (array $months): bool {
                return count($months) === 12
                    && array_keys($months) === range(1, 12)
                    && $months[2]['actual'] === 100_000_000.0
                    && $months[3]['actual'] === 200_000_000.0;
            })
            ->assertViewHas(
                'totals',
                fn (array $totals): bool => $totals['actual'] === 300_000_000.0
            );

        Livewire::test(PersonalSalesReport::class)
            ->set('year', 2026)
            ->assertViewHas('personalRows', function (array $rows): bool {
                return $rows[2]['actual'] === 100_000_000.0
                    && $rows[3]['actual'] === 200_000_000.0;
            })
            ->assertViewHas(
                'personalTotals',
                fn (array $totals): bool => $totals['actual'] === 300_000_000.0
            );
    }

    public function test_sales_target_registration_groups_pre_2026_contracts_into_january_2026(): void
    {
        $salesRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value);
        $department = Department::create([
            'name' => 'Phong Kinh Doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
        $salesperson = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $salesperson->assignRole($salesRole);

        $customer = Customer::create(['name' => 'Khach hang']);
        $handler = Handler::create(['name' => 'Nha thau phu']);

        // Pre-2026 signed contract
        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson->id,
            'department_id' => $department->id,
            'value' => 120_000_000,
            'revenue' => 120_000_000,
            'signed_at' => '2025-11-20',
            'submitted_at' => null,
            'is_renewal' => false,
        ]);

        // 2026 signed contract
        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson->id,
            'department_id' => $department->id,
            'value' => 80_000_000,
            'revenue' => 80_000_000,
            'signed_at' => '2026-02-15',
            'submitted_at' => null,
            'is_renewal' => false,
        ]);

        $this->actingAs($salesperson);

        Livewire::test(SalesTargetRegistration::class)
            ->set('year', 2026)
            ->assertViewHas('months', function (array $months): bool {
                return $months[1]['actual'] === 120_000_000.0
                    && $months[2]['actual'] === 80_000_000.0;
            });
    }

    public function test_current_year_sales_target_report_allows_viewing_all_twelve_months(): void
    {
        $salesRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value);
        $salesperson = User::factory()->create(['is_active' => true]);
        $salesperson->assignRole($salesRole);

        $this->actingAs($salesperson);

        Livewire::test(SalesTargetReport::class)
            ->set('year', (int) now()->format('Y'))
            ->set('viewMonth', 12)
            ->assertSet('viewMonth', 12)
            ->assertViewHas('maxMonth', 12)
            ->assertViewHas(
                'months',
                fn (array $months): bool => array_keys($months) === range(1, 12)
            );
    }

    public function test_sales_target_report_normalizes_legacy_payment_methods(): void
    {
        $salesRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value);
        $department = Department::create([
            'name' => 'Phong Kinh Doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
        $salesperson = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $salesperson->assignRole($salesRole);

        $customer = Customer::create(['name' => 'Khach hang thanh toan']);
        $handler = Handler::create(['name' => 'Nha thau phu thanh toan']);

        ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $salesperson->id,
            'department_id' => $department->id,
            'value' => 10_000_000,
            'revenue' => 9_000_000,
            'submitted_at' => now(),
            'payment_method' => 'Sau ký | Sau khi có kết quả/báo cáo',
            'is_renewal' => false,
        ]);

        $this->actingAs($salesperson);

        Livewire::test(SalesTargetReport::class)
            ->assertSet(
                'detail.0.payment_method',
                'Sau khi ký HĐ | Sau khi có kết quả/báo cáo'
            );
    }
}
