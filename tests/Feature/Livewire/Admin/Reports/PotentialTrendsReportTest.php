<?php

namespace Tests\Feature\Livewire\Admin\Reports;

use App\Enums\Permission as PermissionEnum;
use App\Enums\QuotationStatus;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Reports\PotentialTrendsReport;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PotentialTrendsReportTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;

    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Permission::findOrCreate(PermissionEnum::REPORTS_SALES_VIEW->value);
        Role::findOrCreate(RoleEnum::KINH_DOANH->value)
            ->givePermissionTo(PermissionEnum::REPORTS_SALES_VIEW->value);
        Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value)
            ->givePermissionTo(PermissionEnum::REPORTS_SALES_VIEW->value);
        Role::findOrCreate(RoleEnum::MARKETING->value);

        $this->department = Department::create([
            'name' => 'Phòng Kinh doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
        $this->handler = Handler::create(['name' => 'Nhà thầu báo cáo']);
    }

    public function test_sales_user_with_permission_can_open_bao_cao_route(): void
    {
        $salesperson = $this->salesUser(RoleEnum::KINH_DOANH);

        $this->actingAs($salesperson)
            ->get('/bao-cao')
            ->assertOk()
            ->assertSee('Báo cáo xu hướng tiềm năng');
    }

    public function test_report_renders_detail_sections_as_charts_instead_of_tables(): void
    {
        $manager = $this->salesUser(RoleEnum::TP_KINH_DOANH);
        $this->quotation($manager, QuotationStatus::DANG_THEO_DOI, 50_000_000, now()->toDateString());

        $this->actingAs($manager)
            ->get('/bao-cao')
            ->assertOk()
            ->assertSee('potentialServicesChart', false)
            ->assertSee('potentialRegionsChart', false)
            ->assertSee('potentialStaffChart', false)
            ->assertSee('potentialRecommendationsChart', false)
            ->assertSee('potentialRecentChart', false)
            ->assertDontSee('<table', false);
    }

    public function test_user_without_sales_report_permission_cannot_open_bao_cao_route(): void
    {
        $marketing = User::factory()->create(['is_active' => true]);
        $marketing->assignRole(RoleEnum::MARKETING->value);

        $this->actingAs($marketing)
            ->get('/bao-cao')
            ->assertForbidden();
    }

    public function test_salesperson_cannot_change_staff_filter_to_view_another_person(): void
    {
        $salesperson = $this->salesUser(RoleEnum::KINH_DOANH);
        $otherSalesperson = $this->salesUser(RoleEnum::KINH_DOANH);

        $this->quotation($salesperson, QuotationStatus::DANG_THEO_DOI, 50_000_000, '2026-07-02');
        $this->quotation($salesperson, QuotationStatus::KY_HOP_DONG, 100_000_000, '2026-07-08');
        $this->quotation($otherSalesperson, QuotationStatus::KY_HOP_DONG, 900_000_000, '2026-07-10');
        $this->contract($salesperson, 120_000_000, '2026-07-11');
        $this->contract($salesperson, 80_000_000, null);
        $this->contract($otherSalesperson, 700_000_000, '2026-07-12');

        $this->actingAs($salesperson);

        Livewire::test(PotentialTrendsReport::class)
            ->set('dateFrom', '2026-07-01')
            ->set('dateTo', '2026-07-31')
            ->set('staffId', (string) $otherSalesperson->id)
            ->assertSet('staffId', (string) $salesperson->id)
            ->assertViewHas('report', function (array $report): bool {
                return $report['kpis']['opportunities']['value'] === 2
                    && $report['kpis']['conversion_rate']['value'] === 50.0
                    && $report['kpis']['potential_value']['value'] === 50_000_000.0
                    && $report['kpis']['revenue']['value'] === 120_000_000.0;
            });
    }

    public function test_sales_manager_can_view_team_and_filter_one_salesperson(): void
    {
        $manager = $this->salesUser(RoleEnum::TP_KINH_DOANH);
        $firstSalesperson = $this->salesUser(RoleEnum::KINH_DOANH);
        $secondSalesperson = $this->salesUser(RoleEnum::KINH_DOANH);

        $this->quotation($firstSalesperson, QuotationStatus::KY_HOP_DONG, 100_000_000, '2026-07-05');
        $this->quotation($secondSalesperson, QuotationStatus::DANG_THEO_DOI, 200_000_000, '2026-07-06');

        $this->actingAs($manager);

        Livewire::test(PotentialTrendsReport::class)
            ->set('dateFrom', '2026-07-01')
            ->set('dateTo', '2026-07-31')
            ->assertViewHas(
                'report',
                fn (array $report): bool => $report['kpis']['opportunities']['value'] === 2
                    && count($report['staff_performance']) === 2,
            )
            ->set('staffId', (string) $secondSalesperson->id)
            ->assertViewHas(
                'report',
                fn (array $report): bool => $report['kpis']['opportunities']['value'] === 1
                    && $report['kpis']['potential_value']['value'] === 200_000_000.0,
            );
    }

    private function salesUser(RoleEnum $role): User
    {
        $user = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $user->assignRole($role->value);

        return $user;
    }

    private function quotation(User $staff, QuotationStatus $status, int $value, string $date): Quotation
    {
        return Quotation::create([
            'date' => $date,
            'staff_id' => $staff->id,
            'company_name' => 'Khách hàng '.$staff->id.' '.$date,
            'service' => 'Quan trắc môi trường',
            'province' => 'TP. Hồ Chí Minh',
            'status' => $status->value,
            'total_value' => $value,
        ]);
    }

    private function contract(User $staff, int $revenue, ?string $submittedAt): ContractWaste
    {
        $customer = Customer::create(['name' => 'Khách hợp đồng '.$staff->id.' '.uniqid()]);

        return ContractWaste::create([
            'customer_id' => $customer->id,
            'handler_id' => $this->handler->id,
            'staff_id' => $staff->id,
            'department_id' => $this->department->id,
            'loai_dich_vu' => 'Quan trắc môi trường',
            'province' => 'TP. Hồ Chí Minh',
            'value' => $revenue,
            'revenue' => $revenue,
            'signed_at' => '2026-07-01',
            'submitted_at' => $submittedAt,
            'status' => 'HOÀN THÀNH',
        ]);
    }
}
