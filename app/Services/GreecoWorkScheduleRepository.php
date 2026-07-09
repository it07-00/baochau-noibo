<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class GreecoWorkScheduleRepository
{
    private string $apiUrl;
    private string $apiToken;

    public function __construct()
    {
        $this->apiUrl = rtrim((string) config('services.greeco.api_url'), '/');
        $this->apiToken = (string) config('services.greeco.api_token');
    }

    /**
     * Fetch work schedules from Greeco API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEventsInRange(string $start, string $end): array
    {
        if ($this->apiUrl === '' || $this->apiToken === '') {
            Log::warning('Greeco API is not configured');

            return [];
        }

        try {
            $response = Http::timeout(5)
                ->get("{$this->apiUrl}/api/duty-schedules", [
                    'start' => date('Y-m-d', strtotime($start)),
                    'end' => date('Y-m-d', strtotime($end)),
                    'token' => $this->apiToken,
                ]);

            if (!$response->successful()) {
                Log::warning('Greeco API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json('data', []);
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            Log::warning('Greeco API connection error', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Convert Greeco API item to a temporary WorkSchedule model instance.
     */
    public function toWorkScheduleModel(array $item): WorkSchedule
    {
        $schedule = new WorkSchedule();
        $schedule->setIncrementing(false);
        $schedule->setKeyType('string');

        // Use a string key for ID to differentiate from local DB IDs
        $startDate = (string) ($item['start_date'] ?? now()->toDateString());
        $endDate = $item['end_date'] ?? $startDate;

        $schedule->id = 'greeco_' . ($item['id'] ?? md5(json_encode($item)));
        $schedule->title = 'Greeco: ' . ($item['title'] ?? 'Work schedule');
        $schedule->description = $item['description'] ?? '';
        $schedule->start_date = Carbon::parse($startDate);
        $schedule->start_time = $this->normalizeTime($item['start_time'] ?? null);
        $schedule->end_date = $endDate && $endDate !== $startDate ? Carbon::parse((string) $endDate) : null;
        $schedule->end_time = $this->normalizeTime($item['end_time'] ?? null);
        $schedule->color = $this->mapColor($item['color'] ?? 'primary');
        $schedule->is_private = false;

        // Set participants relation
        $participants = collect($item['participants'] ?? [])->map(function ($p) {
            $u = new User();
            $u->name = $p['name'] ?? 'N/A';
            return $u;
        });
        $schedule->setRelation('participants', $participants);

        // Set creator relation
        $creator = new User();
        $creator->name = $item['creator_name'] ?? 'N/A';
        $schedule->setRelation('user', $creator);

        return $schedule;
    }

    /**
     * Map Greeco label color to Bao Chau color.
     */
    private function mapColor(string $color): string
    {
        $validColors = array_keys(WorkSchedule::COLORS);
        if (in_array($color, $validColors, true)) {
            return $color;
        }

        return match ($color) {
            'purple' => 'purple',
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            'info' => 'info',
            default => 'primary',
        };
    }

    private function normalizeTime(mixed $time): ?string
    {
        if ($time === null || trim((string) $time) === '') {
            return null;
        }

        return substr((string) $time, 0, 5);
    }
}
