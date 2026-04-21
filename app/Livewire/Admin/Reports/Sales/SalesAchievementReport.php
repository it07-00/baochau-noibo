<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    public function render()
    {
        $staffs = User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get();
        $staffIds = $staffs->pluck('id')->all();

        // ── Actual sales per staff from 6 contract types ──
        // Doanh số tính theo cột "revenue", lọc theo tháng xuất hóa đơn (submitted_at)
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

        // ── Targets per staff ──
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

        // ── Doanh Số rankings ──
        $doanhSoRankings = $staffs->map(fn($user) => [
            'name'       => $user->name,
            'avatar_url' => $user->avatar_url,
            'total'      => (float) ($actualByStaff[$user->id] ?? 0),
        ])->sortByDesc('total')->values();

        $maxDoanhSo = $doanhSoRankings->max('total') ?: 1;

        // ── KPI rankings ──
        $kpiRankings = $staffs->map(function ($user) use ($actualByStaff, $targetByStaff) {
            $actual = (float) ($actualByStaff[$user->id] ?? 0);
            $target = (float) ($targetByStaff[$user->id] ?? 0);
            $pct = $target > 0 ? round($actual / $target * 100, 0) : 0;

            return [
                'name'       => $user->name,
                'avatar_url' => $user->avatar_url,
                'pct'        => (int) $pct,
            ];
        })->sortByDesc('pct')->values();

        $maxKpi = $kpiRankings->max('pct') ?: 1;

        // ── Company totals ──
        $companyTarget = array_sum($targetByStaff) ?: 0;
        $companyActual = array_sum($actualByStaff) ?: 0;
        $companyPct = $companyTarget > 0 ? round($companyActual / $companyTarget * 100, 0) : 0;

        return view('livewire.admin.reports.sales.sales-achievement-report', [
            'doanhSoRankings' => $doanhSoRankings,
            'kpiRankings'     => $kpiRankings,
            'maxDoanhSo'      => $maxDoanhSo,
            'maxKpi'          => $maxKpi,
            'companyTarget'   => $companyTarget,
            'companyActual'   => $companyActual,
            'companyPct'      => $companyPct,
            'years'           => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'months'          => range(1, 12),
        ])->layout('admin.layouts.app', ['title' => 'Đường đua doanh số']);
    }
}
