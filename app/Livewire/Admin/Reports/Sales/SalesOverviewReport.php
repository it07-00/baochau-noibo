<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\RenewalSales;
use App\Models\ProgressiveSales;
use Livewire\Component;

class SalesOverviewReport extends Component
{
    public int $year;

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
    }

    public function render()
    {
        $quarters = [];
        for ($q = 1; $q <= 4; $q++) {
            $quarters[$q] = ['renewal' => 0, 'progressive' => 0];
        }

        RenewalSales::whereYear('sales_month', $this->year)
            ->selectRaw('QUARTER(sales_month) as q, SUM(sales_amount) as total')
            ->groupBy('q')->get()
            ->each(fn($r) => $quarters[$r->q]['renewal'] = (float) $r->total);

        ProgressiveSales::whereYear('sales_month', $this->year)
            ->selectRaw('QUARTER(sales_month) as q, SUM(amount) as total')
            ->groupBy('q')->get()
            ->each(fn($r) => $quarters[$r->q]['progressive'] = (float) $r->total);

        $prevYear    = $this->year - 1;
        $prevTotals  = [
            'renewal'     => (float) RenewalSales::whereYear('sales_month', $prevYear)->sum('sales_amount'),
            'progressive' => (float) ProgressiveSales::whereYear('sales_month', $prevYear)->sum('amount'),
        ];
        $prevTotals['grand'] = array_sum($prevTotals);

        $currentTotals = [
            'renewal'     => array_sum(array_column($quarters, 'renewal')),
            'progressive' => array_sum(array_column($quarters, 'progressive')),
        ];
        $currentTotals['grand'] = array_sum($currentTotals);

        return view('livewire.admin.reports.sales.sales-overview-report', [
            'quarters'      => $quarters,
            'currentTotals' => $currentTotals,
            'prevTotals'    => $prevTotals,
            'prevYear'      => $prevYear,
            'years'         => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
        ])->layout('admin.layouts.app', ['title' => 'Bảng tổng kết']);
    }
}
