<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ContractLegal;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\SalesProgressive;
use App\Models\SalesRenewal;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $year  = now()->year;
        $month = now()->month;

        // ── KPI ────────────────────────────────────────
        $totalCustomers = Customer::count();

        $salesThisMonth = (float) SalesRenewal::whereYear('sales_month', $year)->whereMonth('sales_month', $month)->sum('sales_amount')
                        + (float) SalesProgressive::whereYear('sales_month', $year)->whereMonth('sales_month', $month)->sum('amount');

        $salesThisYear = (float) SalesRenewal::whereYear('sales_month', $year)->sum('sales_amount')
                       + (float) SalesProgressive::whereYear('sales_month', $year)->sum('amount');

        $contractsThisYear = ContractWaste::whereYear('signed_at', $year)->count()
                           + ContractLegal::whereYear('signed_at', $year)->count();

        // ── Doanh số theo tháng ─────────────────────────
        $rM = SalesRenewal::whereYear('sales_month', $year)
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');
        $pM = SalesProgressive::whereYear('sales_month', $year)
            ->selectRaw('MONTH(sales_month) as m, SUM(amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = (float) ($rM->get($m)?->val ?? 0)
                         + (float) ($pM->get($m)?->val ?? 0);
        }
        $filtered = array_filter($monthly);
        $maxMonthly = $filtered ? max($filtered) : 1;

        // ── Top 5 nhân viên kinh doanh ───────────────────
        $topStaff = User::role(Role::KINH_DOANH->value)
            ->withSum(
                ['salesRenewals as renewal_sum' => fn ($q) => $q->whereYear('sales_month', $year)],
                'sales_amount'
            )
            ->withSum(
                ['salesProgressives as progressive_sum' => fn ($q) => $q->whereYear('sales_month', $year)],
                'amount'
            )
            ->get()
            ->map(fn ($user) => [
                'name'  => $user->name,
                'total' => (float) ($user->renewal_sum ?? 0) + (float) ($user->progressive_sum ?? 0),
            ])
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
                'contract_no' => $c->shd_cxl ?: ($c->shd_bc ?: '—'),
                'customer'    => $c->customer?->name ?? '—',
                'staff'       => $c->staff?->name ?? '—',
                'value'       => (float) $c->value,
                'signed_at'   => $c->signed_at,
            ]);

        $recentConsulting = ContractLegal::with('customer', 'staff')
            ->whereNotNull('signed_at')
            ->latest('signed_at')
            ->limit(6)
            ->get()
            ->map(fn($c) => [
                'type'        => 'Tư vấn',
                'badge'       => 'bg-label-success',
                'contract_no' => $c->shd_bc ?: '—',
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
