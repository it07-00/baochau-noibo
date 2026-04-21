<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractResearch;
use App\Models\ContractLegal;
use App\Models\ContractEmission;
use App\Models\ContractTechnical;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\SalesTarget;
use App\Models\User;
use Livewire\Component;

class SalesTargetReport extends Component
{
    public int $year;
    public string $filter_staff = '';

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
        // Default to all sales staff so report totals are not accidentally scoped to one person.
        $this->filter_staff = '';
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ['target' => 0, 'actual' => 0];
        }

        $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();

        if (!empty($salesStaffIds)) {
            foreach (SalesTarget::query()
                ->where('year', $this->year)
                ->when(
                    $this->filter_staff !== '',
                    fn($q) => $q->where('staff_id', $this->filter_staff),
                    fn($q) => $q->whereIn('staff_id', $salesStaffIds)
                )
                ->selectRaw('month, SUM(target_amount) as total_target')
                ->groupBy('month')
                ->get() as $r) {
                $months[(int) $r->month]['target'] = (float) $r->total_target;
            }

            foreach ($this->contractModels as $modelClass) {
                $rows = $modelClass::query()
                    ->whereYear('signed_at', $this->year)
                    ->when(
                        $this->filter_staff !== '',
                        fn($q) => $q->where('staff_id', $this->filter_staff),
                        fn($q) => $q->whereIn('staff_id', $salesStaffIds)
                    )
                    ->selectRaw('MONTH(signed_at) as m, SUM(revenue) as total')
                    ->groupBy('m')
                    ->get();

                foreach ($rows as $r) {
                    $months[(int) $r->m]['actual'] += (float) $r->total;
                }
            }
        }

        $totals = ['target' => 0, 'actual' => 0];
        foreach ($months as $m) {
            $totals['target'] += $m['target'];
            $totals['actual'] += $m['actual'];
        }

        return view('livewire.admin.reports.sales.sales-target-report', [
            'months'    => $months,
            'totals'    => $totals,
            'staffs'    => User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get(),
            'years'     => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
        ])->layout('admin.layouts.app', ['title' => 'Bảng doanh số cam kết']);
    }
}
