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
        $canSeeFinance    = $currentUser->hasAnyRole(['admin', 'ke-toan', 'giam-doc', 'quan-ly']);
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
            // ── Thực thu theo nhân viên (tính trước để dùng trong salesRankings) ──
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

            // ── Xếp hạng nhân viên kinh doanh ──────────────
            $salesRankings = User::role(['kinh-doanh', 'tp-kinh-doanh'])->get()
                ->map(function ($user) use ($staffTotals) {
                    $r = (float) RenewalSales::whereYear('sales_month', $this->year)
                        ->where('user_id', $user->id)->sum('sales_amount');
                    $p = (float) ProgressiveSales::whereYear('sales_month', $this->year)
                        ->where('user_id', $user->id)->sum('amount');

                    return [
                        'name'        => $user->name,
                        'renewal'     => $r,
                        'progressive' => $p,
                        'total'       => $r + $p,
                        'revenue'     => $staffTotals[$user->name] ?? 0,
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

            // ── Top tỉnh/TP theo tiền thu từ các giai đoạn HĐ ───────────
            $contractTables = [
                \App\Models\ContractWaste::class          => 'contract_wastes',
                \App\Models\ContractConsulting::class     => 'contract_consultings',
                \App\Models\ContractProject::class        => 'contract_projects',
                \App\Models\ContractCommercial::class     => 'contract_commercials',
                \App\Models\ContractSustainability::class => 'contract_sustainabilities',
                \App\Models\ContractEnergy::class         => 'contract_energies',
            ];

            $parts    = [];
            $bindings = [];
            foreach ($contractTables as $type => $table) {
                // Use COALESCE(contract.province, customer.province) so contracts
                // without a province set fall back to the linked customer's province.
                // Filter by paid_date year + paid_amount > 0 = money actually collected.
                $parts[] = "SELECT COALESCE(NULLIF(c.province,''), cust.province) AS province,
                    COUNT(DISTINCT cps.contract_id) AS cnt,
                    COALESCE(SUM(cps.paid_amount), 0) AS total
                FROM contract_payment_schedules cps
                INNER JOIN `{$table}` c ON cps.contract_id = c.id AND cps.contract_type = ?
                LEFT JOIN customers cust ON cust.id = c.customer_id
                WHERE YEAR(cps.paid_date) = ?
                  AND cps.paid_amount > 0
                  AND c.deleted_at IS NULL
                GROUP BY COALESCE(NULLIF(c.province,''), cust.province)";
                $bindings[] = $type;
                $bindings[] = $this->year;
            }

            $sql = "SELECT province, SUM(cnt) AS cnt, SUM(total) AS total
                    FROM (" . implode(' UNION ALL ', $parts) . ") sub
                    WHERE province IS NOT NULL AND province != ''
                    GROUP BY province
                    ORDER BY total DESC
                    LIMIT 10";

            $topProvinces = collect(DB::select($sql, $bindings));

            // ── Top dịch vụ theo báo giá ───────────
            $topServices = \App\Models\Quotation::whereYear('date', $this->year)
                ->whereNotNull('service')
                ->where('service', '!=', '')
                ->selectRaw('service, COUNT(*) as cnt, SUM(total_value) as total')
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

            $revenueRankings = collect();
        }

        if ($canSeeConsulting) {
            // ── Xếp hạng nhân viên tư vấn (qua ContractAssignment, tất cả loại HĐ) ──
            $allContractTypes = [
                ContractWaste::class,
                ContractConsulting::class,
                ContractProject::class,
                ContractCommercial::class,
                ContractSustainability::class,
                ContractEnergy::class,
            ];

            $consultingRankings = User::role('tu-van')->get()
                ->map(function ($user) use ($allContractTypes) {
                    $assignments = ContractAssignment::where('user_id', $user->id)
                        ->whereIn('assignable_type', $allContractTypes)
                        ->get();

                    if ($assignments->isEmpty()) {
                        return [
                            'name'      => $user->name,
                            'count'     => 0,
                            'completed' => 0,
                        ];
                    }

                    $totalCount = 0;
                    $totalCompleted = 0;

                    foreach ($assignments->groupBy('assignable_type') as $type => $group) {
                        $contracts = $type::whereIn('id', $group->pluck('assignable_id'))
                            ->whereYear('signed_at', $this->year)
                            ->get();

                        $totalCount += $contracts->count();
                        $totalCompleted += $contracts->where('workflow_status', 'finished')->count();
                    }

                    return [
                        'name'      => $user->name,
                        'count'     => $totalCount,
                        'completed' => $totalCompleted,
                    ];
                })
                ->sortByDesc('count')
                ->values();
        }

        if ($canSeeTechnical) {
            // ── Xếp hạng nhân viên kỹ thuật (chỉ HĐ Pháp lý & Hồ sơ MT) ──
            $technicalRankings = User::role('ky-thuat')->get()
                ->map(function ($user) {
                    $assignments = ContractAssignment::where('user_id', $user->id)
                        ->where('assignable_type', ContractConsulting::class)
                        ->pluck('assignable_id');

                    if ($assignments->isEmpty()) {
                        return [
                            'name'      => $user->name,
                            'count'     => 0,
                            'value'     => 0,
                            'completed' => 0,
                        ];
                    }

                    $contracts = ContractConsulting::whereIn('id', $assignments)
                        ->whereYear('signed_at', $this->year)
                        ->get();

                    return [
                        'name'      => $user->name,
                        'count'     => $contracts->count(),
                        'value'     => (float) $contracts->sum('value'),
                        'completed' => $contracts->where('workflow_status', 'finished')->count(),
                    ];
                })
                ->sortByDesc('count')
                ->values();
        }

        return view('livewire.admin.rankings-board', compact(
            'canSeeSales', 'canSeeConsulting', 'canSeeTechnical', 'canSeeFinance',
            'salesRankings', 'consultingRankings', 'technicalRankings',
            'revenueRankings', 'topCustomers', 'topProvinces', 'topServices', 'paymentStats'
        ))->layout('admin.layouts.app');
    }
}
