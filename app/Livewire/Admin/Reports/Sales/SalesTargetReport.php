<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Enums\QuotationStatus;
use App\Enums\Role;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\Quotation;
use App\Models\SalesTarget;
use App\Models\User;
use Livewire\Component;

class SalesTargetReport extends Component
{
    public int $year;

    public int $viewMonth;

    public string $viewMode = 'month';

    public string $filter_staff = '';

    public int $filter_month = 0;

    public array $detail = [];

    public array $potentialDetail = [];

    protected array $contractModels = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    protected array $contractTypeLabels = [
        ContractWaste::class => 'Chất thải',
        ContractLegal::class => 'Pháp lý & Hồ sơ MT',
        ContractTechnical::class => 'Ứng phó sự cố',
        ContractResearch::class => 'Nghiên cứu và chuyển đổi công nghệ',
        ContractSustainability::class => 'Phát triển bền vững',
        ContractEmission::class => 'Giảm phát thải, tiết kiệm năng lượng',
    ];

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
        $this->viewMonth = (int) now()->format('n');
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
        } else {
            $this->filter_staff = '';
        }

        $this->loadMonthDetail();
    }

    public function switchMode(string $mode): void
    {
        if (in_array($mode, ['year', 'month'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function updatedYear(): void
    {
        $this->loadMonthDetail();
    }

    public function updatedFilterStaff(): void
    {
        $this->loadMonthDetail();
    }

    public function updatedViewMonth(): void
    {
        $this->loadMonthDetail();
    }

    public function openDetail(int $month): void
    {
        $this->viewMonth = $month;
        $this->filter_month = $month;
        $this->loadMonthDetail();
        $this->dispatch('openDetailModal');
    }

    private function loadMonthDetail(): void
    {
        $month = $this->viewMonth;
        $this->filter_month = $month;
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
            $staffIds = [$user->id];
        } else {
            $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
            $staffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;
        }

        $detail = collect();
        if (! empty($staffIds)) {
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
                        'customer' => $contract->customer?->name ?? '—',
                        'staff' => $contract->staff?->name ?? '—',
                        'type' => $this->contractTypeLabels[$modelClass],
                        'value' => (float) $contract->revenue,
                        'contract_value' => (float) $contract->value,
                        'service' => $contract->loai_dich_vu ?: $this->contractTypeLabels[$modelClass],
                        'payment_method' => $contract->payment_method,
                        'notes' => $contract->notes,
                        'is_renewal' => (bool) $contract->is_renewal,
                        'date' => $contract->submitted_at?->format('d/m/Y'),
                    ]);
                }
            }
        }

        $this->detail = $detail->sortByDesc('date')->values()->toArray();

        $this->potentialDetail = empty($staffIds)
            ? []
            : Quotation::query()
                ->with('staff')
                ->where('status', QuotationStatus::BAO_GIA_TIEM_NANG->value)
                ->whereIn('staff_id', $staffIds)
                ->whereYear(\DB::raw('COALESCE(expected_signing_date, date)'), $this->year)
                ->whereMonth(\DB::raw('COALESCE(expected_signing_date, date)'), $month)
                ->orderByDesc('date')
                ->get()
                ->map(fn (Quotation $quotation) => [
                    'company' => $quotation->company_name ?: '—',
                    'service' => $quotation->service ?: '—',
                    'staff' => $quotation->staff?->name ?? '—',
                    'source' => $quotation->source ?: '—',
                    'value' => (float) $quotation->value_inc_vat,
                    'date' => $quotation->expected_signing_date ? $quotation->expected_signing_date->format('d/m/Y') : ($quotation->date?->format('d/m/Y') ?? '—'),
                    'notes' => $quotation->notes ?: '—',
                ])
                ->all();
    }

    public function openPotentialDetail(int $month): void
    {
        $this->filter_month = $month;
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
            $staffIds = [$user->id];
        } else {
            $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
            $staffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;
        }

        $this->potentialDetail = empty($staffIds)
            ? []
            : Quotation::query()
                ->with('staff')
                ->where('status', QuotationStatus::BAO_GIA_TIEM_NANG->value)
                ->whereIn('staff_id', $staffIds)
                ->whereYear(\DB::raw('COALESCE(expected_signing_date, date)'), $this->year)
                ->whereMonth(\DB::raw('COALESCE(expected_signing_date, date)'), $month)
                ->orderByDesc('date')
                ->get()
                ->map(fn (Quotation $quotation) => [
                    'company' => $quotation->company_name ?: '—',
                    'service' => $quotation->service ?: '—',
                    'staff' => $quotation->staff?->name ?? '—',
                    'source' => $quotation->source ?: '—',
                    'value' => (float) $quotation->value_inc_vat,
                    'date' => $quotation->expected_signing_date ? $quotation->expected_signing_date->format('d/m/Y') : ($quotation->date?->format('d/m/Y') ?? '—'),
                ])
                ->all();

        $this->dispatch('openPotentialDetailModal');
    }

    public function totalPct(array $totals): ?float
    {
        $target = (float) ($totals['target'] ?? 0);
        $actual = (float) ($totals['actual'] ?? 0);

        return $target > 0 ? round(($actual / $target) * 100, 1) : null;
    }

    public function totalDelta(array $totals): float
    {
        return ((float) ($totals['actual'] ?? 0) + (float) ($totals['potential'] ?? 0)) - (float) ($totals['target'] ?? 0);
    }

    public function monthMetrics(array $data): array
    {
        $target = (float) ($data['target'] ?? 0);
        $actual = (float) ($data['actual'] ?? 0);
        $potential = (float) ($data['potential'] ?? 0);
        $pct = $target > 0 ? round($actual / $target * 100, 1) : null;

        return [
            'target' => $target,
            'actual' => $actual,
            'potential' => $potential,
            'pct' => $pct,
            'delta' => ($actual + $potential) - $target,
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
            $months[$m] = ['target' => 0, 'actual' => 0, 'potential' => 0];
        }

        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        if ($isRestrictedSales) {
            $this->filter_staff = (string) $user->id;
            $targetStaffIds = [$user->id];
            $staffs = User::where('id', $user->id)->get();
        } else {
            $salesStaffIds = User::role(['kinh-doanh', 'tp-kinh-doanh'])->pluck('id')->all();
            $targetStaffIds = $this->filter_staff !== '' ? [(int) $this->filter_staff] : $salesStaffIds;
            $staffs = User::where('is_active', true)->whereIn('id', $salesStaffIds)->orderBy('name')->get();
        }

        if (! empty($targetStaffIds)) {
            foreach (SalesTarget::query()
                ->where('year', $this->year)
                ->whereIn('staff_id', $targetStaffIds)
                ->selectRaw('month, SUM(target_amount) as total_target')
                ->groupBy('month')
                ->get() as $r) {
                $mIdx = (int) $r->month;
                if (isset($months[$mIdx])) {
                    $months[$mIdx]['target'] = (float) $r->total_target;
                }
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
                    $mIdx = (int) $r->m;
                    if (isset($months[$mIdx])) {
                        $months[$mIdx]['actual'] += (float) $r->total;
                    }
                }
            }

            $potentialRows = Quotation::query()
                ->where('status', QuotationStatus::BAO_GIA_TIEM_NANG->value)
                ->whereIn('staff_id', $targetStaffIds)
                ->whereYear(\DB::raw('COALESCE(expected_signing_date, date)'), $this->year)
                ->selectRaw('MONTH(COALESCE(expected_signing_date, date)) as m, SUM(value_inc_vat) as total')
                ->groupBy('m')
                ->get();

            foreach ($potentialRows as $row) {
                $mIdx = (int) $row->m;
                if (isset($months[$mIdx])) {
                    $months[$mIdx]['potential'] += (float) $row->total;
                }
            }
        }

        $totals = ['target' => 0, 'actual' => 0, 'potential' => 0];
        foreach ($months as $m) {
            $totals['target'] += $m['target'];
            $totals['actual'] += $m['actual'];
            $totals['potential'] += $m['potential'];
        }

        return view('livewire.admin.reports.sales.sales-target-report', [
            'months' => $months,
            'totals' => $totals,
            'monthTarget' => (float) ($months[$this->viewMonth]['target'] ?? 0),
            'monthActual' => (float) ($months[$this->viewMonth]['actual'] ?? 0),
            'monthPotential' => (float) ($months[$this->viewMonth]['potential'] ?? 0),
            'monthRemain' => max(0, (float) ($months[$this->viewMonth]['target'] ?? 0) - ((float) ($months[$this->viewMonth]['actual'] ?? 0) + (float) ($months[$this->viewMonth]['potential'] ?? 0))),
            'monthPct' => isset($months[$this->viewMonth]) ? $this->monthMetrics($months[$this->viewMonth])['pct'] : null,
            'selectedStaffName' => $this->filter_staff !== ''
                ? ($staffs->firstWhere('id', (int) $this->filter_staff)?->name ?? '—')
                : 'Tất cả nhân viên KD',
            'staffs' => $staffs,
            'years' => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'maxMonth' => $this->year === (int) now()->format('Y') ? (int) now()->format('n') : 12,
        ])->layout('admin.layouts.app', ['title' => 'Bảng doanh số cam kết']);
    }
}
