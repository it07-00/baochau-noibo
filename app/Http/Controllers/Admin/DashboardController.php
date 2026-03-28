<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractConsulting;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\ProgressiveSales;
use App\Models\QuotationSales;
use App\Models\RenewalSales;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $year  = now()->year;
        $month = now()->month;

        // ── KPI ────────────────────────────────────────
        $totalCustomers = Customer::count();

        $salesThisMonth = (float) QuotationSales::whereYear('sales_month', $year)->whereMonth('sales_month', $month)->sum('sales_amount')
                        + (float) RenewalSales::whereYear('sales_month', $year)->whereMonth('sales_month', $month)->sum('sales_amount')
                        + (float) ProgressiveSales::whereYear('sales_month', $year)->whereMonth('sales_month', $month)->sum('amount');

        $salesThisYear = (float) QuotationSales::whereYear('sales_month', $year)->sum('sales_amount')
                       + (float) RenewalSales::whereYear('sales_month', $year)->sum('sales_amount')
                       + (float) ProgressiveSales::whereYear('sales_month', $year)->sum('amount');

        $contractsThisYear = ContractWaste::whereYear('signed_at', $year)->count()
                           + ContractConsulting::whereYear('signed_at', $year)->count();

        // ── Doanh số theo tháng ─────────────────────────
        $qM = QuotationSales::whereYear('sales_month', $year)
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');
        $rM = RenewalSales::whereYear('sales_month', $year)
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');
        $pM = ProgressiveSales::whereYear('sales_month', $year)
            ->selectRaw('MONTH(sales_month) as m, SUM(amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = (float) ($qM->get($m)?->val ?? 0)
                         + (float) ($rM->get($m)?->val ?? 0)
                         + (float) ($pM->get($m)?->val ?? 0);
        }
        $filtered = array_filter($monthly);
        $maxMonthly = $filtered ? max($filtered) : 1;

        // ── Top 5 nhân viên kinh doanh ───────────────────
        $topStaff = User::role('kinh-doanh')->get()
            ->map(function ($user) use ($year) {
                $total = (float) QuotationSales::whereYear('sales_month', $year)->where('staff_id', $user->id)->sum('sales_amount')
                       + (float) RenewalSales::whereYear('sales_month', $year)->where('user_id', $user->id)->sum('sales_amount')
                       + (float) ProgressiveSales::whereYear('sales_month', $year)->where('user_id', $user->id)->sum('amount');
                return ['name' => $user->name, 'total' => $total];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // ── Hợp đồng ký gần đây ─────────────────────────
        $recentWaste = ContractWaste::with('customer', 'staff')
            ->whereNotNull('signed_at')
            ->latest('signed_at')
            ->limit(6)
            ->get()
            ->map(fn($c) => [
                'type'        => 'Chất thải',
                'badge'       => 'bg-label-primary',
                'contract_no' => $c->shd_cxl ?: ($c->shd_ad ?: '—'),
                'customer'    => $c->customer?->name ?? '—',
                'staff'       => $c->staff?->name ?? '—',
                'value'       => (float) $c->value,
                'signed_at'   => $c->signed_at,
            ]);

        $recentConsulting = ContractConsulting::with('customer', 'staff')
            ->whereNotNull('signed_at')
            ->latest('signed_at')
            ->limit(6)
            ->get()
            ->map(fn($c) => [
                'type'        => 'Tư vấn',
                'badge'       => 'bg-label-success',
                'contract_no' => $c->shd_ad ?: '—',
                'customer'    => $c->customer?->name ?? '—',
                'staff'       => $c->staff?->name ?? '—',
                'value'       => (float) $c->value,
                'signed_at'   => $c->signed_at,
            ]);

        $recentContracts = $recentWaste->concat($recentConsulting)
            ->sortByDesc('signed_at')
            ->take(10)
            ->values();

        return view('admin.pages.dashboard', compact(
            'year', 'month',
            'totalCustomers', 'salesThisMonth', 'salesThisYear', 'contractsThisYear',
            'monthly', 'maxMonthly', 'topStaff', 'recentContracts'
        ));
    }
}
