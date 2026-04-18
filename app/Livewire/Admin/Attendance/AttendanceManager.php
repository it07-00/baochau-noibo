<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceImport;
use App\Models\AttendanceLog;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;

class AttendanceManager extends Component
{
    use WithFileUploads;

    public $userFile;
    public $attlogFile;
    public string $selectedMonth;
    public bool $showImportModal = false;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function import(): void
    {
        $this->validate([
            'userFile'   => 'required|file|max:2048',
            'attlogFile' => 'required|file|max:10240',
        ]);

        // 1. Parse user.dat (binary)
        $userContent = file_get_contents($this->userFile->getRealPath());
        $employees = $this->parseUserDat($userContent);

        // 2. Upsert employees
        foreach ($employees as $uid => $name) {
            AttendanceEmployee::updateOrCreate(
                ['device_uid' => $uid],
                ['name' => $name],
            );
        }

        // 3. Parse attlog.dat (text)
        $attlogContent = file_get_contents($this->attlogFile->getRealPath());
        $logs = $this->parseAttlog($attlogContent);

        // 4. Determine month from data
        $firstLog = collect($logs)->first();
        $month = $firstLog ? Carbon::parse($firstLog['datetime'])->format('Y-m') : now()->format('Y-m');

        // 5. Delete old logs for this month then insert
        $employeeMap = AttendanceEmployee::pluck('id', 'device_uid');

        // Remove existing logs for the month
        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth   = Carbon::parse($month . '-01')->endOfMonth();

        AttendanceLog::whereBetween('checked_at', [$startOfMonth, $endOfMonth])->delete();

        // 6. Insert new logs
        $inserted = 0;
        $batchSize = 500;
        $batch = [];

        foreach ($logs as $log) {
            $employeeId = $employeeMap[$log['uid']] ?? null;
            if (!$employeeId) continue;

            $batch[] = [
                'employee_id' => $employeeId,
                'checked_at'  => $log['datetime'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
            $inserted++;

            if (count($batch) >= $batchSize) {
                AttendanceLog::insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            AttendanceLog::insert($batch);
        }

        // 7. Record import
        AttendanceImport::create([
            'imported_by'   => auth()->id(),
            'month'         => $month,
            'total_records' => $inserted,
        ]);

        $this->selectedMonth = $month;
        $this->showImportModal = false;
        $this->reset(['userFile', 'attlogFile']);

        session()->flash('success', "Import thành công {$inserted} bản ghi cho tháng {$month}.");
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

    private function parseUserDat(string $binary): array
    {
        $employees = [];
        $recordSize = 72; // Each record is 72 bytes in user.dat

        $length = strlen($binary);
        $offset = 0;

        while ($offset + $recordSize <= $length) {
            $record = substr($binary, $offset, $recordSize);

            // UID is at bytes 0-1 (little-endian uint16)
            $uid = unpack('v', substr($record, 0, 2))[1];

            // Name starts at byte 11, null-terminated, up to ~24 chars
            $nameRaw = substr($record, 11, 24);
            $name = rtrim(explode("\x00", $nameRaw)[0]);

            if ($uid > 0 && $name !== '') {
                $employees[$uid] = $name;
            }

            $offset += $recordSize;
        }

        return $employees;
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
                $logs[] = [
                    'uid'      => $uid,
                    'datetime' => $datetime,
                ];
            }
        }

        return $logs;
    }
}
