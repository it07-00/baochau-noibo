<?php

namespace Tests\Feature\Livewire\Admin\Reports;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Reports\Sales\PersonalSalesReport;
use App\Livewire\Admin\Reports\Sales\SalesSummaryReport;
use App\Livewire\Admin\Reports\Sales\SalesTargetReport;
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
            DB::connection()->getPdo()->sqliteCreateFunction(
                'MONTH',
                static fn (?string $date): ?int => $date ? (int) date('n', strtotime($date)) : null
            );
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
            'submitted_at' => null,
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
                    && $detail->first()['date']->toDateString() === '2026-02-10'
            );

        Livewire::test(SalesTargetReport::class)
            ->set('year', 2026)
            ->assertViewHas('months', function (array $months): bool {
                return $months[2]['actual'] === 100_000_000.0
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
}
