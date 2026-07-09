<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WorkScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_all_public_work_schedules_in_range(): void
    {
        config()->set('services.greeco.api_token', 'test-token');

        $creator = User::factory()->create();

        $publicSchedule = WorkSchedule::create([
            'user_id' => $creator->id,
            'title' => 'Public sales visit',
            'description' => 'Visible to integration',
            'start_date' => '2026-07-10',
            'start_time' => '08:30:00',
            'end_date' => null,
            'end_time' => null,
            'color' => 'primary',
            'is_private' => false,
        ]);

        WorkSchedule::create([
            'user_id' => $creator->id,
            'title' => 'Private appointment',
            'start_date' => '2026-07-10',
            'color' => 'danger',
            'is_private' => true,
        ]);

        $response = $this->getJson('/api/work-schedules?start=2026-07-01&end=2026-07-31&token=test-token');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $publicSchedule->id)
            ->assertJsonPath('data.0.title', 'Public sales visit');

        $this->assertCount(1, $response->json('data'));
    }

    public function test_it_exports_multi_day_work_schedules_when_querying_a_covered_later_day(): void
    {
        config()->set('services.greeco.api_token', 'test-token');

        $creator = User::factory()->create();

        $schedule = WorkSchedule::create([
            'user_id' => $creator->id,
            'title' => 'Two day course',
            'start_date' => '2026-07-22',
            'start_time' => '07:01:00',
            'end_date' => '2026-07-23',
            'end_time' => '17:25:00',
            'color' => 'warning',
            'is_private' => false,
        ]);

        $response = $this->getJson('/api/work-schedules?start=2026-07-23&end=2026-07-23&token=test-token');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $schedule->id . '_2026-07-23')
            ->assertJsonPath('data.0.start_date', '2026-07-23')
            ->assertJsonPath('data.0.end_date', '2026-07-23')
            ->assertJsonPath('data.0.start_time', '07:01')
            ->assertJsonPath('data.0.end_time', '17:25');
    }
}
