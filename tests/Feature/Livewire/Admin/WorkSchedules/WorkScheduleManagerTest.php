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
}
