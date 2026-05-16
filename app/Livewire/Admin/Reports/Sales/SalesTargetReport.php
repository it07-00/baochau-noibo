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
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesTargetReport extends Component
{
    public int $year;
    public string $filter_staff = '';
    public int $filter_month = 0;
    public array $detail = [];

    protected array $contractModels = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    protected array $contractTypeLabels = [
        ContractWaste::class          => 'Chất thải & Tiếng ồn',
        ContractLegal::class          => 'Pháp lý & Hồ sơ MT',
        ContractTechnical::class      => 'Kỹ thuật & Ứng phó SC',
        ContractResearch::class       => 'NC & CĐ Công nghệ',
        ContractSustainability::class => 'TV & BC PTBV',
        ContractEmission::class       => 'Phát thải & Năng lượng',
    ];

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
        // Default to all sales staff so report totals are not accidentally scoped to one person.
        $this->filter_staff = '';
    }

    public function openDetail(int $month): void
    {
        $this->filter_month = $month;
        $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
        $staffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;

        $detail = collect();
        if (!empty($staffIds)) {
            foreach ($this->contractModels as $modelClass) {
                $contracts = $modelClass::query()
                    ->with('customer', 'staff')
                    ->whereRaw('COALESCE(submitted_at, signed_at) IS NOT NULL')
                    ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                    ->whereMonth(DB::raw('COALESCE(submitted_at, signed_at)'), $month)
                    ->whereIn('staff_id', $staffIds)
                    ->get();

                foreach ($contracts as $contract) {
                    $detail->push([
                        'customer'   => $contract->customer?->name ?? '—',
                        'staff'      => $contract->staff?->name ?? '—',
                        'type'       => $this->contractTypeLabels[$modelClass],
                        'value'      => (float) $contract->revenue,
                        'is_renewal' => (bool) $contract->is_renewal,
                        'date'       => ($contract->submitted_at ?? $contract->signed_at)?->format('d/m/Y'),
                    ]);
                }
            }
        }

        $this->detail = $detail->sortByDesc('date')->values()->toArray();
        $this->dispatch('openDetailModal');
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
                    ->whereRaw('COALESCE(submitted_at, signed_at) IS NOT NULL')
                    ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                    ->when(
                        $this->filter_staff !== '',
                        fn($q) => $q->where('staff_id', $this->filter_staff),
                        fn($q) => $q->whereIn('staff_id', $salesStaffIds)
                    )
                    ->selectRaw('MONTH(COALESCE(submitted_at, signed_at)) as m, SUM(revenue) as total')
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
            'staffs'    => User::role(['kinh-doanh', 'tp-kinh-doanh'])->where('is_active', true)->orderBy('name')->get(),
            'years'     => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
        ])->layout('admin.layouts.app', ['title' => 'Bảng doanh số cam kết']);
    }
}
