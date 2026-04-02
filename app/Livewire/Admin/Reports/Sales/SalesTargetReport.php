<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\RenewalSales;
use App\Models\ProgressiveSales;
use App\Models\SalesTarget;
use App\Models\User;
use Livewire\Component;

class SalesTargetReport extends Component
{
    public int $year;
    public string $filter_staff = '';
    public array $targets = [];

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
        $this->loadTargets();
    }

    public function updatedYear(): void       { $this->loadTargets(); }
    public function updatedFilterStaff(): void { $this->loadTargets(); }

    private function loadTargets(): void
    {
        $this->targets = array_fill(1, 12, 0);
        SalesTarget::where('year', $this->year)
            ->when(
                $this->filter_staff,
                fn($q) => $q->where('staff_id', $this->filter_staff),
                fn($q) => $q->whereNull('staff_id')
            )
            ->get()
            ->each(fn($t) => $this->targets[$t->month] = (int) $t->target_amount);
    }

    public function saveTargets(): void
    {
        if (! auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly'])) {
            abort(403);
        }
        for ($m = 1; $m <= 12; $m++) {
            SalesTarget::updateOrCreate(
                [
                    'year'     => $this->year,
                    'month'    => $m,
                    'staff_id' => $this->filter_staff ?: null,
                ],
                ['target_amount' => (int) ($this->targets[$m] ?? 0)]
            );
        }
        $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Đã lưu mục tiêu!']]);
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ['target' => $this->targets[$m] ?? 0, 'actual' => 0];
        }

        $staffFilter = $this->filter_staff;

        RenewalSales::whereYear('sales_month', $this->year)
            ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as total')
            ->groupBy('m')->get()
            ->each(fn($r) => $months[$r->m]['actual'] += (float) $r->total);

        ProgressiveSales::whereYear('sales_month', $this->year)
            ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
            ->selectRaw('MONTH(sales_month) as m, SUM(amount) as total')
            ->groupBy('m')->get()
            ->each(fn($r) => $months[$r->m]['actual'] += (float) $r->total);

        $totals = ['target' => 0, 'actual' => 0];
        foreach ($months as $m) {
            $totals['target'] += $m['target'];
            $totals['actual'] += $m['actual'];
        }

        return view('livewire.admin.reports.sales.sales-target-report', [
            'months'    => $months,
            'totals'    => $totals,
            'staffs'    => User::role('kinh-doanh')->orderBy('name')->get(),
            'years'     => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'canEdit'   => auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly']),
        ])->layout('admin.layouts.app', ['title' => 'Bảng doanh số cam kết']);
    }
}
