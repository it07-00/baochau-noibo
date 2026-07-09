<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\GreecoWorkScheduleRepository;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GreecoWorkScheduleRepositoryTest extends TestCase
{
    public function test_it_fetches_duty_schedules_from_configured_greeco_api(): void
    {
        config()->set('services.greeco.api_url', 'https://noibo.greeco.vn');
        config()->set('services.greeco.api_token', 'test-token');

        Http::fake([
            'https://noibo.greeco.vn/api/duty-schedules*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 10, 'title' => 'Hop giao ban'],
                ],
            ]),
        ]);

        $items = (new GreecoWorkScheduleRepository())
            ->getEventsInRange('2026-07-01 08:00:00', '2026-07-31 17:00:00');

        $this->assertSame(10, $items[0]['id']);

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://noibo.greeco.vn/api/duty-schedules')
            && $request['start'] === '2026-07-01'
            && $request['end'] === '2026-07-31'
            && $request['token'] === 'test-token');
    }

    public function test_it_converts_greeco_payload_to_temporary_work_schedule_model(): void
    {
        $schedule = (new GreecoWorkScheduleRepository())->toWorkScheduleModel([
            'id' => 25,
            'title' => 'Kiem tra hien truong',
            'description' => null,
            'start_date' => '2026-07-09',
            'start_time' => '08:30:00',
            'end_date' => '2026-07-09',
            'end_time' => null,
            'color' => 'warning',
            'creator_name' => 'Nguyen Van A',
            'participants' => [
                ['id' => 1, 'name' => 'Tran Thi B'],
            ],
        ]);

        $this->assertSame('greeco_25', $schedule->id);
        $this->assertSame('Greeco: Kiem tra hien truong', $schedule->title);
        $this->assertSame('2026-07-09', $schedule->start_date->toDateString());
        $this->assertSame('08:30', $schedule->start_time);
        $this->assertNull($schedule->end_date);
        $this->assertNull($schedule->end_time);
        $this->assertSame('warning', $schedule->color);
        $this->assertSame('Tran Thi B', $schedule->participants->first()->name);
        $this->assertSame('Nguyen Van A', $schedule->user->name);
    }
}
