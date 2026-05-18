<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractResearch;
use App\Models\ContractLegal;
use App\Models\ContractEmission;
use App\Models\ContractTechnical;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\User;
use Livewire\Component;

class SalesSummaryReport extends Component
{
    public int $year;
    public string $filter_staff = '';
    public int $filter_month = 0;

    protected array $contractModelClasses = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    protected array $contractTypeLabels = [
        ContractWaste::class          => 'Chất thải',
        ContractLegal::class     => 'Pháp lý & Hồ sơ MT',
        ContractTechnical::class        => 'Ứng phó sự cố',
        ContractResearch::class     => 'Nghiên cứu và chuyển đổi công nghệ',
        ContractSustainability::class => 'Phát triển bền vững',
        ContractEmission::class         => 'Giảm phát thải, tiết kiệm năng lượng',
    ];

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'renewal'          => 0,
                'progressive'      => 0,
                'contract_total'   => 0,
                'renewal_count'    => 0,
                'progressive_count'=> 0,
            ];
        }

        $salesStaffIds  = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
        $targetStaffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;

        if (!empty($targetStaffIds)) {
            foreach ($this->contractModelClasses as $modelClass) {
                // DS Tái ký — tổng tiền (theo ngày xuất hóa đơn)
                foreach ($modelClass::query()
                    ->whereNotNull('submitted_at')
                    ->whereYear('submitted_at', $this->year)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->where('is_renewal', true)
                    ->selectRaw('MONTH(submitted_at) as m, SUM(revenue) as total, COUNT(*) as cnt')
                    ->groupBy('m')
                    ->get() as $r) {
                    $months[(int) $r->m]['renewal']       += (float) $r->total;
                    $months[(int) $r->m]['renewal_count'] += (int) $r->cnt;
                }

                // DS HĐ mới — tổng tiền (theo ngày xuất hóa đơn)
                foreach ($modelClass::query()
                    ->whereNotNull('submitted_at')
                    ->whereYear('submitted_at', $this->year)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->where(function ($q) {
                        $q->where('is_renewal', false)->orWhereNull('is_renewal');
                    })
                    ->selectRaw('MONTH(submitted_at) as m, SUM(revenue) as total, COUNT(*) as cnt')
                    ->groupBy('m')
                    ->get() as $r) {
                    $months[(int) $r->m]['progressive']       += (float) $r->total;
                    $months[(int) $r->m]['progressive_count'] += (int) $r->cnt;
                }
            }
        }

        for ($m = 1; $m <= 12; $m++) {
            $months[$m]['contract_total'] = $months[$m]['renewal'] + $months[$m]['progressive'];
        }

        $totals = [
            'renewal'          => array_sum(array_column($months, 'renewal')),
            'progressive'      => array_sum(array_column($months, 'progressive')),
            'contract_total'   => array_sum(array_column($months, 'contract_total')),
            'renewal_count'    => array_sum(array_column($months, 'renewal_count')),
            'progressive_count'=> array_sum(array_column($months, 'progressive_count')),
        ];
        $totals['grand'] = $totals['contract_total'];

        // ── Chi tiết theo tháng được chọn ───────────────
        $detail = collect();
        if ($this->filter_month > 0 && !empty($targetStaffIds)) {
            foreach ($this->contractModelClasses as $modelClass) {
                $contracts = $modelClass::query()
                    ->with('customer')
                    ->whereNotNull('submitted_at')
                    ->whereYear('submitted_at', $this->year)
                    ->whereMonth('submitted_at', $this->filter_month)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->get();

                foreach ($contracts as $contract) {
                    $detail->push([
                        'customer'   => $contract->customer?->name ?? '—',
                        'type'       => $this->contractTypeLabels[$modelClass],
                        'value'      => (float) $contract->revenue,
                        'is_renewal' => (bool) $contract->is_renewal,
                        'date'       => $contract->submitted_at,
                    ]);
                }
            }
            $detail = $detail->sortByDesc('date')->values();
        }

        $staffs = User::where('is_active', true)->whereIn('id', $salesStaffIds)->orderBy('name')->get();

        return view('livewire.admin.reports.sales.sales-summary-report', [
            'months'  => $months,
            'totals'  => $totals,
            'staffs'  => $staffs,
            'years'   => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'detail'  => $detail,
        ])->layout('admin.layouts.app', ['title' => 'Bảng tổng kết doanh số']);
    }
}
