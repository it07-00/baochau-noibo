<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\DailyReport;
use App\Models\SalesTarget;
use App\Models\User;
use Livewire\Component;

class SalesAchievementReport extends Component
{
    public int $year;

    public string $filter_month = '';

    protected array $contractModels = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
        $this->filter_month = (string) now()->month;
    }

    public function updatedYear(): void
    {
        $maxMonth = $this->year >= (int) now()->format('Y') ? (int) now()->format('n') : 12;
        if ($this->filter_month !== '' && (int) $this->filter_month > $maxMonth) {
            $this->filter_month = (string) $maxMonth;
        }
    }

    public function monthLabel(): string
    {
        return $this->filter_month !== ''
            ? 'Tháng '.str_pad($this->filter_month, 2, '0', STR_PAD_LEFT)
            : 'Cả năm';
    }

    public function raceInitials(string $name): string
    {
        $cleanName = preg_split('/\s*-\s*/', trim($name))[0] ?? '';
        $parts = array_values(array_filter(explode(' ', $cleanName), fn ($word) => mb_strlen($word) > 0));

        if (count($parts) === 0) {
            return '?';
        }

        if (count($parts) === 1) {
            return strtoupper(mb_substr($parts[0], 0, 2));
        }

        $first = mb_substr($parts[0], 0, 1);
        $last = mb_substr($parts[count($parts) - 1], 0, 1);

        return strtoupper($first.$last);
    }

    public function salesHasDailyReport(): bool
    {
        return DailyReport::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->exists();
    }

    public function render()
    {
        $staffs = User::role(['kinh-doanh', 'tp-kinh-doanh'])->where('is_active', true)->orderBy('name')->get();
        $staffIds = $staffs->pluck('id')->all();

        // Doanh số tính theo revenue, chỉ dùng submitted_at (ngày xuất hóa đơn)
        $actualByStaff = [];
        foreach ($this->contractModels as $modelClass) {
            $query = $modelClass::whereNotNull('submitted_at')
                ->whereYear('submitted_at', $this->year)
                ->whereNotNull('staff_id');

            if ($this->filter_month) {
                $query->whereMonth('submitted_at', $this->filter_month);
            }

            $rows = $query->selectRaw('staff_id, COALESCE(SUM(revenue), 0) as total')
                ->groupBy('staff_id')
                ->pluck('total', 'staff_id');

            foreach ($rows as $staffId => $total) {
                $staffId = (int) $staffId;
                $actualByStaff[$staffId] = ($actualByStaff[$staffId] ?? 0) + (float) $total;
            }
        }

        // Targets per staff
        $targetByStaff = [];
        $targetQuery = SalesTarget::where('year', $this->year)
            ->whereIn('staff_id', $staffIds);

        if ($this->filter_month) {
            $targetQuery->where('month', $this->filter_month);
        }

        foreach ($targetQuery->selectRaw('staff_id, SUM(target_amount) as total_target')
            ->groupBy('staff_id')
            ->get() as $r) {
            $targetByStaff[(int) $r->staff_id] = (float) $r->total_target;
        }

        // Doanh Số rankings
        $doanhSoRankings = $staffs->map(fn ($user) => [
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'total' => (float) ($actualByStaff[$user->id] ?? 0),
        ])->sortByDesc('total')->values();

        $maxDoanhSo = $doanhSoRankings->max('total') ?: 1;

        // ΓöÇΓöÇ KPI rankings ΓöÇΓöÇ
        $kpiRankings = $staffs->map(function ($user) use ($actualByStaff, $targetByStaff) {
            $actual = (float) ($actualByStaff[$user->id] ?? 0);
            $target = (float) ($targetByStaff[$user->id] ?? 0);
            $pct = $target > 0 ? round($actual / $target * 100, 0) : 0;

            return [
                'name' => $user->name,
                'avatar_url' => $user->avatar_url,
                'pct' => (int) $pct,
            ];
        })->sortByDesc('pct')->values();

        $maxKpi = $kpiRankings->max('pct') ?: 1;

        // Company totals
        $companyTarget = array_sum($targetByStaff) ?: 0;
        $companyActual = array_sum($actualByStaff) ?: 0;
        $companyPct = $companyTarget > 0 ? round($companyActual / $companyTarget * 100, 0) : 0;

        $maxMonth = $this->year >= (int) now()->format('Y') ? (int) now()->format('n') : 12;
        if ($this->filter_month !== '' && (int) $this->filter_month > $maxMonth) {
            $this->filter_month = (string) $maxMonth;
        }

        return view('livewire.admin.reports.sales.sales-achievement-report', [
            'doanhSoRankings' => $doanhSoRankings,
            'kpiRankings' => $kpiRankings,
            'maxDoanhSo' => $maxDoanhSo,
            'maxKpi' => $maxKpi,
            'companyTarget' => $companyTarget,
            'companyActual' => $companyActual,
            'companyPct' => $companyPct,
            'hasKpiTarget' => $companyTarget > 0,
            'years' => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'months' => range(1, $maxMonth),
        ])->layout('admin.layouts.app', [
            'title' => 'Đường đua Doanh Số',
            'fullWidth' => true,
        ]);
    }
}
