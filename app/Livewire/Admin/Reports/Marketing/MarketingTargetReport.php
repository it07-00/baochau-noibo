<?php

namespace App\Livewire\Admin\Reports\Marketing;

use App\Enums\Role;
use App\Models\Quotation;
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
        $this->canEdit = auth()->user()->hasAnyRole([Role::IT->value, Role::GIAM_DOC->value]);
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

    public function percentValue(int $actual, int $target): ?float
    {
        if ($target <= 0) {
            return null;
        }

        return round(($actual / $target) * 100, 1);
    }

    public function percentClass(?float $percent): string
    {
        if ($percent === null) {
            return 'text-danger';
        }

        if ($percent >= 100) {
            return 'text-success fw-bold';
        }

        return $percent >= 70 ? 'text-warning' : 'text-danger';
    }

    public function render()
    {
        $actualRows = Quotation::whereYear('date', $this->year)
            ->selectRaw('MONTH(date) as m, COUNT(*) as count, SUM(total_value) as total_sales')
            ->groupByRaw('MONTH(date)')
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
