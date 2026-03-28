<?php

namespace App\Livewire\Admin\Reports\Marketing;

use App\Models\QuotationSales;
use Livewire\Component;

class MarketingSummaryReport extends Component
{
    public int $year;
    public string $filter_month = '';
    public array $years = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function render()
    {
        $monthRows = QuotationSales::whereYear('sales_month', $this->year)
            ->when($this->filter_month, fn($q) => $q->whereMonth('sales_month', $this->filter_month))
            ->selectRaw('MONTH(sales_month) as m, COUNT(*) as count,
                SUM(value_ext_vat) as total_value, SUM(sales_amount) as total_sales')
            ->groupByRaw('MONTH(sales_month)')
            ->get()->keyBy('m');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $row = $monthRows->get($m);
            $monthly[$m] = [
                'count' => $row ? (int) $row->count : 0,
                'value' => $row ? (float) $row->total_value : 0,
                'sales' => $row ? (float) $row->total_sales : 0,
            ];
        }

        $byService = QuotationSales::whereYear('sales_month', $this->year)
            ->when($this->filter_month, fn($q) => $q->whereMonth('sales_month', $this->filter_month))
            ->whereNotNull('service')
            ->where('service', '!=', '')
            ->selectRaw('service, COUNT(*) as count, SUM(sales_amount) as total_sales')
            ->groupBy('service')
            ->orderByDesc('total_sales')
            ->get();

        $totals = [
            'count' => array_sum(array_column($monthly, 'count')),
            'value' => array_sum(array_column($monthly, 'value')),
            'sales' => array_sum(array_column($monthly, 'sales')),
        ];

        return view('livewire.admin.reports.marketing.marketing-summary-report',
            compact('monthly', 'byService', 'totals'))
            ->layout('admin.layouts.app');
    }
}
