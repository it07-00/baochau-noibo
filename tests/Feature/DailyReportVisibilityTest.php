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
