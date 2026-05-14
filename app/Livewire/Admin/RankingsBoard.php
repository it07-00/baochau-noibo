<?php

namespace App\Livewire\Admin;

use App\Enums\Role;
use App\Models\ContractAssignment;
use App\Models\ContractLegal;
use App\Models\ContractWaste;
use App\Models\ContractTechnical;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractEmission;
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
        $canSeeFinance    = $currentUser->hasAnyRole([Role::KE_TOAN->value, Role::GIAM_DOC->value, Role::QUAN_LY->value]);
        $canSeeSales      = $currentUser->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::KINH_DOANH->value, Role::KE_TOAN->value]);
        $canSeeConsulting = $currentUser->hasAnyRole([Role::GIAM_DOC->value, Role::TU_VAN->value]);
        $canSeeTechnical  = $currentUser->hasAnyRole([Role::GIAM_DOC->value, Role::KY_THUAT->value]);

        $salesRankings      = collect();
        $consultingRankings = collect();
        $technicalRankings  = collect();
        $topCustomers       = collect();
        $topServices        = collect();

        if ($canSeeSales) {
            // ── Xếp hạng nhân viên kinh doanh theo tổng tiền của 6 loại HĐ ──
            $contractModels = [
                ContractWaste::class,
                ContractLegal::class,
                ContractTechnical::class,
                ContractResearch::class,
                ContractSustainability::class,
                ContractEmission::class,
            ];

            $totalsByStaff = [];
            foreach ($contractModels as $modelClass) {
                $rows = $modelClass::whereNotNull('submitted_at')
                    ->whereYear('submitted_at', $this->year)
                    ->whereNotNull('staff_id')
                    ->selectRaw('staff_id, COALESCE(SUM(revenue), 0) as total')
                    ->groupBy('staff_id')
                    ->pluck('total', 'staff_id');

                foreach ($rows as $staffId => $total) {
                    $staffId = (int) $staffId;
                    $totalsByStaff[$staffId] = ($totalsByStaff[$staffId] ?? 0) + (float) $total;
                }
            }

            $salesRankings = User::role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value])->get()
                ->map(function ($user) use ($totalsByStaff) {
                    return [
                        'name'  => $user->name,
                        'total' => (float) ($totalsByStaff[$user->id] ?? 0),
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

            // ── Top dịch vụ theo báo giá ───────────
            $topServices = \App\Models\Quotation::whereYear('date', $this->year)
                ->whereNotNull('service')
                ->where('service', '!=', '')
                ->selectRaw('service, COUNT(*) as cnt, SUM(total_value) as total')
                ->groupBy('service')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

        }

        if ($canSeeConsulting) {
            // ── Xếp hạng nhân viên tư vấn (qua ContractAssignment, tất cả loại HĐ) ──
            $allContractTypes = [
                ContractWaste::class,
                ContractLegal::class,
                ContractTechnical::class,
                ContractResearch::class,
                ContractSustainability::class,
                ContractEmission::class,
            ];

            $consultingRankings = User::role(Role::TU_VAN->value)->get()
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
                            ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                            ->get();

                        $totalCount += $contracts->count();
                        $totalCompleted += $contracts->whereIn('workflow_status', ['finished'])->count();
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
            $technicalRankings = User::role(Role::KY_THUAT->value)->get()
                ->map(function ($user) {
                    $assignments = ContractAssignment::where('user_id', $user->id)
                        ->where('assignable_type', ContractLegal::class)
                        ->pluck('assignable_id');

                    if ($assignments->isEmpty()) {
                        return [
                            'name'      => $user->name,
                            'count'     => 0,
                            'value'     => 0,
                            'completed' => 0,
                        ];
                    }

                    $contracts = ContractLegal::whereIn('id', $assignments)
                        ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                        ->get();

                    $contractIds = $contracts->pluck('id');
                    $finishedIds = \App\Models\ContractWorkflowStep::where('contract_type', ContractLegal::class)
                        ->whereIn('contract_id', $contractIds)
                        ->where('step_name', 'finished')
                        ->pluck('contract_id')
                        ->unique();

                    return [
                        'name'      => $user->name,
                        'count'     => $contracts->count(),
                        'value'     => (float) $contracts->sum('value'),
                        'completed' => $finishedIds->count(),
                    ];
                })
                ->sortByDesc('count')
                ->values();
        }

        return view('livewire.admin.rankings-board', compact(
            'canSeeSales', 'canSeeConsulting', 'canSeeTechnical', 'canSeeFinance',
            'salesRankings', 'consultingRankings', 'technicalRankings',
            'topCustomers', 'topServices'
        ))->layout('admin.layouts.app');
    }
}
