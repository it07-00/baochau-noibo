<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\RenewalSales;
use App\Models\User;
use Livewire\Component;

class PersonalRenewalReport extends Component
{
    public int $year;
    public string $filter_staff = '';

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');

        if (auth()->user()->hasRole('kinh-doanh')) {
            $this->filter_staff = (string) auth()->id();
        }
    }

    public function render()
    {
        $userId = $this->filter_staff;
        if (! $userId && ! auth()->user()->hasAnyRole(['it', 'giam-doc', 'quan-ly'])) {
            $userId = (string) auth()->id();
        }

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ['count' => 0, 'sales_value' => 0, 'sales_amount' => 0];
        }

        if ($userId) {
            RenewalSales::whereYear('sales_month', $this->year)
                ->where('user_id', $userId)
                ->selectRaw('MONTH(sales_month) as m, COUNT(*) as cnt, SUM(sales_value) as sv, SUM(sales_amount) as sa')
                ->groupBy('m')->get()
                ->each(function ($r) use (&$months) {
                    $months[$r->m] = ['count' => $r->cnt, 'sales_value' => (float) $r->sv, 'sales_amount' => (float) $r->sa];
                });

            $userDetail = User::find($userId);
            $allUsers   = collect();
        } else {
            $userDetail = null;
            $months     = [];
            $allUsers   = User::orderBy('name')->get()->map(function ($user) {
                $rows = RenewalSales::whereYear('sales_month', $this->year)
                    ->where('user_id', $user->id)
                    ->selectRaw('COUNT(*) as cnt, SUM(sales_value) as sv, SUM(sales_amount) as sa')
                    ->first();
                if (! $rows->cnt) return null;
                return [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'count'        => $rows->cnt,
                    'sales_value'  => (float) $rows->sv,
                    'sales_amount' => (float) $rows->sa,
                ];
            })->filter()->values();
        }

        $totals = $userId ? [
            'count'        => array_sum(array_column($months, 'count')),
            'sales_value'  => array_sum(array_column($months, 'sales_value')),
            'sales_amount' => array_sum(array_column($months, 'sales_amount')),
        ] : null;

        return view('livewire.admin.reports.sales.personal-renewal-report', [
            'months'     => $months,
            'totals'     => $totals,
            'allUsers'   => $allUsers,
            'userDetail' => $userDetail,
            'staffs'     => User::orderBy('name')->get(),
            'years'      => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'isSingle'   => (bool) $userId,
        ])->layout('admin.layouts.app', ['title' => 'Bảng theo dõi tái ký cá nhân']);
    }
}
