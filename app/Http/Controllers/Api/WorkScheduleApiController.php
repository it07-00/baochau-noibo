<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WorkScheduleApiController extends Controller
{
    /**
     * Return work schedules in a date range as JSON for cross-system consumption.
     *
     * GET /api/work-schedules?start=2026-07-01&end=2026-07-31&token=xxx
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
            'token' => ['required', 'string'],
        ]);

        if ($request->input('token') !== config('services.greeco.api_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $startDate = $request->input('start');
        $endDate = $request->input('end');

        $events = WorkSchedule::query()
            ->with(['user:id,name', 'participants:id,name'])
            ->where('is_private', false)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereNotNull('end_date')
                        ->where('start_date', '<=', $endDate)
                        ->where('end_date', '>=', $startDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->whereNull('end_date')
                        ->whereBetween('start_date', [$startDate, $endDate]);
                });
            })
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $splitEvents = collect();
        $qStart = \Carbon\Carbon::parse($startDate);
        $qEnd = \Carbon\Carbon::parse($endDate);

        foreach ($events as $event) {
            $eventStart = $event->start_date;
            $eventEnd = $event->effective_end_date;

            if ($eventEnd->gt($eventStart)) {
                $overlapStart = $eventStart->gt($qStart) ? $eventStart : $qStart;
                $overlapEnd = $eventEnd->lt($qEnd) ? $eventEnd : $qEnd;

                if ($overlapStart->lte($overlapEnd)) {
                    $period = \Carbon\CarbonPeriod::create($overlapStart, $overlapEnd);
                    foreach ($period as $date) {
                        $clone = $event->replicate();
                        $clone->id = $event->id . '_' . $date->format('Y-m-d');
                        $clone->start_date = $date->copy();
                        $clone->end_date = null;

                        if ($event->relationLoaded('user')) {
                            $clone->setRelation('user', $event->user);
                        }
                        if ($event->relationLoaded('participants')) {
                            $clone->setRelation('participants', $event->participants);
                        }
                        $splitEvents->push($clone);
                    }
                }
            } else {
                $splitEvents->push($event);
            }
        }
        $events = $splitEvents;

        $data = $events->map(function (WorkSchedule $event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start_date' => $event->start_date->format('Y-m-d'),
                'start_time' => $event->formatted_start_time,
                'end_date' => $event->effective_end_date->format('Y-m-d'),
                'end_time' => $event->formatted_end_time,
                'color' => $event->color,
                'creator_name' => $event->user?->name ?? 'N/A',
                'participants' => $event->participants->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data->toArray(),
        ]);
    }
}
