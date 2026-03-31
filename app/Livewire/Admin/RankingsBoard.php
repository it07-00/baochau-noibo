<?php

namespace App\Livewire\Admin;

use App\Models\ContractConsulting;
use App\Models\ProgressiveSales;
use App\Models\QuotationSales;
use App\Models\RenewalSales;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RankingsBoard extends Component
{
    public int $year;
    public array $years = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function render()
    {
        $currentUser = auth()->user();
        $canSeeSales      = $currentUser->hasAnyRole(['it', 'giam-doc', 'tp-kinh-doanh', 'kinh-doanh', 'ke-toan']);
        $canSeeConsulting = $currentUser->hasAnyRole(['it', 'giam-doc', 'tu-van']);

        $salesRankings      = collect();
        $consultingRankings = collect();
        $topCustomers       = collect();
        $topProvinces       = collect();
        $topServices        = collect();

        if ($canSeeSales) {
            // ── Xếp hạng nhân viên kinh doanh ──────────────
            $salesRankings = User::role('kinh-doanh')->get()
                ->map(function ($user) {
                    $q = (float) QuotationSales::whereYear('sales_month', $this->year)
                        ->where('staff_id', $user->id)->sum('sales_amount');
                    $r = (float) RenewalSales::whereYear('sales_month', $this->year)
                        ->where('user_id', $user->id)->sum('sales_amount');
                    $p = (float) ProgressiveSales::whereYear('sales_month', $this->year)
                        ->where('user_id', $user->id)->sum('amount');

                    return [
                        'name'        => $user->name,
                        'quotation'   => $q,
                        'renewal'     => $r,
                        'progressive' => $p,
                        'total'       => $q + $r + $p,
                    ];
                })
                ->sortByDesc('total')
                ->values();

            // ── Top khách hàng theo giá trị HĐ ──────────────
            $topCustomers = DB::table('customers')
                ->leftJoin('contract_wastes as cw', function ($j) {
                    $j->on('customers.id', '=', 'cw.customer_id')
                      ->whereYear('cw.signed_at', $this->year);
                })
                ->leftJoin('contract_consultings as cc', function ($j) {
                    $j->on('customers.id', '=', 'cc.customer_id')
                      ->whereYear('cc.signed_at', $this->year);
                })
                ->selectRaw('customers.id, customers.name,
                    COUNT(DISTINCT cw.id) as waste_count,
                    COALESCE(SUM(DISTINCT cw.value), 0) as waste_value,
                    COUNT(DISTINCT cc.id) as consult_count,
                    COALESCE(SUM(DISTINCT cc.value), 0) as consult_value')
                ->groupBy('customers.id', 'customers.name')
                ->havingRaw('waste_count + consult_count > 0')
                ->orderByRaw('waste_value + consult_value DESC')
                ->limit(15)
                ->get();

            // ── Top tỉnh/TP theo doanh số báo giá ───────────
            $topProvinces = QuotationSales::whereYear('sales_month', $this->year)
                ->whereNotNull('province')
                ->where('province', '!=', '')
                ->selectRaw('province, COUNT(*) as cnt, SUM(sales_amount) as total')
                ->groupBy('province')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            // ── Top dịch vụ theo doanh số báo giá ───────────
            $topServices = QuotationSales::whereYear('sales_month', $this->year)
                ->whereNotNull('service')
                ->where('service', '!=', '')
                ->selectRaw('service, COUNT(*) as cnt, SUM(sales_amount) as total')
                ->groupBy('service')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        }

        if ($canSeeConsulting) {
            // ── Xếp hạng nhân viên tư vấn ──────────────────
            $consultingRankings = User::role('tu-van')->get()
                ->map(function ($user) {
                    $contracts = ContractConsulting::where('consultant_id', $user->id)
                        ->whereYear('signed_at', $this->year)
                        ->get();

                    return [
                        'name'      => $user->name,
                        'count'     => $contracts->count(),
                        'value'     => (float) $contracts->sum('value'),
                        'completed' => $contracts->where('workflow_status', 'finished')->count(),
                        'revenue'   => (float) $contracts->sum('revenue'),
                    ];
                })
                ->sortByDesc('value')
                ->values();
        }

        return view('livewire.admin.rankings-board', compact(
            'canSeeSales', 'canSeeConsulting',
            'salesRankings', 'consultingRankings', 'topCustomers', 'topProvinces', 'topServices'
        ))->layout('admin.layouts.app');
    }
}
