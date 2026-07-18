<?php

namespace Tests\Feature\Livewire\Admin\DailyReports;

use App\Enums\DailyReportStatus;
use App\Enums\DailyReportSupportStatus;
use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\DailyReports\DailyReportManager;
use App\Models\DailyReport;
use App\Models\User;
use App\Notifications\DailyReportSupportUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DailyReportSupportManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        foreach ([PermissionEnum::DAILY_REPORTS_VIEW, PermissionEnum::DAILY_REPORTS_VIEW_ALL] as $permission) {
            Permission::findOrCreate($permission->value);
        }
    }

    public function test_report_with_an_issue_is_added_to_the_support_queue(): void
    {
        $reporter = $this->createUser(RoleEnum::KINH_DOANH);
        $this->actingAs($reporter);

        Livewire::test(DailyReportManager::class)
            ->set('content', 'Đã liên hệ và xử lý hồ sơ khách hàng trong ngày.')
            ->set('status', DailyReportStatus::GAP_VAN_DE->value)
            ->set('issues', 'Cần hỗ trợ kiểm tra hồ sơ kỹ thuật.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('daily_reports', [
            'user_id' => $reporter->id,
            'support_status' => DailyReportSupportStatus::PENDING->value,
        ]);
    }

    public function test_manager_can_receive_and_resolve_a_visible_support_request(): void
    {
        Notification::fake();

        $manager = $this->createUser(RoleEnum::TP_KINH_DOANH, true);
        $reporter = $this->createUser(RoleEnum::KINH_DOANH);
        $report = $this->createSupportReport($reporter);

        $this->actingAs($manager);

        Livewire::test(DailyReportManager::class)
            ->set('activeTab', 'support')
            ->assertSee('Cần hỗ trợ kiểm tra hồ sơ kỹ thuật.')
            ->call('startSupport', $report->id)
            ->assertHasNoErrors()
            ->call('openSupportModal', $report->id)
            ->set('supportResolution', 'Đã hướng dẫn bổ sung biểu mẫu và kiểm tra lại hồ sơ.')
            ->call('resolveSupport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'support_status' => DailyReportSupportStatus::RESOLVED->value,
            'support_handler_id' => $manager->id,
            'support_response' => 'Đã hướng dẫn bổ sung biểu mẫu và kiểm tra lại hồ sơ.',
        ]);

        Notification::assertSentTo($reporter, DailyReportSupportUpdatedNotification::class, 2);
    }

    public function test_sales_manager_cannot_handle_support_outside_the_sales_scope(): void
    {
        $manager = $this->createUser(RoleEnum::TP_KINH_DOANH, true);
        $technicalUser = $this->createUser(RoleEnum::KY_THUAT);
        $report = $this->createSupportReport($technicalUser);

        $this->actingAs($manager);

        Livewire::test(DailyReportManager::class)
            ->call('startSupport', $report->id)
            ->assertNotFound();

        $this->assertDatabaseHas('daily_reports', [
            'id' => $report->id,
            'support_status' => DailyReportSupportStatus::PENDING->value,
            'support_handler_id' => null,
        ]);
    }

    private function createUser(RoleEnum $role, bool $canViewAll = false): User
    {
        $roleModel = Role::findByName($role->value);
        $roleModel->givePermissionTo(PermissionEnum::DAILY_REPORTS_VIEW->value);

        if ($canViewAll) {
            $roleModel->givePermissionTo(PermissionEnum::DAILY_REPORTS_VIEW_ALL->value);
        }

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($roleModel);

        return $user;
    }

    private function createSupportReport(User $reporter): DailyReport
    {
        return DailyReport::create([
            'user_id' => $reporter->id,
            'date' => today(),
            'content' => 'Báo cáo công việc trong ngày.',
            'plan' => '',
            'status' => DailyReportStatus::GAP_VAN_DE->value,
            'issues' => 'Cần hỗ trợ kiểm tra hồ sơ kỹ thuật.',
            'support_status' => DailyReportSupportStatus::PENDING->value,
        ]);
    }
}
