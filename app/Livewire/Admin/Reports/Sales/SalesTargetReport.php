<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractResearch;
use App\Models\ContractLegal;
use App\Models\ContractEmission;
use App\Models\ContractTechnical;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\SalesTarget;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesTargetReport extends Component
{
    public int $year;
    public string $filter_staff = '';
    public int $filter_month = 0;
    public array $detail = [];

    protected array $contractModels = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    protected array $contractTypeLabels = [
        ContractWaste::class          => 'Chất thải',
        ContractLegal::class          => 'Pháp lý & Hồ sơ MT',
        ContractTechnical::class      => 'Ứng phó sự cố',
        ContractResearch::class       => 'Nghiên cứu và chuyển đổi công nghệ',
        ContractSustainability::class => 'Phát triển bền vững',
        ContractEmission::class       => 'Giảm phát thải, tiết kiệm năng lượng',
    ];

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
        } else {
            $this->filter_staff = '';
        }
    }

    public function openDetail(int $month): void
    {
        $this->filter_month = $month;
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
            $staffIds = [$user->id];
        } else {
            $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
            $staffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;
        }

        $detail = collect();
        if (!empty($staffIds)) {
            foreach ($this->contractModels as $modelClass) {
                $contracts = $modelClass::query()
                    ->with('customer', 'staff')
                    ->whereNotNull('submitted_at')
                    ->whereYear('submitted_at', $this->year)
                    ->whereMonth('submitted_at', $month)
                    ->whereIn('staff_id', $staffIds)
                    ->get();

                foreach ($contracts as $contract) {
                    $detail->push([
                        'customer'   => $contract->customer?->name ?? '—',
                        'staff'      => $contract->staff?->name ?? '—',
                        'type'       => $this->contractTypeLabels[$modelClass],
                        'value'      => (float) $contract->revenue,
                        'is_renewal' => (bool) $contract->is_renewal,
                        'date'       => $contract->submitted_at?->format('d/m/Y'),
                    ]);
                }
            }
        }

        $this->detail = $detail->sortByDesc('date')->values()->toArray();
        $this->dispatch('openDetailModal');
    }

    public function totalPct(array $totals): ?float
    {
        $target = (float) ($totals['target'] ?? 0);
        $actual = (float) ($totals['actual'] ?? 0);

        return $target > 0 ? round(($actual / $target) * 100, 1) : null;
    }

    public function totalDelta(array $totals): float
    {
        return (float) ($totals['actual'] ?? 0) - (float) ($totals['target'] ?? 0);
    }

    public function monthMetrics(array $data): array
    {
        $target = (float) ($data['target'] ?? 0);
        $actual = (float) ($data['actual'] ?? 0);
        $pct = $target > 0 ? round($actual / $target * 100, 1) : null;

        return [
            'target' => $target,
            'actual' => $actual,
            'pct' => $pct,
            'delta' => $actual - $target,
            'progressWidth' => $pct !== null ? max(0, min(100, $pct)) : 0,
            'progressClass' => $pct === null
                ? 'bg-secondary'
                : ($pct >= 100 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger')),
        ];
    }

    public function pctTextClass(?float $pct): string
    {
        if ($pct === null) {
            return 'text-danger';
        }

        if ($pct >= 100) {
            return 'text-success';
        }

        if ($pct >= 70) {
            return 'text-warning';
        }

        return 'text-danger';
    }

    public function pctBadgeClass(?float $pct): string
    {
        if ($pct === null) {
            return 'bg-soft-secondary text-secondary';
        }

        if ($pct >= 100) {
            return 'bg-soft-success text-success';
        }

        if ($pct >= 70) {
            return 'bg-soft-warning text-warning';
        }

        return 'bg-soft-danger text-danger';
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = ['target' => 0, 'actual' => 0];
        }

        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
            $targetStaffIds = [$user->id];
            $staffs = User::where('id', $user->id)->get();
        } else {
            $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
            $targetStaffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;
            $staffs = User::where('is_active', true)->whereIn('id', $salesStaffIds)->orderBy('name')->get();
        }

        if (!empty($targetStaffIds)) {
            foreach (SalesTarget::query()
                ->where('year', $this->year)
                ->whereIn('staff_id', $targetStaffIds)
                ->selectRaw('month, SUM(target_amount) as total_target')
                ->groupBy('month')
                ->get() as $r) {
                $months[(int) $r->month]['target'] = (float) $r->total_target;
            }

            foreach ($this->contractModels as $modelClass) {
                $rows = $modelClass::query()
                    ->whereNotNull('submitted_at')
                    ->whereYear('submitted_at', $this->year)
                    ->whereIn('staff_id', $targetStaffIds)
                    ->selectRaw('MONTH(submitted_at) as m, SUM(revenue) as total')
                    ->groupBy('m')
                    ->get();

                foreach ($rows as $r) {
                    $months[(int) $r->m]['actual'] += (float) $r->total;
                }
            }
        }

        $totals = ['target' => 0, 'actual' => 0];
        foreach ($months as $m) {
            $totals['target'] += $m['target'];
            $totals['actual'] += $m['actual'];
        }

        return view('livewire.admin.reports.sales.sales-target-report', [
            'months'    => $months,
            'totals'    => $totals,
            'staffs'    => $staffs,
            'years'     => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
        ])->layout('admin.layouts.app', ['title' => 'Bảng doanh số cam kết']);
    }
}
