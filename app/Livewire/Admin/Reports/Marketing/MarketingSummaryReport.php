<?php

namespace App\Livewire\Admin\Reports\Marketing;

use App\Models\Quotation;
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
        $monthRows = Quotation::whereYear('date', $this->year)
            ->when($this->filter_month, fn($q) => $q->whereMonth('date', $this->filter_month))
            ->selectRaw('MONTH(date) as m, COUNT(*) as count,
                SUM(original_value) as total_value, SUM(total_value) as total_sales')
            ->groupByRaw('MONTH(date)')
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

        $byService = Quotation::whereYear('date', $this->year)
            ->when($this->filter_month, fn($q) => $q->whereMonth('date', $this->filter_month))
            ->whereNotNull('work_description')
            ->where('work_description', '!=', '')
            ->selectRaw('work_description as service, COUNT(*) as count, SUM(total_value) as total_sales')
            ->groupBy('work_description')
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
