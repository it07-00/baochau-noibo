<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\WorkScheduleNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $qStart = Carbon::parse($startDate);
        $qEnd = Carbon::parse($endDate);

        foreach ($events as $event) {
            $eventStart = $event->start_date;
            $eventEnd = $event->effective_end_date;

            if ($eventEnd->gt($eventStart)) {
                $overlapStart = $eventStart->gt($qStart) ? $eventStart : $qStart;
                $overlapEnd = $eventEnd->lt($qEnd) ? $eventEnd : $qEnd;

                if ($overlapStart->lte($overlapEnd)) {
                    $period = CarbonPeriod::create($overlapStart, $overlapEnd);
                    foreach ($period as $date) {
                        $clone = $event->replicate();
                        $clone->setIncrementing(false);
                        $clone->setKeyType('string');
                        $clone->id = $event->id.'_'.$date->format('Y-m-d');
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
                'id'           => $event->id,
                'title'        => $event->title,
                'description'  => $event->description,
                'start_date'   => $event->start_date->format('Y-m-d'),
                'start_time'   => $event->formatted_start_time,
                'end_date'     => $event->effective_end_date->format('Y-m-d'),
                'end_time'     => $event->formatted_end_time,
                'color'        => $event->color,
                'creator_name' => $event->user?->name ?? 'N/A',
                'participants' => collect($event->combined_participants)->map(fn ($p) => [
                    'id'   => $p['id'],
                    'name' => $p['name'],
                ])->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data->toArray(),
        ]);
    }

    /**
     * Return all active users as JSON for cross-system participant selection.
     *
     * GET /api/users?token=xxx
     */
    public function users(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        if ($request->input('token') !== config('services.greeco.api_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::query()
            ->with('department')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'department' => $u->department?->name ?? 'Nhân viên',
            ]);

        return response()->json([
            'success' => true,
            'data'    => $users->toArray(),
        ]);
    }

    /**
     * Receive a cross-system notification request from Greeco.
     * Greeco calls this when it adds Bảo Châu users to a duty schedule.
     *
     * POST /api/notify
     * Body (JSON): { token, user_ids[], event_title, creator_name, action, event_date, event_time_label }
     */
    public function notify(Request $request): JsonResponse
    {
        $request->validate([
            'token'           => ['required', 'string'],
            'user_ids'        => ['required', 'array'],
            'user_ids.*'      => ['integer'],
            'event_title'     => ['required', 'string'],
            'creator_name'    => ['required', 'string'],
            'action'          => ['nullable', 'string', 'in:added,updated,deleted'],
            'event_date'      => ['nullable', 'date'],
            'event_time_label' => ['nullable', 'string'],
        ]);

        if ($request->input('token') !== config('services.greeco.api_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $users = User::whereIn('id', $request->input('user_ids', []))
            ->where('is_active', true)
            ->get();

        if ($users->isEmpty()) {
            return response()->json(['success' => true, 'notified' => 0]);
        }

        $notification = new WorkScheduleNotification(
            eventTitle: $request->input('event_title'),
            userName: $request->input('creator_name'),
            action: $request->input('action', 'added'),
            eventDate: $request->input('event_date'),
            eventTimeLabel: $request->input('event_time_label'),
        );

        foreach ($users as $user) {
            $user->notify($notification);
        }

        return response()->json(['success' => true, 'notified' => $users->count()]);
    }
}
