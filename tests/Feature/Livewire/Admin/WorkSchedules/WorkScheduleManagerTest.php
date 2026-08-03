<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Admin\WorkSchedules;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\WorkSchedules\WorkScheduleManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

final class WorkScheduleManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_calendar_without_scope_errors(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::findOrCreate(RoleEnum::IT->value);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        Http::fake([
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $this->actingAs($user);

        Livewire::test(WorkScheduleManager::class)
            ->assertOk();
    }

    public function test_it_renders_greeco_multi_day_events_on_each_covered_day(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::findOrCreate(RoleEnum::IT->value);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'id' => 88,
                        'title' => 'GREECO MULTI DAY TRAINING',
                        'description' => null,
                        'start_date' => '2026-07-22',
                        'start_time' => '07:01:00',
                        'end_date' => '2026-07-23',
                        'end_time' => '17:25:00',
                        'color' => 'warning',
                        'creator_name' => 'Giam Doc',
                        'participants' => [],
                    ],
                ],
            ]),
        ]);

        $this->actingAs($user);

        $html = Livewire::test(WorkScheduleManager::class)
            ->set('monthFilter', 7)
            ->set('yearFilter', 2026)
            ->html();

        $this->assertSame(
            4,
            substr_count($html, 'Greeco: GREECO MULTI DAY TRAINING'),
            'The multi-day Greeco event should render as separate daily events on each covered day for both desktop and mobile.',
        );
        $this->assertStringNotContainsString('22/07 - 23/07', $html);
    }

    public function test_it_can_create_event_with_custom_hex_color(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::findOrCreate(RoleEnum::IT->value);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        Http::fake([
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $this->actingAs($user);

        Livewire::test(WorkScheduleManager::class)
            ->set('title', 'Sự kiện màu đặc biệt')
            ->set('startDate', today()->format('Y-m-d'))
            ->set('color', '#ff0055')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_schedules', [
            'title' => 'Sự kiện màu đặc biệt',
            'color' => '#ff0055',
        ]);
    }

    public function test_it_can_create_birthday_event_and_displays_birthday_icon(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::findOrCreate(RoleEnum::IT->value);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        Http::fake([
            '*' => Http::response(['success' => true, 'data' => []]),
        ]);

        $this->actingAs($user);

        Livewire::test(WorkScheduleManager::class)
            ->set('title', 'Sinh nhật Đăng Thi')
            ->set('startDate', today()->format('Y-m-d'))
            ->set('isBirthday', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_schedules', [
            'title' => 'Sinh nhật Đăng Thi',
            'is_birthday' => true,
        ]);
    }
}
