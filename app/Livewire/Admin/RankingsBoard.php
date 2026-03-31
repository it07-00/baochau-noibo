<?php

namespace App\Livewire\Admin;

use App\Models\ContractAssignment;
use App\Models\ContractConsulting;
use App\Models\ContractPaymentSchedule;
use App\Models\ContractWaste;
use App\Models\ContractProject;
use App\Models\ContractCommercial;
use App\Models\ContractSustainability;
use App\Models\ContractEnergy;
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
        $canSeeSales      = $currentUser->hasAnyRole(['giam-doc', 'tp-kinh-doanh', 'kinh-doanh', 'ke-toan']);
        $canSeeConsulting = $currentUser->hasAnyRole(['giam-doc', 'tu-van']);
        $canSeeTechnical  = $currentUser->hasAnyRole(['giam-doc', 'ky-thuat']);

        $salesRankings      = collect();
        $consultingRankings = collect();
        $technicalRankings  = collect();
        $topCustomers       = collect();
        $topProvinces       = collect();
        $topServices        = collect();
        $revenueRankings    = collect();
        $paymentStats       = ['due' => 0, 'paid' => 0, 'pending' => 0, 'partial' => 0, 'overdue' => 0];

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

            // ── Tiến độ thu tiền ─────────────────────────────
            $paymentStats['due']  = (float) ContractPaymentSchedule::whereYear('due_date', $this->year)->sum('amount');
            $paymentStats['paid'] = (float) ContractPaymentSchedule::whereYear('due_date', $this->year)->sum('paid_amount');

            $statusCounts = ContractPaymentSchedule::whereYear('due_date', $this->year)
                ->selectRaw("status, COUNT(*) as cnt, SUM(amount) as total")
                ->groupBy('status')->get()->keyBy('status');

            $paymentStats['pending_amount'] = (float) ($statusCounts->get('pending')?->total ?? 0);
            $paymentStats['partial_amount'] = (float) ($statusCounts->get('partial')?->total ?? 0);
            $paymentStats['paid_amount']    = (float) ($statusCounts->get('paid')?->total ?? 0);
            $paymentStats['overdue_amount'] = (float) ($statusCounts->get('overdue')?->total ?? 0);

            $paymentStats['pending_count'] = (int) ($statusCounts->get('pending')?->cnt ?? 0);
            $paymentStats['partial_count'] = (int) ($statusCounts->get('partial')?->cnt ?? 0);
            $paymentStats['paid_count']    = (int) ($statusCounts->get('paid')?->cnt ?? 0);
            $paymentStats['overdue_count'] = (int) ($statusCounts->get('overdue')?->cnt ?? 0);

            // ── Xếp hạng nhân viên theo doanh số thực thu ──
            $contractModelsMap = [
                ContractWaste::class, ContractConsulting::class, ContractProject::class,
                ContractCommercial::class, ContractSustainability::class, ContractEnergy::class,
            ];
            $revenuePayments = ContractPaymentSchedule::whereYear('paid_date', $this->year)
                ->whereIn('status', ['paid', 'partial'])->get();

            $staffTotals = [];
            foreach ($revenuePayments->groupBy('contract_type') as $type => $items) {
                if (!class_exists($type)) continue;
                $ids = $items->pluck('contract_id')->unique();
                $contracts = $type::whereIn('id', $ids)->with('staff')->get()->keyBy('id');
                foreach ($items as $item) {
                    $contract = $contracts->get($item->contract_id);
                    if (!$contract || !$contract->staff) continue;
                    $staffTotals[$contract->staff->name] = ($staffTotals[$contract->staff->name] ?? 0) + (float) $item->paid_amount;
                }
            }
            $revenueRankings = collect($staffTotals)->sortDesc()->take(15)
                ->map(fn($total, $name) => ['name' => $name, 'total' => $total])->values();
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

        if ($canSeeTechnical) {
            // ── Xếp hạng nhân viên kỹ thuật ────────────────
            $contractModels = [
                'waste'          => ContractWaste::class,
                'consulting'     => ContractConsulting::class,
                'project'        => ContractProject::class,
                'commercial'     => ContractCommercial::class,
                'sustainability' => ContractSustainability::class,
                'energy'         => ContractEnergy::class,
            ];

            $technicalRankings = User::role('ky-thuat')->get()
                ->map(function ($user) use ($contractModels) {
                    $totalCount     = 0;
                    $totalValue     = 0;
                    $totalCompleted = 0;

                    foreach ($contractModels as $modelClass) {
                        $assignments = ContractAssignment::where('user_id', $user->id)
                            ->where('assignable_type', $modelClass)
                            ->pluck('assignable_id');

                        if ($assignments->isEmpty()) continue;

                        $contracts = $modelClass::whereIn('id', $assignments)
                            ->whereYear('signed_at', $this->year)
                            ->get();

                        $totalCount     += $contracts->count();
                        $totalValue     += (float) $contracts->sum('value');
                        $totalCompleted += $contracts->where('status', 'HOÀN THÀNH')->count();
                    }

                    return [
                        'name'      => $user->name,
                        'count'     => $totalCount,
                        'value'     => $totalValue,
                        'completed' => $totalCompleted,
                    ];
                })
                ->sortByDesc('value')
                ->values();
        }

        return view('livewire.admin.rankings-board', compact(
            'canSeeSales', 'canSeeConsulting', 'canSeeTechnical',
            'salesRankings', 'consultingRankings', 'technicalRankings',
            'revenueRankings', 'topCustomers', 'topProvinces', 'topServices', 'paymentStats'
        ))->layout('admin.layouts.app');
    }
}
