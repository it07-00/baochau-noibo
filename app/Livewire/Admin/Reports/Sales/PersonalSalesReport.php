<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractCommercial;
use App\Models\ContractConsulting;
use App\Models\ContractEnergy;
use App\Models\ContractProject;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\ProgressiveSales;
use App\Models\RenewalSales;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PersonalSalesReport extends Component
{
    public int $year;
    public string $filter_staff = '';

    protected array $contractModels = [
        ContractWaste::class,
        ContractConsulting::class,
        ContractProject::class,
        ContractCommercial::class,
        ContractSustainability::class,
        ContractEnergy::class,
    ];

    private function canViewAllSalesStaff(): bool
    {
        return auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly', 'tp-kinh-doanh']);
    }

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');

        // Nhóm không có quyền xem toàn bộ sẽ mặc định xem dữ liệu của chính mình.
        if (! $this->canViewAllSalesStaff()) {
            $this->filter_staff = (string) auth()->id();
        }
    }

    public function render()
    {
        $staffId = $this->filter_staff;

        // Nhóm không có quyền xem toàn bộ sẽ khóa theo chính mình khi chưa chọn filter.
        if (! $staffId && ! $this->canViewAllSalesStaff()) {
            $staffId = (string) auth()->id();
        }

        $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
        $targetStaffIds = $staffId !== '' ? [(int) $staffId] : $salesStaffIds;

        $targetsByMonth = SalesTarget::query()
            ->where('year', $this->year)
            ->when(
                !empty($targetStaffIds),
                fn($q) => $q->whereIn('staff_id', $targetStaffIds),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->selectRaw('month as m, SUM(target_amount) as total')
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $actualByMonth = array_fill(1, 12, 0.0);
        if (!empty($targetStaffIds)) {
            foreach ($this->contractModels as $modelClass) {
                $rows = $modelClass::query()
                    ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->selectRaw('MONTH(COALESCE(submitted_at, signed_at)) as m, SUM(value) as total')
                    ->groupBy('m')
                    ->get();

                foreach ($rows as $r) {
                    $month = (int) $r->m;
                    if ($month >= 1 && $month <= 12) {
                        $actualByMonth[$month] += (float) $r->total;
                    }
                }
            }
        }

        $potentialByMonth = RenewalSales::query()
            ->whereYear('sales_month', $this->year)
            ->when(
                !empty($targetStaffIds),
                fn($q) => $q->whereIn('user_id', $targetStaffIds),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->where('status', 'Báo giá tiềm năng')
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as total')
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $sampleContractByMonth = RenewalSales::query()
            ->whereYear('sales_month', $this->year)
            ->when(
                !empty($targetStaffIds),
                fn($q) => $q->whereIn('user_id', $targetStaffIds),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->where('status', 'Hợp đồng mẫu')
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as total')
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $officialContractByMonth = RenewalSales::query()
            ->whereYear('sales_month', $this->year)
            ->when(
                !empty($targetStaffIds),
                fn($q) => $q->whereIn('user_id', $targetStaffIds),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->where('status', 'Đã ký')
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as total')
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $renewalByMonth = RenewalSales::query()
            ->whereYear('sales_month', $this->year)
            ->when(
                !empty($targetStaffIds),
                fn($q) => $q->whereIn('user_id', $targetStaffIds),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as total')
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $progressiveByMonth = ProgressiveSales::query()
            ->whereYear('sales_month', $this->year)
            ->when(
                !empty($targetStaffIds),
                fn($q) => $q->whereIn('user_id', $targetStaffIds),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->selectRaw('MONTH(sales_month) as m, SUM(amount) as total')
            ->groupBy('m')
            ->get()
            ->keyBy('m');

        $personalRows = [];
        $pipelineRows = [];
        $renewalProgressRows = [];
        $forecastRows = [];

        $runningTarget = 0.0;
        $runningActual = 0.0;
        $runningForecast = 0.0;

        for ($m = 1; $m <= 12; $m++) {
            $target = (float) ($targetsByMonth->get($m)?->total ?? 0);
            $actual = (float) ($actualByMonth[$m] ?? 0);

            $potential = (float) ($potentialByMonth->get($m)?->total ?? 0);
            $sampleContract = (float) ($sampleContractByMonth->get($m)?->total ?? 0);
            $officialContract = (float) ($officialContractByMonth->get($m)?->total ?? 0);
            $pipelineTotal = $potential + $sampleContract + $officialContract;

            $renewal = (float) ($renewalByMonth->get($m)?->total ?? 0);
            $progressive = (float) ($progressiveByMonth->get($m)?->total ?? 0);
            $renewalProgressTotal = $renewal + $progressive;

            $runningTarget += $target;
            $runningActual += $actual;

            $personalRows[$m] = [
                'month' => $m,
                'target' => $target,
                'target_cumulative' => $runningTarget,
                'actual' => $actual,
                'actual_cumulative' => $runningActual,
                'remaining' => max(0, $runningTarget - $runningActual),
                'kpi_pct' => $runningTarget > 0 ? round(($runningActual / $runningTarget) * 100) : null,
            ];

            $pipelineRows[$m] = [
                'month' => $m,
                'potential_quote' => $potential,
                'sample_contract' => $sampleContract,
                'official_contract' => $officialContract,
                'total' => $pipelineTotal,
            ];

            $renewalProgressRows[$m] = [
                'month' => $m,
                'renewal' => $renewal,
                'progressive' => $progressive,
                'total' => $renewalProgressTotal,
            ];

            $forecastCurrent = $actual + $pipelineTotal + $renewalProgressTotal;
            $runningForecast += $forecastCurrent;

            $forecastRows[$m] = [
                'month' => $m,
                'forecast_total' => $forecastCurrent,
                'forecast_cumulative' => $runningForecast,
                'forecast_kpi_pct' => $runningTarget > 0 ? round(($runningForecast / $runningTarget) * 100) : null,
                'remaining_to_run' => max(0, $runningTarget - $runningForecast),
            ];
        }

        $personalTotals = [
            'target' => array_sum(array_column($personalRows, 'target')),
            'target_cumulative' => $runningTarget,
            'actual' => array_sum(array_column($personalRows, 'actual')),
            'actual_cumulative' => $runningActual,
            'remaining' => max(0, $runningTarget - $runningActual),
            'kpi_pct' => $runningTarget > 0 ? round(($runningActual / $runningTarget) * 100) : null,
        ];

        $pipelineTotals = [
            'potential_quote' => array_sum(array_column($pipelineRows, 'potential_quote')),
            'sample_contract' => array_sum(array_column($pipelineRows, 'sample_contract')),
            'official_contract' => array_sum(array_column($pipelineRows, 'official_contract')),
            'total' => array_sum(array_column($pipelineRows, 'total')),
        ];

        $renewalProgressTotals = [
            'renewal' => array_sum(array_column($renewalProgressRows, 'renewal')),
            'progressive' => array_sum(array_column($renewalProgressRows, 'progressive')),
            'total' => array_sum(array_column($renewalProgressRows, 'total')),
        ];

        $forecastTotals = [
            'forecast_total' => array_sum(array_column($forecastRows, 'forecast_total')),
            'forecast_cumulative' => $runningForecast,
            'forecast_kpi_pct' => $runningTarget > 0 ? round(($runningForecast / $runningTarget) * 100) : null,
            'remaining_to_run' => max(0, $runningTarget - $runningForecast),
        ];

        $staffDetail = $staffId ? User::find((int) $staffId) : null;

        return view('livewire.admin.reports.sales.personal-sales-report', [
            'personalRows' => $personalRows,
            'personalTotals' => $personalTotals,
            'pipelineRows' => $pipelineRows,
            'pipelineTotals' => $pipelineTotals,
            'renewalProgressRows' => $renewalProgressRows,
            'renewalProgressTotals' => $renewalProgressTotals,
            'forecastRows' => $forecastRows,
            'forecastTotals' => $forecastTotals,
            'staffDetail' => $staffDetail,
            'staffs'      => User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get(),
            'years'       => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'hasStaffFilter' => (bool) $staffId,
        ])->layout('admin.layouts.app', ['title' => 'Bảng doanh số cá nhân']);
    }
}
