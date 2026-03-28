<?php

namespace App\Livewire\Admin\Reports\Marketing;

use App\Models\QuotationSales;
use App\Models\SalesTarget;
use Livewire\Component;

class MarketingTargetReport extends Component
{
    public int $year;
    public array $years = [];
    public array $targets = [];
    public bool $canEdit = false;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
        $this->canEdit = auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly']);
        $this->loadTargets();
    }

    public function updatedYear(): void
    {
        $this->loadTargets();
    }

    private function loadTargets(): void
    {
        $records = SalesTarget::where('year', $this->year)
            ->whereNull('staff_id')
            ->get()->keyBy('month');

        $this->targets = [];
        for ($m = 1; $m <= 12; $m++) {
            $this->targets[$m] = $records->has($m)
                ? (string) ($records[$m]->target_count ?? 0)
                : '';
        }
    }

    public function saveTargets(): void
    {
        if (!$this->canEdit) {
            abort(403);
        }

        for ($m = 1; $m <= 12; $m++) {
            $count = max(0, (int) ($this->targets[$m] ?? 0));
            SalesTarget::updateOrCreate(
                ['year' => $this->year, 'month' => $m, 'staff_id' => null],
                ['target_count' => $count]
            );
        }

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Đã lưu mục tiêu báo giá!']);
    }

    public function render()
    {
        $actualRows = QuotationSales::whereYear('sales_month', $this->year)
            ->selectRaw('MONTH(sales_month) as m, COUNT(*) as count, SUM(sales_amount) as total_sales')
            ->groupByRaw('MONTH(sales_month)')
            ->get()->keyBy('m');

        $targetRecords = SalesTarget::where('year', $this->year)
            ->whereNull('staff_id')
            ->get()->keyBy('month');

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $target = $targetRecords->has($m) ? (int) ($targetRecords[$m]->target_count ?? 0) : 0;
            $actual = $actualRows->has($m) ? (int) $actualRows[$m]->count : 0;
            $actualSales = $actualRows->has($m) ? (float) $actualRows[$m]->total_sales : 0;
            $months[$m] = [
                'target'       => $target,
                'actual'       => $actual,
                'actual_sales' => $actualSales,
            ];
        }

        $totals = [
            'target'       => array_sum(array_column($months, 'target')),
            'actual'       => array_sum(array_column($months, 'actual')),
            'actual_sales' => array_sum(array_column($months, 'actual_sales')),
        ];

        return view('livewire.admin.reports.marketing.marketing-target-report',
            compact('months', 'totals'))
            ->layout('admin.layouts.app');
    }
}
