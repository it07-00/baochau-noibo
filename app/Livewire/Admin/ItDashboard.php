<?php

namespace App\Livewire\Admin;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class ItDashboard extends Component
{
    public string $activeTab = 'overview'; // overview | logs | sessions

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole(RoleEnum::IT->value), 403);
    }

    public function setTab(string $tab): void
    {
        if (!in_array($tab, ['overview', 'logs', 'sessions'], true)) {
            return;
        }
        $this->activeTab = $tab;
    }

    public function clearCache(): void
    {
        abort_unless(auth()->user()->hasRole(RoleEnum::IT->value), 403);
        try {
            Artisan::call('optimize:clear');
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã dọn dẹp cache & tối ưu hệ thống.']);
        } catch (\Exception $e) {
            Log::error('ItDashboard clearCache: ' . $e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Lỗi khi xóa cache.']);
        }
    }

    public function clearLogs(): void
    {
        abort_unless(auth()->user()->hasRole(RoleEnum::IT->value), 403);
        try {
            $logFile = storage_path('logs/laravel.log');
            if (File::exists($logFile)) {
                File::put($logFile, '');
                $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã làm trống file log hệ thống.']);
            } else {
                $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Hiện không có file log nào.']);
            }
        } catch (\Exception $e) {
            Log::error('ItDashboard clearLogs: ' . $e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Lỗi khi xóa log.']);
        }
    }

    public function render()
    {
        // ── Disk ──────────────────────────────────────────
        $diskPath    = base_path();
        $diskTotal   = disk_total_space($diskPath) ?: 1;
        $diskFree    = disk_free_space($diskPath) ?: 0;
        $diskUsed    = $diskTotal - $diskFree;
        $diskPercent = round(($diskUsed / $diskTotal) * 100, 1);

        // ── DB size ───────────────────────────────────────
        $dbName = config('database.connections.mysql.database');
        $dbSize = 0;
        try {
            $row    = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);
            $dbSize = $row[0]->size_mb ?? 0;
        } catch (\Exception $e) {}

        // ── Queue ─────────────────────────────────────────
        $pendingJobs = 0;
        try { $pendingJobs = DB::table('jobs')->count(); } catch (\Exception $e) {}

        $failedJobs = 0;
        try { $failedJobs = DB::table('failed_jobs')->count(); } catch (\Exception $e) {}

        // ── Sessions ──────────────────────────────────────
        $activeSessions = 0;
        try { $activeSessions = DB::table('sessions')->count(); } catch (\Exception $e) {}

        // Sessions theo giờ (24h gần nhất) - dùng last_activity timestamp
        $sessionsByHour = [];
        try {
            $rows = DB::table('sessions')
                ->selectRaw('HOUR(FROM_UNIXTIME(last_activity)) as hr, COUNT(*) as cnt')
                ->where('last_activity', '>=', now()->subHours(24)->timestamp)
                ->groupByRaw('HOUR(FROM_UNIXTIME(last_activity))')
                ->orderByRaw('HOUR(FROM_UNIXTIME(last_activity))')
                ->get();
            foreach ($rows as $r) {
                $sessionsByHour[$r->hr] = $r->cnt;
            }
        } catch (\Exception $e) {}

        // ── Users ─────────────────────────────────────────
        $totalUsers  = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $lockedUsers = User::where('is_active', false)->count();

        $roleDistribution = Role::withCount('users')->get()
            ->map(fn($r) => ['name' => $r->name, 'count' => $r->users_count])
            ->filter(fn($r) => $r['count'] > 0)
            ->values();

        // ── Activity (lượt truy cập) ───────────────────────
        // Lượt hoạt động 7 ngày
        $activityByDay = Activity::selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('cnt', 'day');

        // Fill 7 ngày kể cả ngày không có log
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $last7Days[$d] = $activityByDay[$d] ?? 0;
        }

        $totalActivities7d = array_sum($last7Days);

        // Top users hoạt động (7 ngày)
        $topUsers = Activity::select('causer_id', 'causer_type', DB::raw('count(*) as total'))
            ->where('causer_type', User::class)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('causer_id', 'causer_type')
            ->orderByDesc('total')
            ->limit(8)
            ->with('causer')
            ->get();

        // ── Bảo mật ───────────────────────────────────────
        $failedLogins24h = Activity::where('log_name', 'auth')
            ->where('description', 'like', '%thất bại%')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $recentActivities = Activity::with('causer')->latest()->take(15)->get();

        // ── Error log ─────────────────────────────────────
        $recentErrors  = [];
        $logSizeKb     = 0;
        $logPath       = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            $logSizeKb = round(File::size($logPath) / 1024, 1);
            $lines = collect(File::lines($logPath))->reverse()->take(500);
            $count = 0;
            foreach ($lines as $line) {
                if (stripos($line, '.ERROR') !== false || stripos($line, '.CRITICAL') !== false) {
                    $recentErrors[] = trim($line);
                    if (++$count >= 20) break;
                }
            }
        }

        return view('livewire.admin.it-dashboard', [
            'disk'              => compact('diskTotal', 'diskFree', 'diskUsed', 'diskPercent'),
            'dbSize'            => $dbSize,
            'pendingJobs'       => $pendingJobs,
            'failedJobs'        => $failedJobs,
            'activeSessions'    => $activeSessions,
            'sessionsByHour'    => $sessionsByHour,
            'totalUsers'        => $totalUsers,
            'activeUsers'       => $activeUsers,
            'lockedUsers'       => $lockedUsers,
            'roleDistribution'  => $roleDistribution,
            'last7Days'         => $last7Days,
            'totalActivities7d' => $totalActivities7d,
            'topUsers'          => $topUsers,
            'failedLogins24h'   => $failedLogins24h,
            'recentActivities'  => $recentActivities,
            'recentErrors'      => $recentErrors,
            'logSizeKb'         => $logSizeKb,
            'phpVersion'        => PHP_VERSION,
            'laravelVersion'    => app()->version(),
        ])->layout('admin.layouts.app', ['title' => 'Quản trị hệ thống']);
    }
}
