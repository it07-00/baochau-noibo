<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractResearch;
use App\Models\ContractLegal;
use App\Models\ContractEmission;
use App\Models\ContractPaymentSchedule;
use App\Models\ContractTechnical;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        ContractWaste::class          => 'Chất thải & Tiếng ồn',
        ContractLegal::class     => 'Pháp lý & Hồ sơ MT',
        ContractTechnical::class        => 'Kỹ thuật & Ứng phó SC',
        ContractResearch::class     => 'NC & CĐ Công nghệ',
        ContractSustainability::class => 'TV & BC PTBV',
        ContractEmission::class         => 'Phát thải & Năng lượng',
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
                'payment_due'      => 0,
                'payment_paid'     => 0,
            ];
        }

        $salesStaffIds  = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
        $targetStaffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;

        if (!empty($targetStaffIds)) {
            foreach ($this->contractModelClasses as $modelClass) {
                // DS Tái ký — tổng tiền
                foreach ($modelClass::query()
                    ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->where('is_renewal', true)
                    ->selectRaw('MONTH(COALESCE(submitted_at, signed_at)) as m, SUM(revenue) as total, COUNT(*) as cnt')
                    ->groupBy('m')
                    ->get() as $r) {
                    $months[(int) $r->m]['renewal']       += (float) $r->total;
                    $months[(int) $r->m]['renewal_count'] += (int) $r->cnt;
                }

                // DS HĐ mới — tổng tiền
                foreach ($modelClass::query()
                    ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->where(function ($q) {
                        $q->where('is_renewal', false)->orWhereNull('is_renewal');
                    })
                    ->selectRaw('MONTH(COALESCE(submitted_at, signed_at)) as m, SUM(revenue) as total, COUNT(*) as cnt')
                    ->groupBy('m')
                    ->get() as $r) {
                    $months[(int) $r->m]['progressive']       += (float) $r->total;
                    $months[(int) $r->m]['progressive_count'] += (int) $r->cnt;
                }
            }
        }

        $paymentScheduleQuery = ContractPaymentSchedule::query();
        if (!empty($targetStaffIds)) {
            $paymentScheduleQuery->where(function ($q) use ($targetStaffIds) {
                foreach ($this->contractModelClasses as $modelClass) {
                    $q->orWhere(function ($sub) use ($modelClass, $targetStaffIds) {
                        $sub->where('contract_type', $modelClass)
                            ->whereIn('contract_id', $modelClass::query()
                                ->whereIn('staff_id', $targetStaffIds)
                                ->select('id'));
                    });
                }
            });
        } else {
            $paymentScheduleQuery->whereRaw('1 = 0');
        }

        foreach ((clone $paymentScheduleQuery)->whereYear('due_date', $this->year)
            ->selectRaw('MONTH(due_date) as m, SUM(amount) as total')
            ->groupBy('m')->get() as $r) {
            $months[$r->m]['payment_due'] = (float) $r->total;
        }

        foreach ((clone $paymentScheduleQuery)->whereYear('paid_date', $this->year)
            ->selectRaw('MONTH(paid_date) as m, SUM(paid_amount) as total')
            ->groupBy('m')->get() as $r) {
            $months[$r->m]['payment_paid'] = (float) $r->total;
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
            'payment_due'      => array_sum(array_column($months, 'payment_due')),
            'payment_paid'     => array_sum(array_column($months, 'payment_paid')),
        ];
        $totals['grand'] = $totals['contract_total'];

        // ── Chi tiết theo tháng được chọn ───────────────
        $detail = collect();
        if ($this->filter_month > 0 && !empty($targetStaffIds)) {
            foreach ($this->contractModelClasses as $modelClass) {
                $contracts = $modelClass::query()
                    ->with('customer')
                    ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                    ->whereMonth(DB::raw('COALESCE(submitted_at, signed_at)'), $this->filter_month)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->get();

                foreach ($contracts as $contract) {
                    $detail->push([
                        'customer'   => $contract->customer?->name ?? '—',
                        'type'       => $this->contractTypeLabels[$modelClass],
                        'value'      => (float) $contract->revenue,
                        'is_renewal' => (bool) $contract->is_renewal,
                        'date'       => $contract->submitted_at ?? $contract->signed_at,
                    ]);
                }
            }
            $detail = $detail->sortByDesc('date')->values();
        }

        $staffs = User::query()->whereIn('id', $salesStaffIds)->orderBy('name')->get();

        return view('livewire.admin.reports.sales.sales-summary-report', [
            'months'  => $months,
            'totals'  => $totals,
            'staffs'  => $staffs,
            'years'   => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'detail'  => $detail,
        ])->layout('admin.layouts.app', ['title' => 'Bảng tổng kết doanh số']);
    }
}
