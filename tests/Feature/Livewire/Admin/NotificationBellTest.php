<?php

namespace Tests\Feature\Livewire\Admin;

use App\Enums\DailyReportStatus;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\NotificationBell;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_a_browser_event_when_polling_finds_a_new_notification(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertNotDispatched('browser-notification');

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'Tests\\Fixtures\\BrowserNotification',
            'data' => [
                'contract_type' => 'commission',
                'contract_label' => 'Yêu cầu chi hoa hồng',
                'message' => 'Có yêu cầu mới cần xử lý.',
                'url' => '/hoa-hong',
            ],
        ]);

        $component
            ->call('$refresh')
            ->assertDispatched('browser-notification', function (string $event, array $payload): bool {
                return $payload['title'] === 'Yêu cầu chi hoa hồng'
                    && $payload['body'] === 'Có yêu cầu mới cần xử lý.'
                    && $payload['url'] === '/hoa-hong';
            });
    }

    public function test_daily_report_issue_renders_a_visible_warning_icon(): void
    {
        $managerRole = Role::findOrCreate(RoleEnum::TP_KINH_DOANH->value);
        $manager = User::factory()->create();
        $manager->assignRole($managerRole);

        $salesRole = Role::findOrCreate(RoleEnum::KINH_DOANH->value);
        $reporter = User::factory()->create();
        $reporter->assignRole($salesRole);
        DailyReport::create([
            'user_id' => $reporter->id,
            'date' => today(),
            'content' => 'Báo cáo có vấn đề cần hỗ trợ',
            'plan' => '',
            'issues' => 'Cần trưởng phòng hỗ trợ',
            'status' => DailyReportStatus::GAP_VAN_DE->value,
        ]);

        Livewire::actingAs($manager)
            ->test(NotificationBell::class)
            ->assertSeeHtml('fa-solid fa-triangle-exclamation ')
            ->assertDontSeeHtml('fa-triangle-exclamation-fill');
    }
}
