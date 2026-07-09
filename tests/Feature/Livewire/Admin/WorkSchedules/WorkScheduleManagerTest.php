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
            3,
            substr_count($html, 'Greeco: GREECO MULTI DAY TRAINING'),
            'The multi-day Greeco event should render as one spanning desktop event and once per covered day on mobile.',
        );
        $this->assertStringContainsString('grid-column: 3 / span 2;', $html);
        $this->assertStringContainsString('22/07 - 23/07', $html);
    }
}
