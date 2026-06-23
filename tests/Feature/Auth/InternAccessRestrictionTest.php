<?php

namespace Tests\Feature\Auth;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InternAccessRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_intern_login_defaults_to_daily_reports(): void
    {
        $user = $this->createInternUser();

        $response = $this->post(route('login.attempt'), [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('app.daily-reports.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_intern_get_routes_outside_daily_reports_redirect_back_to_daily_reports(): void
    {
        $user = $this->createInternUser();

        $response = $this->actingAs($user)->get(route('app.dashboard'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('app.daily-reports.index'), $this->locationHeader($response));
    }

    public function test_intern_can_open_daily_reports_without_sidebar_links_to_other_sections(): void
    {
        $user = $this->createInternUser();

        $response = $this->actingAs($user)->get(route('app.daily-reports.index'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString(route('app.daily-reports.index'), $response->getContent());
        $this->assertStringNotContainsString(route('app.work-schedules.index'), $response->getContent());
        $this->assertStringNotContainsString(route('app.profile.index'), $response->getContent());
    }

    private function createInternUser(): User
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value);
        }

        $role = Role::findOrCreate(RoleEnum::THUC_TAP->value);
        $role->syncPermissions([
            PermissionEnum::DAILY_REPORTS_VIEW->value,
            PermissionEnum::DAILY_REPORTS_CREATE->value,
            PermissionEnum::DAILY_REPORTS_EDIT->value,
        ]);

        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function locationHeader($response): ?string
    {
        $headers = $response->baseResponse->headers;

        return is_array($headers)
            ? ($headers['Location'] ?? null)
            : $headers->get('Location');
    }
}
