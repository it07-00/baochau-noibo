<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceImport;
use App\Models\AttendanceLog;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;

class AttendanceManager extends Component
{
    use WithFileUploads;

    public $attlogFile;
    public string $selectedMonth;
    public bool $showImportModal = false;

    // Import 2-step
    public int $importStep = 1;
    public array $parsedEmployees = [];   // [['uid'=>x, 'name'=>y], ...]
    public array $includedUids = [];      // UIDs được tích để import logs
    public array $detectedMonths = [];
    public array $selectedMonths = [];
    public string $importCacheKey = '';

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function openImportModal(): void
    {
        $this->resetImport();
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        if ($this->importCacheKey) {
            Cache::forget($this->importCacheKey);
        }
        $this->showImportModal = false;
        $this->resetImport();
    }

    private function resetImport(): void
    {
        $this->importStep = 1;
        $this->parsedEmployees = [];
        $this->includedUids = [];
        $this->detectedMonths = [];
        $this->selectedMonths = [];
        $this->importCacheKey = '';
        $this->reset(['attlogFile']);
    }

    public function analyze(): void
    {
        $this->validate([
            'attlogFile' => 'required|file|extensions:dat,txt,log,csv|max:10240',
        ]);

        $attlogContent = file_get_contents($this->attlogFile->getRealPath());
        $logs = $this->parseAttlog($attlogContent);

        $uidsInLog = collect($logs)->pluck('uid')->unique()->toArray();

        $existingEmployees = AttendanceEmployee::whereIn('device_uid', $uidsInLog)->get()->keyBy('device_uid');
        $blockedUids = AttendanceEmployee::where('is_blocked', true)->pluck('device_uid')->toArray();

        $this->parsedEmployees = collect($uidsInLog)
            ->map(fn($uid) => [
                'uid'        => $uid,
                'name'       => $existingEmployees[$uid]->name ?? '(Chưa đăng ký)',
                'is_blocked' => in_array($uid, $blockedUids),
                'is_unknown' => !isset($existingEmployees[$uid]),
            ])
            ->sortBy('uid')
            ->values()
            ->toArray();

        $this->includedUids = collect($this->parsedEmployees)
            ->where('is_blocked', false)
            ->where('is_unknown', false)
            ->pluck('uid')
            ->toArray();

        $this->detectedMonths = collect($logs)
            ->map(fn($l) => Carbon::parse($l['datetime'])->format('Y-m'))
            ->unique()->sort()->values()->toArray();

        $this->selectedMonths = $this->detectedMonths;

        $this->importCacheKey = 'att_import_' . auth()->id() . '_' . now()->timestamp;
        Cache::put($this->importCacheKey, $logs, now()->addMinutes(30));

        $this->importStep = 2;
        $this->reset(['attlogFile']);
    }

    public function import(): void
    {
        if (empty($this->selectedMonths)) {
            $this->addError('selectedMonths', 'Vui lòng chọn ít nhất một tháng để import.');
            return;
        }

        $logs = Cache::get($this->importCacheKey, []);

        $blockedUids = AttendanceEmployee::where('is_blocked', true)->pluck('device_uid')->toArray();
        $importUids  = array_values(array_diff($this->includedUids, $blockedUids));

        // Tạo NV mới cho các UID chưa có trong DB (is_unknown) nếu đã điền tên
        foreach ($this->parsedEmployees as $emp) {
            if (!$emp['is_unknown'] || !in_array($emp['uid'], $importUids)) continue;
            $name = trim($emp['name']);
            if (!$name || $name === '(Chưa đăng ký)') continue;
            AttendanceEmployee::firstOrCreate(
                ['device_uid' => $emp['uid']],
                ['name' => $name, 'is_active' => true, 'is_blocked' => false],
            );
        }

        $employeeMap = AttendanceEmployee::whereIn('device_uid', $importUids)->pluck('id', 'device_uid');

        $logsByMonth = collect($logs)
            ->filter(fn($l) => in_array(Carbon::parse($l['datetime'])->format('Y-m'), $this->selectedMonths))
            ->filter(fn($l) => in_array($l['uid'], $importUids))
            ->groupBy(fn($l) => Carbon::parse($l['datetime'])->format('Y-m'));

        $totalInserted = 0;

        foreach ($logsByMonth as $month => $monthLogs) {
            $start = Carbon::parse($month . '-01')->startOfMonth();
            $end   = Carbon::parse($month . '-01')->endOfMonth();

            AttendanceLog::whereBetween('checked_at', [$start, $end])
                ->whereIn('employee_id', $employeeMap->values())
                ->delete();

            $batch = [];
            foreach ($monthLogs as $log) {
                $employeeId = $employeeMap[$log['uid']] ?? null;
                if (!$employeeId) continue;

                $batch[] = [
                    'employee_id' => $employeeId,
                    'checked_at'  => $log['datetime'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                if (count($batch) >= 500) {
                    AttendanceLog::insert($batch);
                    $totalInserted += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                AttendanceLog::insert($batch);
                $totalInserted += count($batch);
            }

            AttendanceImport::create([
                'imported_by'   => auth()->id(),
                'month'         => $month,
                'total_records' => $monthLogs->count(),
            ]);
        }

        $this->selectedMonth = collect($this->selectedMonths)->sort()->last() ?? now()->format('Y-m');

        $monthCount = count($logsByMonth);
        $this->closeImportModal();

        session()->flash('success', "Import thành công {$totalInserted} bản ghi ({$monthCount} tháng).");
    }

    public function render()
    {
        $service = app(AttendanceService::class);
        $monthData = $service->getMonthData($this->selectedMonth, onlyWithLogs: false);

        $grid = [];
        foreach ($service->buildSummaryGrid($monthData['employees'], $monthData['logs'], $monthData['dates'], $monthData['startOfMonth']) as $row) {
            $grid[$row['employee']->id] = $row;
        }

        $lastImport = AttendanceImport::where('month', $this->selectedMonth)
            ->latest()
            ->first();

        return view('livewire.admin.attendance.attendance-manager', [
            'grid'        => $grid,
            'dates'       => $monthData['dates'],
            'daysInMonth' => $monthData['daysInMonth'],
            'lastImport'  => $lastImport,
        ])->layout('admin.layouts.app');
    }

    public function dayData(array $row, Carbon $date): ?array
    {
        return $row['days'][$date->day] ?? null;
    }

    public function isLate(?array $day): bool
    {
        return (bool) ($day && ($day['first'] ?? null) > '08:00');
    }

    public function isEarly(?array $day): bool
    {
        return (bool) ($day && !empty($day['last']) && $day['last'] < '17:00');
    }

    public function isAbsent(?array $day, Carbon $date): bool
    {
        return !$day && $date->lte(now());
    }

    public function attendanceCellStyle(?array $day, Carbon $date): array
    {
        $isLate = $this->isLate($day);
        $isEarly = $this->isEarly($day);
        $isAbsent = $this->isAbsent($day, $date);

        if ($isAbsent || ($day && $isLate && $isEarly)) {
            return ['bg' => '#dc3545', 'color' => '#fff'];
        }

        if ($day && $isLate) {
            return ['bg' => '#fd7e14', 'color' => '#fff'];
        }

        if ($day && $isEarly) {
            return ['bg' => '#ffc107', 'color' => '#000'];
        }

        if ($day) {
            return ['bg' => '#198754', 'color' => '#fff'];
        }

        return ['bg' => 'transparent', 'color' => 'inherit'];
    }

    private function parseAttlog(string $content): array
    {
        $logs = [];
        $lines = preg_split('/\r?\n/', trim($content));

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = preg_split('/\t/', $line);
            if (count($parts) < 2) continue;

            $uid = (int) trim($parts[0]);
            $datetime = trim($parts[1]);

            if ($uid > 0 && strtotime($datetime)) {
                $logs[] = ['uid' => $uid, 'datetime' => $datetime];
            }
        }

        return $logs;
    }
}
