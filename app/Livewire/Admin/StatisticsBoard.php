<?php

namespace App\Livewire\Admin;

use App\Models\ContractCommercial;
use App\Models\ContractConsulting;
use App\Models\ContractEnergy;
use App\Models\ContractProject;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\ContractAssignment;
use App\Models\ContractPaymentSchedule;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\DailyReport;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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

    protected $listeners = ['it-action-completed' => '$refresh'];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function updatedChartMode(): void
    {
        $this->dispatch('chart-updated');
    }

    public function updatedYear(): void
    {
        $this->dispatch('chart-updated');
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
        if (!auth()->user()->hasRole('it')) return;

        try {
            Artisan::call('optimize:clear');
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã dọn dẹp toàn bộ bộ nhớ đệm (Cache) và tối ưu hóa hệ thống.']);
        } catch (\Exception $e) {
            Log::error("IT Dash ClearCache Error: " . $e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Gặp lỗi khi dọn dẹp cache.']);
        }
    }

    public function clearLogs()
    {
        if (!auth()->user()->hasRole('it')) return;

        try {
            $logFile = storage_path('logs/laravel.log');
            if (File::exists($logFile)) {
                File::put($logFile, '');
                $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã làm trống tệp nhật ký hệ thống (laravel.log).']);
                $this->mount(); // Refresh data
            } else {
                $this->dispatch('swal:toast', ['type' => 'info', 'message' => 'Hiện không có tệp nhật ký nào.']);
            }
        } catch (\Exception $e) {
            Log::error("IT Dash ClearLogs Error: " . $e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Gặp lỗi khi xóa logs.']);
        }
    }

    public function loadEnv()
    {
        if (!auth()->user()->hasRole('it')) return;

        $path = base_path('.env');
        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->envData = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $this->envData[trim($key)] = trim($value, '"\' ');
            }
        }
    }

    public function saveEnv()
    {
        if (!auth()->user()->hasRole('it')) return;

        try {
            if (empty($this->envData) || !isset($this->envData['APP_KEY'])) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Lỗi: Dữ liệu môi trường không hợp lệ hoặc thiếu APP_KEY.']);
                return;
            }

            $path = base_path('.env');
            $backupPath = base_path('.env.bak');
            File::copy($path, $backupPath);

            $content = "";
            foreach ($this->envData as $key => $value) {
                $content .= "{$key}=" . (strpos($value, ' ') !== false ? "\"{$value}\"" : $value) . "\n";
            }

            File::put($path, $content);
            Artisan::call('config:clear');

            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tệp .env và làm mới cấu hình.']);
            $this->showEnv = false;
        } catch (\Exception $e) {
            Log::error("IT Dash SaveEnv Error: " . $e->getMessage());
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Lỗi khi lưu tệp .env.']);
        }
    }

    public function toggleEnv()
    {
        $this->showEnv = !$this->showEnv;
        if ($this->showEnv) $this->loadEnv();
    }

    public function setTab($tab)
    {
        if (!in_array($tab, ['overview', 'security'], true)) {
            $this->activeTab = 'overview';
            return;
        }

        $this->activeTab = $tab;
    }

    public function render()
    {
        $selectedMonth = $this->month !== '' ? (int) $this->month : null;

        $contractDateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->contractDateFrom) ? $this->contractDateFrom : null;
        $contractDateTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->contractDateTo) ? $this->contractDateTo : null;

        if ($contractDateFrom !== null && $contractDateTo !== null && $contractDateFrom > $contractDateTo) {
            [$contractDateFrom, $contractDateTo] = [$contractDateTo, $contractDateFrom];
        }

        $applyContractDateFilter = function ($query, ?int $monthForFallback = null, string $dateColumn = 'signed_at') use ($contractDateFrom, $contractDateTo) {
            if ($contractDateFrom !== null || $contractDateTo !== null) {
                if ($contractDateFrom !== null) {
                    $query->whereDate($dateColumn, '>=', $contractDateFrom);
                }
                if ($contractDateTo !== null) {
                    $query->whereDate($dateColumn, '<=', $contractDateTo);
                }

                return $query;
            }

            $query->whereYear($dateColumn, $this->year);
            if ($monthForFallback !== null) {
                $query->whereMonth($dateColumn, $monthForFallback);
            }

            return $query;
        };

        $resolveContractDateColumn = function (string $modelClass): string {
            static $dateColumnCache = [];

            if (isset($dateColumnCache[$modelClass])) {
                return $dateColumnCache[$modelClass];
            }

            $table = (new $modelClass())->getTable();

            if (Schema::hasColumn($table, 'date')) {
                return $dateColumnCache[$modelClass] = 'date';
            }

            if (Schema::hasColumn($table, 'signed_at')) {
                return $dateColumnCache[$modelClass] = 'signed_at';
            }

            return $dateColumnCache[$modelClass] = 'created_at';
        };

        // ── KPI tổng quan ──────────────────────────────
        $customerQuery = Customer::whereYear('created_at', $this->year);
        if ($selectedMonth !== null) {
            $customerQuery->whereMonth('created_at', $selectedMonth);
        }
        $totalCustomers = (int) $customerQuery->count();

        $contractTypes = [
            'Chất thải'   => ContractWaste::class,
            'Tư vấn'      => ContractConsulting::class,
            'Dự án'       => ContractProject::class,
            'Thương mại'  => ContractCommercial::class,
            'Năng lượng'  => ContractEnergy::class,
            'Bền vững'    => ContractSustainability::class,
        ];

        $byType = [];
        $totalContracts = 0;
        $totalContractValue = 0;

        foreach ($contractTypes as $label => $model) {
            $dateColumn = $resolveContractDateColumn($model);

            $yearOrDateQuery = $model::query();
            $applyContractDateFilter($yearOrDateQuery, null, $dateColumn);
            $row = $yearOrDateQuery
                ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(value),0) as val')
                ->first();

            $byType[$label] = [
                'count' => (int) ($row->cnt ?? 0),
                'value' => (float) ($row->val ?? 0),
            ];

            $kpiContractQuery = $model::query();
            $applyContractDateFilter($kpiContractQuery, $selectedMonth, $dateColumn);
            $totalContracts += (int) $kpiContractQuery->count();

            $kpiContractValueQuery = $model::query();
            $applyContractDateFilter($kpiContractValueQuery, $selectedMonth, $dateColumn);
            $totalContractValue += (float) $kpiContractValueQuery->sum('value');
        }

        // ── Doanh số ghi nhận từ cột doanh số (revenue) trong hợp đồng ──────
        $totalSales = 0;
        foreach ($contractTypes as $modelClass) {
            $dateColumn = $resolveContractDateColumn($modelClass);

            $modelQuery = $modelClass::query();
            $applyContractDateFilter($modelQuery, $selectedMonth, $dateColumn);
            $totalSales += (float) $modelQuery->sum('revenue');
        }

        // ── Doanh số thực thu (từ lịch thanh toán) ────
        $totalRevenue = (float) ContractPaymentSchedule::whereYear('paid_date', $this->year)
            ->whereIn('status', ['paid', 'partial'])->sum('paid_amount');

        $revenueByMonth = ContractPaymentSchedule::whereYear('paid_date', $this->year)
            ->whereIn('status', ['paid', 'partial'])
            ->selectRaw('MONTH(paid_date) as m, SUM(paid_amount) as total')
            ->groupByRaw('MONTH(paid_date)')->get()->keyBy('m');

        // ── Theo tháng: tất cả 6 loại HĐ ký ─────────
        $monthlyModels = [
            ContractWaste::class,
            ContractConsulting::class,
            ContractProject::class,
            ContractCommercial::class,
            ContractSustainability::class,
            ContractEnergy::class,
        ];

        $contractMonthly = [];
        foreach ($monthlyModels as $model) {
            $dateColumn = $resolveContractDateColumn($model);

            $monthlyQuery = $model::query();
            $applyContractDateFilter($monthlyQuery, null, $dateColumn);
            $rows = $monthlyQuery
                ->selectRaw("MONTH({$dateColumn}) as m, COUNT(*) as cnt, SUM(value) as val, SUM(revenue) as rev")
                ->groupByRaw("MONTH({$dateColumn})")
                ->get()
                ->keyBy('m');
            foreach ($rows as $m => $row) {
                $contractMonthly[$m]['cnt'] = ($contractMonthly[$m]['cnt'] ?? 0) + $row->cnt;
                $contractMonthly[$m]['val'] = ($contractMonthly[$m]['val'] ?? 0) + (float) $row->val;
                $contractMonthly[$m]['rev'] = ($contractMonthly[$m]['rev'] ?? 0) + (float) $row->rev;
            }
        }

        // ── Tiến độ thu tiền ────────────────────────
        $paymentDueByMonth = ContractPaymentSchedule::whereYear('due_date', $this->year)
            ->selectRaw('MONTH(due_date) as m, SUM(amount) as total')
            ->groupByRaw('MONTH(due_date)')->get()->keyBy('m');

        $paymentPaidByMonth = ContractPaymentSchedule::whereYear('paid_date', $this->year)
            ->selectRaw('MONTH(paid_date) as m, SUM(paid_amount) as total')
            ->groupByRaw('MONTH(paid_date)')->get()->keyBy('m');

        $totalPaymentDue  = (float) ContractPaymentSchedule::whereYear('due_date', $this->year)->sum('amount');
        $totalPaymentPaid = (float) ContractPaymentSchedule::whereYear('paid_date', $this->year)->sum('paid_amount');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = [
                'contracts'    => $contractMonthly[$m]['cnt'] ?? 0,
                'value'        => (float) ($contractMonthly[$m]['val'] ?? 0),
                'sales'        => (float) ($contractMonthly[$m]['rev'] ?? 0),
                'revenue'      => (float) ($revenueByMonth->get($m)?->total ?? 0),
                'payment_due'  => (float) ($paymentDueByMonth->get($m)?->total ?? 0),
                'payment_paid' => (float) ($paymentPaidByMonth->get($m)?->total ?? 0),
            ];
        }

        $currentUser = auth()->user();

        // ── Nhắc nhở báo cáo ngày ─────────────────────
        $dailyReportReminder = null;
        if (!$currentUser->hasRole('giam-doc')) {
            $hasReportToday = DailyReport::where('user_id', $currentUser->id)
                ->whereDate('date', today())
                ->exists();
            $dailyReportReminder = !$hasReportToday;
        }

        $canSeeTechnical  = $currentUser->hasAnyRole(['giam-doc', 'ky-thuat']);
        $canSeeConsulting = $currentUser->hasAnyRole(['giam-doc', 'tu-van', 'tp-kinh-doanh']);
        $canSeeFinance    = !$currentUser->hasAnyRole(['tu-van', 'ky-thuat']);

        // ── Insight theo tháng: báo giá vs ký hợp đồng theo dịch vụ/khu vực ──
        $insightMonth = $selectedMonth ?? (int) now()->month;

        $quotedByService = Quotation::whereYear('date', $this->year)
            ->whereMonth('date', $insightMonth)
            ->selectRaw("COALESCE(NULLIF(TRIM(service), ''), 'Khác') as label, COUNT(*) as cnt")
            ->groupByRaw("COALESCE(NULLIF(TRIM(service), ''), 'Khác')")
            ->pluck('cnt', 'label')
            ->toArray();

        $quotedByProvince = Quotation::whereYear('date', $this->year)
            ->whereMonth('date', $insightMonth)
            ->selectRaw("COALESCE(NULLIF(TRIM(province), ''), 'Không rõ') as label, COUNT(*) as cnt")
            ->groupByRaw("COALESCE(NULLIF(TRIM(province), ''), 'Không rõ')")
            ->pluck('cnt', 'label')
            ->toArray();

        $signedContractByService = [];
        $signedContractByProvince = [];
        $revenueByProvinceFromContracts = [];

        foreach (array_values($contractTypes) as $modelClass) {
            $dateColumn = $resolveContractDateColumn($modelClass);

            $serviceQuery = $modelClass::query();
            $applyContractDateFilter($serviceQuery, $insightMonth, $dateColumn);
            $serviceRows = $serviceQuery
                ->selectRaw("COALESCE(NULLIF(TRIM(loai_dich_vu), ''), 'Khác') as label, COUNT(*) as cnt")
                ->groupByRaw("COALESCE(NULLIF(TRIM(loai_dich_vu), ''), 'Khác')")
                ->get();

            foreach ($serviceRows as $row) {
                $label = (string) $row->label;
                $signedContractByService[$label] = ($signedContractByService[$label] ?? 0) + (int) $row->cnt;
            }

            $provinceQuery = $modelClass::query();
            $applyContractDateFilter($provinceQuery, $insightMonth, $dateColumn);
            $provinceRows = $provinceQuery
                ->selectRaw("COALESCE(NULLIF(TRIM(province), ''), 'Không rõ') as label, COUNT(*) as cnt, COALESCE(SUM(revenue), 0) as rev")
                ->groupByRaw("COALESCE(NULLIF(TRIM(province), ''), 'Không rõ')")
                ->get();

            foreach ($provinceRows as $row) {
                $label = (string) $row->label;
                $signedContractByProvince[$label] = ($signedContractByProvince[$label] ?? 0) + (int) $row->cnt;
                $revenueByProvinceFromContracts[$label] = ($revenueByProvinceFromContracts[$label] ?? 0) + (float) $row->rev;
            }
        }

        $serviceLabels = collect(array_keys($quotedByService))
            ->merge(array_keys($signedContractByService))
            ->unique()
            ->sort()
            ->values();

        $serviceInsightChart = [
            'labels' => $serviceLabels->all(),
            'quoted' => $serviceLabels->map(fn ($label) => (int) ($quotedByService[$label] ?? 0))->all(),
            'signed' => $serviceLabels->map(fn ($label) => (int) ($signedContractByService[$label] ?? 0))->all(),
        ];

        $regionLabels = collect(array_keys($quotedByProvince))
            ->merge(array_keys($signedContractByProvince))
            ->unique()
            ->sortByDesc(fn ($label) => (int) ($quotedByProvince[$label] ?? 0) + (int) ($signedContractByProvince[$label] ?? 0))
            ->take(10)
            ->values();

        $regionInsightChart = [
            'labels' => $regionLabels->all(),
            'quoted' => $regionLabels->map(fn ($label) => (int) ($quotedByProvince[$label] ?? 0))->all(),
            'signed' => $regionLabels->map(fn ($label) => (int) ($signedContractByProvince[$label] ?? 0))->all(),
            'revenue' => $regionLabels->map(fn ($label) => (float) ($revenueByProvinceFromContracts[$label] ?? 0))->all(),
        ];

        // ── Biểu đồ tư vấn: số dự án theo loại / quý hoặc cả năm ──
        $consultingTypes = [
            'Tư vấn'    => ContractConsulting::class,
            'Dự án'     => ContractProject::class,
            'Thương mại'=> ContractCommercial::class,
            'Bền vững'  => ContractSustainability::class,
            'Năng lượng'=> ContractEnergy::class,
        ];
        $consultingChartData = [];
        if ($canSeeConsulting) {
            if ($this->chartMode === 'quarter') {
                foreach ($consultingTypes as $label => $model) {
                    $dateColumn = $resolveContractDateColumn($model);

                    $qData = [];
                    for ($q = 1; $q <= 4; $q++) {
                        $startMonth = ($q - 1) * 3 + 1;
                        $endMonth   = $startMonth + 2;
                        $quarterQuery = $model::query();
                        $applyContractDateFilter($quarterQuery, null, $dateColumn);
                        $quarterQuery->whereMonth($dateColumn, '>=', $startMonth)
                            ->whereMonth($dateColumn, '<=', $endMonth);
                        $qData[] = (int) $quarterQuery->count();
                    }
                    $consultingChartData[$label] = $qData;
                }
            } else {
                // year mode: so sánh 5 năm gần nhất
                foreach ($consultingTypes as $label => $model) {
                    $dateColumn = $resolveContractDateColumn($model);

                    $yData = [];
                    foreach (array_reverse($this->years) as $y) {
                        if ($contractDateFrom !== null || $contractDateTo !== null) {
                            $yearModeQuery = $model::query();
                            $applyContractDateFilter($yearModeQuery, null, $dateColumn);
                            $yearModeQuery->whereYear($dateColumn, $y);
                            $yData[] = (int) $yearModeQuery->count();
                        } else {
                            $yData[] = (int) $model::whereYear($dateColumn, $y)->count();
                        }
                    }
                    $consultingChartData[$label] = $yData;
                }
            }
        }

        $technicalStats = collect();
        if ($canSeeTechnical) {
            $techUsers = User::role('ky-thuat')->get();
            $typeLabels = [
                ContractConsulting::class      => 'Pháp lý & Hồ sơ MT',
            ];

            foreach ($typeLabels as $modelClass => $label) {
                $dateColumn = $resolveContractDateColumn($modelClass);

                $assignments = ContractAssignment::where('assignable_type', $modelClass)
                    ->whereHas('assignable', fn ($q) => $applyContractDateFilter($q, null, $dateColumn))
                    ->with('assignable')
                    ->get();

                $count = $assignments->count();
                $value = $assignments->sum(fn ($a) => (float) ($a->assignable->value ?? 0));
                $completed = $assignments->filter(fn ($a) => ($a->assignable->workflow_status ?? '') === 'finished')->count();

                $technicalStats->push([
                    'label'     => $label,
                    'count'     => $count,
                    'value'     => $value,
                    'completed' => $completed,
                ]);
            }
        }

        // ── IT Admin Stats ───────────────────────────
        $isIT = $currentUser->hasRole('it');
        $itStats = [];
        $envData = [];
        $itStats = $this->itStats;
        if ($isIT) {
            // Role distribution
            $roleDistribution = Role::withCount('users')->get()->map(fn($r) => [
                'name'  => $r->name,
                'label' => $r->display_name ?: $r->name,
                'count' => $r->users_count
            ])->filter(fn($r) => $r['count'] > 0)->values();

            // System health
            $diskPath = base_path();
            $totalSpace = disk_total_space($diskPath) ?: 1;
            $freeSpace  = disk_free_space($diskPath) ?: 11;
            $usedSpace  = $totalSpace - $freeSpace;
            $diskUsagePercent = round(($usedSpace / $totalSpace) * 100, 1);

            // DB size (MySQL specific)
            $dbName = config('database.connections.mysql.database');
            $dbSizeResult = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);
            $dbSize = $dbSizeResult[0]->size_mb ?? 0;

            // Queues
            $pendingJobs = DB::table('jobs')->count();

            // Error logs
            $recentErrors = [];
            $logPath = storage_path('logs/laravel.log');
            if (File::exists($logPath)) {
                $lines = File::lines($logPath)->reverse()->take(100);
                $errorCount = 0;
                foreach ($lines as $line) {
                    if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
                        $recentErrors[] = $line;
                        $errorCount++;
                        if ($errorCount >= 10) break;
                    }
                }
            }

            $this->itStats = [
                'total_users'  => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'locked_users' => User::where('is_active', false)->count(),
                'recent_activities' => Activity::with('causer')->latest()->take(10)->get(),
                'role_distribution' => $roleDistribution,
                'top_users' => Activity::select('causer_id', 'causer_type', DB::raw('count(*) as total'))
                    ->where('causer_type', \App\Models\User::class)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('causer_id', 'causer_type')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->with('causer')
                    ->get(),
                'system' => [
                    'disk_total'   => round($totalSpace / (1024 ** 3), 2),
                    'disk_free'    => round($freeSpace / (1024 ** 3), 2),
                    'disk_used'    => round($usedSpace / (1024 ** 3), 2),
                    'disk_percent' => $diskUsagePercent,
                    'db_size_mb'   => $dbSize,
                    'pending_jobs' => $pendingJobs,
                    'active_sessions' => DB::table('sessions')->count(),
                    'php_version'  => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'failed_logins_24h' => Activity::where('log_name', 'auth')
                        ->where('description', 'like', '%Đăng nhập thất bại%')
                        ->where('created_at', '>=', now()->subDay())
                        ->count(),
                ],
                'recent_errors' => $recentErrors
            ];

            $itStats = $this->itStats;
            $envData = $this->envData;

            if ($this->activeTab === 'env' && empty($this->envData)) {
                $this->loadEnv();
            }
        }

        // ── Doanh số theo nguồn thông tin (Dynamic) ──────────
        $sourceSalesMap = [];

        foreach ($contractTypes as $modelClass) {
            $sourceField = ($modelClass === ContractWaste::class) ? 'source' : 'info_source';
            $dateColumn = $resolveContractDateColumn($modelClass);

            $modelQuery = $modelClass::query();
            $applyContractDateFilter($modelQuery, $selectedMonth, $dateColumn);

            $contractsOnSource = $modelQuery->select($sourceField, 'is_renewal', 'revenue')->get();

            foreach ($contractsOnSource as $c) {
                $val = (float) $c->revenue;
                if ($c->is_renewal) {
                    $label = 'TÁI KÝ';
                } else {
                    $label = mb_convert_case(trim($c->$sourceField ?? 'KHÁC'), MB_CASE_UPPER, "UTF-8");
                    if ($label === '') $label = 'KHÁC';
                }

                $sourceSalesMap[$label] = ($sourceSalesMap[$label] ?? 0) + $val;
            }
        }

        // Sắp xếp theo doanh số giảm dần
        arsort($sourceSalesMap);

        $sourceSalesChart = [
            'labels' => array_keys($sourceSalesMap),
            'datasets' => array_values($sourceSalesMap),
        ];

        return view('livewire.admin.statistics-board', compact(
            'totalCustomers', 'totalContracts', 'totalContractValue', 'totalSales',
            'totalRevenue', 'totalPaymentDue', 'totalPaymentPaid',
            'byType', 'monthly', 'canSeeTechnical', 'technicalStats',
            'canSeeConsulting', 'consultingChartData', 'canSeeFinance',
            'isIT', 'itStats', 'envData', 'dailyReportReminder',
            'insightMonth', 'serviceInsightChart', 'regionInsightChart',
            'sourceSalesChart'
        ))->layout('admin.layouts.app');
    }
}
