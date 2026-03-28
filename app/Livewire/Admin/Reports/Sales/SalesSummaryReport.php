<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\QuotationSales;
use App\Models\RenewalSales;
use App\Models\ProgressiveSales;
use App\Models\User;
use Livewire\Component;

class SalesSummaryReport extends Component
{
    public int $year;
    public string $filter_staff = '';

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ['quotation' => 0, 'renewal' => 0, 'progressive' => 0];
        }

        QuotationSales::whereYear('sales_month', $this->year)
            ->when($this->filter_staff, fn($q) => $q->where('staff_id', $this->filter_staff))
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as total')
            ->groupBy('m')->get()
            ->each(fn($r) => $months[$r->m]['quotation'] = (float) $r->total);

        RenewalSales::whereYear('sales_month', $this->year)
            ->when($this->filter_staff, fn($q) => $q->where('user_id', $this->filter_staff))
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as total')
            ->groupBy('m')->get()
            ->each(fn($r) => $months[$r->m]['renewal'] = (float) $r->total);

        ProgressiveSales::whereYear('sales_month', $this->year)
            ->when($this->filter_staff, fn($q) => $q->where('user_id', $this->filter_staff))
            ->selectRaw('MONTH(sales_month) as m, SUM(amount) as total')
            ->groupBy('m')->get()
            ->each(fn($r) => $months[$r->m]['progressive'] = (float) $r->total);

        $totals = [
            'quotation'   => array_sum(array_column($months, 'quotation')),
            'renewal'     => array_sum(array_column($months, 'renewal')),
            'progressive' => array_sum(array_column($months, 'progressive')),
        ];
        $totals['grand'] = $totals['quotation'] + $totals['renewal'] + $totals['progressive'];

        $staffs = User::role('kinh-doanh')->orderBy('name')->get();

        return view('livewire.admin.reports.sales.sales-summary-report', [
            'months'  => $months,
            'totals'  => $totals,
            'staffs'  => $staffs,
            'years'   => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
        ])->layout('admin.layouts.app', ['title' => 'Bảng tổng kết doanh số']);
    }
}
