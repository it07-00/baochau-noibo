<?php

namespace App\Livewire\Admin;

use App\Enums\Role as RoleEnum;
use App\Services\StatisticsService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class StatisticsBoard extends Component
{
    public int $year;

    public array $years = [];

    public string $month = '';

    public string $contractDateFrom = '';

    public string $contractDateTo = '';

    public string $chartMode = 'quarter'; // 'quarter' | 'year'

    public array $itStats = [];

    public array $envData = [];

    public bool $showEnv = false;

    public string $activeTab = 'overview'; // overview, security, env

    public string $filter_staff = '';

    protected $listeners = ['it-action-completed' => '$refresh'];

    public function updatedFilterStaff(): void
    {
        $this->dispatch('chart-updated');
    }

    public function mount(): void
    {
        if (auth()->user()->hasRole(RoleEnum::THUC_TAP->value)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        if (auth()->user()->hasRole(RoleEnum::MARKETING->value)) {
            $this->redirect(route('app.marketing.content.index'), navigate: true);
        }

        $this->year = now()->year;
        $this->month = (string) now()->month;
        $this->years = range(now()->year, now()->year - 4);

        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(RoleEnum::KINH_DOANH->value)
            && ! $user->hasAnyRole([RoleEnum::GIAM_DOC->value, RoleEnum::TP_KINH_DOANH->value, RoleEnum::IT->value]);
        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
        } else {
            $this->filter_staff = '';
        }
    }

    public function updatedChartMode(): void
    {
        $this->dispatch('chart-updated');
    }

    public function updatedYear(): void
    {
        $this->dispatch('chart-updated');
    }

    public function maximumVisibleMonth(): int
    {
        if ($this->year === now()->year) {
            return now()->month;
        }
        return 12;
    }

    public function updatedMonth(): void
    {
        $this->dispatch('chart-updated');
    }

    public function updatedContractDateFrom(): void
    {
        $this->dispatch('chart-updated');
    }

    public function updatedContractDateTo(): void
    {
        $this->dispatch('chart-updated');
    }

    public function clearContractDateFilter(): void
    {
        $this->contractDateFrom = '';
        $this->contractDateTo = '';
        $this->dispatch('chart-updated');
    }

    public function clearCache()
    {
        if (! auth()->user()->hasRole(RoleEnum::IT->value)) {
            return;
        }

        try {
            Artisan::call('optimize:clear');
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã dọn dẹp toàn bộ bộ nhớ đệm (Cache) và tối ưu hóa hệ thống.']);
        } catch (\Exception $e) {
            Log::error('IT Dash ClearCache Error: '.$e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Gặp lỗi khi dọn dẹp cache.']);
        }
    }

    public function clearLogs()
    {
        if (! auth()->user()->hasRole(RoleEnum::IT->value)) {
            return;
        }

        try {
            $logFile = storage_path('logs/laravel.log');
            if (File::exists($logFile)) {
                File::put($logFile, '');
                $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã làm trống tệp nhật ký hệ thống (laravel.log).']);
                $this->itStats = []; // Force reload IT stats
            } else {
                $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Hiện không có tệp nhật ký nào.']);
            }
        } catch (\Exception $e) {
            Log::error('IT Dash ClearLogs Error: '.$e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Gặp lỗi khi xóa logs.']);
        }
    }

    public function loadEnv()
    {
        if (! auth()->user()->hasRole(RoleEnum::IT->value)) {
            return;
        }

        $path = base_path('.env');
        if (! file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->envData = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $this->envData[trim($key)] = trim($value, '"\' ');
            }
        }
    }

    public function saveEnv()
    {
        if (! auth()->user()->hasRole(RoleEnum::IT->value)) {
            return;
        }

        try {
            if (empty($this->envData) || ! isset($this->envData['APP_KEY'])) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Lỗi: Dữ liệu môi trường không hợp lệ hoặc thiếu APP_KEY.']);

                return;
            }

            $path = base_path('.env');
            $backupPath = base_path('.env.bak');
            File::copy($path, $backupPath);

            $content = '';
            foreach ($this->envData as $key => $value) {
                $content .= "{$key}=".(strpos($value, ' ') !== false ? "\"{$value}\"" : $value)."\n";
            }

            File::put($path, $content);
            Artisan::call('config:clear');

            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tệp .env và làm mới cấu hình.']);
            $this->showEnv = false;
        } catch (\Exception $e) {
            Log::error('IT Dash SaveEnv Error: '.$e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Lỗi khi lưu tệp .env.']);
        }
    }

    public function toggleEnv()
    {
        $this->showEnv = ! $this->showEnv;
        if ($this->showEnv) {
            $this->loadEnv();
        }
    }

    public function setTab($tab)
    {
        if (! in_array($tab, ['overview', 'security'], true)) {
            $this->activeTab = 'overview';

            return;
        }

        $this->activeTab = $tab;
    }

    public function render(StatisticsService $statisticsService)
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(RoleEnum::KINH_DOANH->value)
            && ! $user->hasAnyRole([RoleEnum::GIAM_DOC->value, RoleEnum::TP_KINH_DOANH->value, RoleEnum::IT->value]);
        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
        }

        $canFilterStaff = $user->hasAnyRole([RoleEnum::GIAM_DOC->value, RoleEnum::TP_KINH_DOANH->value, RoleEnum::IT->value]);

        $data = $statisticsService->getDashboardData(
            $user,
            $this->year,
            $this->month,
            $this->contractDateFrom,
            $this->contractDateTo,
            $this->chartMode,
            $this->itStats,
            $this->envData,
            $this->activeTab,
            $this->filter_staff
        );

        // Synchronize IT stats back to component state so we cache them during AJAX re-renders
        if (isset($data['itStats'])) {
            $this->itStats = $data['itStats'];
        }

        $staffs = collect();
        if ($canFilterStaff) {
            $salesStaffIds = \App\Models\User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
            $staffs = \App\Models\User::where('is_active', true)->whereIn('id', $salesStaffIds)->orderBy('name')->get();
        }

        $data['staffs'] = $staffs;
        $data['canFilterStaff'] = $canFilterStaff;

        return view('livewire.admin.statistics-board', $data)
            ->layout('admin.layouts.app');
    }
}
