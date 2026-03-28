<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\QuotationSales;
use App\Models\User;
use Livewire\Component;

class PersonalSalesReport extends Component
{
    public int $year;
    public string $filter_staff = '';

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');

        // kinh-doanh tự động lọc theo mình
        if (auth()->user()->hasRole('kinh-doanh')) {
            $this->filter_staff = (string) auth()->id();
        }
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ['count' => 0, 'value' => 0, 'sales_amount' => 0];
        }

        $staffId = $this->filter_staff;

        // Nếu it/quan-ly không lọc → hiển thị theo từng nhân viên
        if (! $staffId && ! auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly'])) {
            $staffId = (string) auth()->id();
        }

        if ($staffId) {
            // Chế độ 1 nhân viên: breakdown theo tháng
            QuotationSales::whereYear('sales_month', $this->year)
                ->where('staff_id', $staffId)
                ->selectRaw('MONTH(sales_month) as m, COUNT(*) as cnt, SUM(value_ext_vat) as val, SUM(sales_amount) as sa')
                ->groupBy('m')->get()
                ->each(function ($r) use (&$months) {
                    $months[$r->m] = ['count' => $r->cnt, 'value' => (float) $r->val, 'sales_amount' => (float) $r->sa];
                });

            $staffDetail = User::find($staffId);
            $allStaff    = collect();
        } else {
            // Chế độ tất cả: breakdown theo nhân viên
            $staffDetail = null;
            $allStaff    = User::role('kinh-doanh')->orderBy('name')->get()->map(function ($user) {
                $rows = QuotationSales::whereYear('sales_month', $this->year)
                    ->where('staff_id', $user->id)
                    ->selectRaw('COUNT(*) as cnt, SUM(value_ext_vat) as val, SUM(sales_amount) as sa')
                    ->first();
                return [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'count'        => $rows->cnt ?? 0,
                    'value'        => (float) ($rows->val ?? 0),
                    'sales_amount' => (float) ($rows->sa ?? 0),
                ];
            });
            $months = [];
        }

        $totals = $staffId ? [
            'count'        => array_sum(array_column($months, 'count')),
            'value'        => array_sum(array_column($months, 'value')),
            'sales_amount' => array_sum(array_column($months, 'sales_amount')),
        ] : null;

        return view('livewire.admin.reports.sales.personal-sales-report', [
            'months'      => $months,
            'totals'      => $totals,
            'allStaff'    => $allStaff,
            'staffDetail' => $staffDetail,
            'staffs'      => User::role('kinh-doanh')->orderBy('name')->get(),
            'years'       => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'isSingle'    => (bool) $staffId,
        ])->layout('admin.layouts.app', ['title' => 'Bảng doanh số cá nhân']);
    }
}
