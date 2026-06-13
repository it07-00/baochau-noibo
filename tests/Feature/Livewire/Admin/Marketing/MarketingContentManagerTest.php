<?php

namespace Tests\Feature\Livewire\Admin\Marketing;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Marketing\MarketingContentManager;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

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

        \Livewire\Livewire::test(MarketingContentManager::class)
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
}
