<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\QuotationSales;
use App\Models\RenewalSales;
use App\Models\ProgressiveSales;
use App\Models\User;
use Livewire\Component;

class SalesAchievementReport extends Component
{
    public int $year;
    public string $filter_month = '';

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
    }

    public function render()
    {
        $staffs = User::role('kinh-doanh')->orderBy('name')->get();

        $rankings = $staffs->map(function ($user) {
            $qBase = QuotationSales::where('staff_id', $user->id)
                ->whereYear('sales_month', $this->year)
                ->when($this->filter_month, fn($q) => $q->whereMonth('sales_month', $this->filter_month));

            $rBase = RenewalSales::where('user_id', $user->id)
                ->whereYear('sales_month', $this->year)
                ->when($this->filter_month, fn($q) => $q->whereMonth('sales_month', $this->filter_month));

            $pBase = ProgressiveSales::where('user_id', $user->id)
                ->whereYear('sales_month', $this->year)
                ->when($this->filter_month, fn($q) => $q->whereMonth('sales_month', $this->filter_month));

            $quotation   = (float) (clone $qBase)->sum('sales_amount');
            $renewal     = (float) (clone $rBase)->sum('sales_amount');
            $progressive = (float) (clone $pBase)->sum('amount');
            $total       = $quotation + $renewal + $progressive;

            return [
                'id'          => $user->id,
                'name'        => $user->name,
                'quotation'   => $quotation,
                'renewal'     => $renewal,
                'progressive' => $progressive,
                'total'       => $total,
            ];
        })
        ->sortByDesc('total')
        ->values();

        return view('livewire.admin.reports.sales.sales-achievement-report', [
            'rankings' => $rankings,
            'years'    => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'months'   => range(1, 12),
        ])->layout('admin.layouts.app', ['title' => 'Bảng thành tích']);
    }
}
