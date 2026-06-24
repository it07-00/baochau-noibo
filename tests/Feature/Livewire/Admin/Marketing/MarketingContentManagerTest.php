<?php

namespace Tests\Feature\Livewire\Admin\Marketing;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Marketing\MarketingContentManager;
use App\Models\Department;
use App\Models\MarketingContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MarketingContentManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $salesUser;

    private User $accountantUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie permission cache
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed roles & permissions
        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value);
        }

        $salesRole = Role::findByName(RoleEnum::KINH_DOANH->value);
        $accountantRole = Role::findByName(RoleEnum::KE_TOAN->value);

        foreach (PermissionEnum::cases() as $perm) {
            Permission::findOrCreate($perm->value);
        }

        // Give KINH_DOANH necessary permissions including marketing-reports.view
        $salesRole->syncPermissions([
            PermissionEnum::MARKETING_REPORTS_VIEW->value,
        ]);

        $dept = Department::firstOrCreate(
            ['slug' => 'kinh-doanh'],
            ['name' => 'Phòng Kinh Doanh']
        );

        $this->salesUser = User::factory()->create([
            'is_active' => true,
            'department_id' => $dept->id,
        ]);
        $this->salesUser->assignRole($salesRole);

        $this->accountantUser = User::factory()->create([
            'is_active' => true,
        ]);
        $this->accountantUser->assignRole($accountantRole);
    }

    public function test_sales_user_can_access_marketing_content_page(): void
    {
        $this->actingAs($this->salesUser);

        $response = $this->get(route('app.marketing.content.index'));

        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_marketing_content_page(): void
    {
        $this->actingAs($this->accountantUser);

        $response = $this->get(route('app.marketing.content.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_create_permission_can_create_marketing_content(): void
    {
        $this->salesUser->givePermissionTo(PermissionEnum::ARTICLES_CREATE->value);

        $this->actingAs($this->salesUser);

        Livewire::test(MarketingContentManager::class)
            ->assertViewHas('isMarketing', true)
            ->set('formTitle', 'New content title')
            ->set('formContent', 'New content caption')
            ->set('formScheduledAt', '2026-06-20')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('marketing_contents', [
            'title' => 'New content title',
            'content' => 'New content caption',
            'user_id' => $this->salesUser->id,
            'status' => 'draft',
        ]);
    }

    public function test_calendar_day_click_prefills_create_date(): void
    {
        $this->salesUser->givePermissionTo(PermissionEnum::ARTICLES_CREATE->value);

        $this->actingAs($this->salesUser);

        Livewire::test(MarketingContentManager::class)
            ->call('openCreateForDate', '2026-07-15')
            ->assertSet('isEditing', false)
            ->assertSet('editingId', null)
            ->assertSet('formScheduledAt', '2026-07-15');
    }

    public function test_calendar_content_click_opens_editor_for_editable_content(): void
    {
        $this->salesUser->givePermissionTo(PermissionEnum::ARTICLES_EDIT->value);

        $record = MarketingContent::create([
            'user_id' => $this->salesUser->id,
            'title' => 'Draft campaign',
            'content' => 'Draft caption',
            'scheduled_at' => '2026-07-15',
            'status' => 'draft',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(MarketingContentManager::class)
            ->call('openCalendarContent', $record->id)
            ->assertSet('isEditing', true)
            ->assertSet('editingId', $record->id)
            ->assertSet('formTitle', 'Draft campaign');
    }

    public function test_calendar_content_click_opens_detail_for_locked_content(): void
    {
        $this->salesUser->givePermissionTo(PermissionEnum::ARTICLES_EDIT->value);

        $record = MarketingContent::create([
            'user_id' => $this->salesUser->id,
            'title' => 'Approved campaign',
            'content' => 'Approved caption',
            'scheduled_at' => '2026-07-15',
            'status' => 'approved',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(MarketingContentManager::class)
            ->call('openCalendarContent', $record->id)
            ->assertSet('isEditing', false)
            ->assertSet('detailId', $record->id);
    }

    public function test_detail_modal_has_copy_and_download_actions(): void
    {
        $record = MarketingContent::create([
            'user_id' => $this->salesUser->id,
            'title' => 'Ready campaign',
            'content' => 'Caption ready for social channels',
            'scheduled_at' => '2026-07-15',
            'status' => 'approved',
            'images' => ['marketing-content/ready.jpg'],
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(MarketingContentManager::class)
            ->call('openDetail', $record->id)
            ->assertSee('Copy đăng bài')
            ->assertSee('Tải xuống');
    }

    public function test_marketing_content_renders_as_month_calendar(): void
    {
        Carbon::setTestNow('2026-07-10 09:00:00');

        MarketingContent::create([
            'user_id' => $this->salesUser->id,
            'title' => 'July campaign',
            'content' => 'Campaign caption for July',
            'scheduled_at' => '2026-07-15',
            'status' => 'pending',
        ]);

        MarketingContent::create([
            'user_id' => $this->salesUser->id,
            'title' => 'August campaign',
            'content' => 'Campaign caption for August',
            'scheduled_at' => '2026-08-01',
            'status' => 'draft',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(MarketingContentManager::class)
            ->set('calendarMonth', '2026-07')
            ->assertSee('Tháng 07/2026')
            ->assertSee('July campaign')
            ->assertDontSee('August campaign')
            ->assertSeeHtml('mc-calendar-grid');
    }

    public function test_future_schedule_copy_uses_whole_calendar_days(): void
    {
        Carbon::setTestNow('2026-07-10 09:00:00');

        MarketingContent::create([
            'user_id' => $this->salesUser->id,
            'title' => 'Upcoming campaign',
            'content' => 'Campaign caption',
            'scheduled_at' => '2026-07-13',
            'status' => 'draft',
        ]);

        $this->actingAs($this->salesUser);

        Livewire::test(MarketingContentManager::class)
            ->set('calendarMonth', '2026-07')
            ->assertSee('Trong 3 ngày');
    }

    public function test_tpkd_user_cannot_create_or_edit_marketing_content(): void
    {
        $tpkdRole = Role::findByName(RoleEnum::TP_KINH_DOANH->value);
        $tpkdRole->givePermissionTo(PermissionEnum::ARTICLES_CREATE->value);
        $tpkdRole->givePermissionTo(PermissionEnum::ARTICLES_EDIT->value);

        $tpkdUser = User::factory()->create([
            'is_active' => true,
        ]);
        $tpkdUser->assignRole($tpkdRole);

        $this->actingAs($tpkdUser);

        Livewire::test(MarketingContentManager::class)
            ->assertViewHas('isMarketing', false)
            ->set('formTitle', 'New content title')
            ->set('formContent', 'New content caption')
            ->set('formScheduledAt', '2026-06-20')
            ->call('save')
            ->assertStatus(403);
    }

    public function test_tpkd_user_can_access_marketing_content_page(): void
    {
        $tpkdRole = Role::findByName(RoleEnum::TP_KINH_DOANH->value);
        $tpkdUser = User::factory()->create([
            'is_active' => true,
        ]);
        $tpkdUser->assignRole($tpkdRole);

        $this->actingAs($tpkdUser);

        $response = $this->get(route('app.marketing.content.index'));

        $response->assertStatus(200);
    }

    public function test_tpkd_user_calendar_click_opens_detail_modal_instead_of_editor(): void
    {
        $tpkdRole = Role::findByName(RoleEnum::TP_KINH_DOANH->value);
        $tpkdRole->givePermissionTo(PermissionEnum::ARTICLES_EDIT->value);

        $tpkdUser = User::factory()->create([
            'is_active' => true,
        ]);
        $tpkdUser->assignRole($tpkdRole);

        $record = MarketingContent::create([
            'user_id' => $tpkdUser->id,
            'title' => 'Draft campaign by TPKD',
            'content' => 'Draft caption',
            'scheduled_at' => '2026-07-15',
            'status' => 'draft',
        ]);

        $this->actingAs($tpkdUser);

        Livewire::test(MarketingContentManager::class)
            ->call('openCalendarContent', $record->id)
            ->assertSet('isEditing', false)
            ->assertSet('detailId', $record->id);
    }
}
