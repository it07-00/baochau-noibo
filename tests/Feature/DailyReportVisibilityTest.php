<?php

namespace Tests\Feature;

use App\Enums\DailyReportStatus;
use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\DailyReports\DailyReportManager;
use App\Models\DailyReport;
use App\Models\User;
use App\Support\DailyReportVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DailyReportVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        Permission::findOrCreate(PermissionEnum::DAILY_REPORTS_VIEW->value);
        Permission::findOrCreate(PermissionEnum::DAILY_REPORTS_VIEW_ALL->value);
    }

    public function test_regular_user_only_sees_their_own_daily_report_scope(): void
    {
        $viewer = $this->createUser(RoleEnum::KINH_DOANH);
        $this->createUser(RoleEnum::KY_THUAT);

        $visibleUserIds = DailyReportVisibility::visibleUsersQuery($viewer)->pluck('id')->all();

        $this->assertSame([$viewer->id], $visibleUserIds);
    }

    public function test_sales_manager_scope_stays_limited_to_sales_users(): void
    {
        $manager = $this->createUser(RoleEnum::TP_KINH_DOANH, [PermissionEnum::DAILY_REPORTS_VIEW_ALL]);
        $salesUser = $this->createUser(RoleEnum::KINH_DOANH);
        $technicalUser = $this->createUser(RoleEnum::KY_THUAT);

        $visibleUserIds = DailyReportVisibility::visibleUsersQuery($manager)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertContains($manager->id, $visibleUserIds);
        $this->assertContains($salesUser->id, $visibleUserIds);
        $this->assertNotContains($technicalUser->id, $visibleUserIds);
    }

    public function test_non_sales_user_with_view_all_permission_sees_company_scope(): void
    {
        $viewer = $this->createUser(RoleEnum::HCNS, [PermissionEnum::DAILY_REPORTS_VIEW_ALL]);
        $salesUser = $this->createUser(RoleEnum::KINH_DOANH);
        $technicalUser = $this->createUser(RoleEnum::KY_THUAT);

        $visibleUserIds = DailyReportVisibility::visibleUsersQuery($viewer)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->assertContains($viewer->id, $visibleUserIds);
        $this->assertContains($salesUser->id, $visibleUserIds);
        $this->assertContains($technicalUser->id, $visibleUserIds);
    }

    public function test_view_all_permission_enables_daily_report_management_tab(): void
    {
        $viewer = $this->createUser(RoleEnum::HCNS, [PermissionEnum::DAILY_REPORTS_VIEW_ALL]);

        $this->actingAs($viewer);

        Livewire::test(DailyReportManager::class)
            ->assertSet('isManager', true);
    }

    public function test_management_missing_count_matches_every_visible_user_without_a_report(): void
    {
        $manager = $this->createUser(RoleEnum::TP_KINH_DOANH, [PermissionEnum::DAILY_REPORTS_VIEW_ALL]);
        $firstMissingUser = $this->createUser(RoleEnum::KINH_DOANH);
        $secondMissingUser = $this->createUser(RoleEnum::KINH_DOANH);

        DailyReport::create([
            'user_id' => $manager->id,
            'date' => today(),
            'content' => 'Báo cáo công việc của trưởng phòng',
            'plan' => '',
            'status' => DailyReportStatus::HOAN_THANH_DUNG_KH->value,
        ]);

        $this->actingAs($manager);

        Livewire::test(DailyReportManager::class)
            ->set('activeTab', 'management')
            ->assertSet('reportStats.missing', 2)
            ->assertSee($firstMissingUser->name)
            ->assertSee($secondMissingUser->name)
            ->assertSee('2 chưa báo cáo');
    }

    public function test_view_all_permission_does_not_allow_deleting_other_users_reports(): void
    {
        $viewer = $this->createUser(RoleEnum::HCNS, [PermissionEnum::DAILY_REPORTS_VIEW_ALL]);
        $reportOwner = $this->createUser(RoleEnum::KINH_DOANH);

        $report = DailyReport::create([
            'user_id' => $reportOwner->id,
            'date' => today(),
            'content' => 'Visible report content',
            'plan' => '',
            'status' => DailyReportStatus::HOAN_THANH_DUNG_KH->value,
        ]);

        $this->actingAs($viewer);

        Livewire::test(DailyReportManager::class)
            ->call('deleteReport', $report->id)
            ->assertStatus(403);

        $this->assertDatabaseHas('daily_reports', ['id' => $report->id]);
    }

    public function test_daily_report_late_calculation_with_three_day_threshold(): void
    {
        $user = $this->createUser(RoleEnum::KINH_DOANH);
        $this->actingAs($user);

        // 1. Report submitted 1 day late (diff = 1) -> not late (returns 0)
        $report1 = DailyReport::create([
            'user_id' => $user->id,
            'date' => today()->subDays(1),
            'content' => 'Report content 1',
            'status' => DailyReportStatus::HOAN_THANH_DUNG_KH->value,
            'plan' => '',
            'issues' => '',
        ]);
        $report1->created_at = today();
        $report1->save();

        // 2. Report submitted 2 days late (diff = 2) -> not late (returns 0)
        $report2 = DailyReport::create([
            'user_id' => $user->id,
            'date' => today()->subDays(2),
            'content' => 'Report content 2',
            'status' => DailyReportStatus::HOAN_THANH_DUNG_KH->value,
            'plan' => '',
            'issues' => '',
        ]);
        $report2->created_at = today();
        $report2->save();

        // 3. Report submitted 3 days late (diff = 3) -> late (returns 3)
        $report3 = DailyReport::create([
            'user_id' => $user->id,
            'date' => today()->subDays(3),
            'content' => 'Report content 3',
            'status' => DailyReportStatus::HOAN_THANH_DUNG_KH->value,
            'plan' => '',
            'issues' => '',
        ]);
        $report3->created_at = today();
        $report3->save();

        $component = Livewire::test(DailyReportManager::class)->instance();

        $this->assertSame(0, $component->reportLateDays($report1));
        $this->assertSame(0, $component->reportLateDays($report2));
        $this->assertSame(3, $component->reportLateDays($report3));
    }

    private function createUser(RoleEnum $role, array $extraPermissions = []): User
    {
        $roleModel = Role::findByName($role->value);
        $roleModel->givePermissionTo(PermissionEnum::DAILY_REPORTS_VIEW->value);

        foreach ($extraPermissions as $permission) {
            $roleModel->givePermissionTo($permission->value);
        }

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($roleModel);

        return $user;
    }
}
