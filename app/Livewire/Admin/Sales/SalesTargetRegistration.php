<?php

namespace App\Livewire\Admin\Sales;

use App\Enums\Role;
use App\Models\User;
use App\Models\ContractResearch;
use App\Models\ContractLegal;
use App\Models\ContractEmission;
use App\Models\ContractTechnical;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\SalesTarget;
use Livewire\Component;

class SalesTargetRegistration extends Component
{
    public int $year;
    public int $viewMonth;
    public string $viewMode = 'month';
    public array $targets = [];
    public ?int $selectedStaffId = null;

    protected array $contractModels = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    protected array $contractTypeNames = [
        ContractWaste::class          => 'Chất thải',
        ContractLegal::class          => 'Pháp lý & Hồ sơ MT',
        ContractTechnical::class      => 'Ứng phó sự cố',
        ContractResearch::class       => 'Nghiên cứu và chuyển đổi công nghệ',
        ContractSustainability::class => 'Phát triển bền vững',
        ContractEmission::class       => 'Giảm phát thải, tiết kiệm năng lượng',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyRole([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]), 403);

        $this->year            = (int) now()->format('Y');
        $this->viewMonth       = (int) now()->format('n');
        $this->selectedStaffId = auth()->id();
        $this->loadTargets();
    }

    public function switchMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function viewMonthDetail(int $month): void
    {
        $this->viewMonth = $month;
        $this->viewMode  = 'month';
    }

    public function prevMonth(): void
    {
        if ($this->viewMonth > 1) {
            $this->viewMonth--;
        }
    }

    public function nextMonth(): void
    {
        if ($this->viewMonth < 12) {
            $this->viewMonth++;
        }
    }

    public function updatedYear(): void
    {
        $this->loadTargets();
    }

    public function updatedSelectedStaffId(): void
    {
        $this->loadTargets();
    }

    public function updatedTargets(mixed $value, mixed $key): void
    {
        $month = (int) $key;
        // Chỉ nhân viên xem dữ liệu của chính mình mới được cập nhật
        if ($this->selectedStaffId !== auth()->id()) {
            return;
        }

        if ($month < 1 || $month > 12) {
            return;
        }

        $normalized = $this->normalizeTargetValue($value);
        $this->targets[$month] = number_format($normalized, 0, ',', '.');
    }

    private function normalizeTargetValue(mixed $value): int
    {
        if (is_numeric($value)) {
            return max(0, (int) $value);
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? 0 : (int) $digits;
    }

    private function loadTargets(): void
    {
        $this->targets = array_fill(1, 12, '0');

        SalesTarget::where('year', $this->year)
            ->where('staff_id', $this->selectedStaffId)
            ->get()
            ->each(fn($t) => $this->targets[(int) $t->month] = number_format((int) $t->target_amount, 0, ',', '.'));
    }

    private function getMonthContracts(): \Illuminate\Support\Collection
    {
        $contracts = collect();

        foreach ($this->contractModels as $modelClass) {
            $rows = $modelClass::with('customer')
                ->where('staff_id', $this->selectedStaffId)
                ->whereYear('signed_at', $this->year)
                ->whereMonth('signed_at', $this->viewMonth)
                ->get()
                ->map(fn($c) => [
                    'id'              => $c->id,
                    'model_idx'       => array_search($modelClass, $this->contractModels),
                    'type'            => $this->contractTypeNames[$modelClass] ?? class_basename($modelClass),
                    'customer_name'   => $c->customer?->name ?? '—',
                    'service'         => $c->loai_dich_vu ?: ($this->contractTypeNames[$modelClass] ?? '—'),
                    'value'           => (int) $c->value,
                    'payment_method'  => $c->payment_method,
                    'revenue'         => (int) $c->revenue,
                    'expected'        => max(0, (int) $c->value - (int) $c->revenue),
                    'notes'           => $c->notes,
                ]);

            $contracts = $contracts->merge($rows);
        }

        return $contracts;
    }

    public function saveNote(int $modelIdx, int $id, string $notes): void
    {
        $modelClass = $this->contractModels[$modelIdx] ?? null;
        if (!$modelClass) {
            return;
        }

        $modelClass::where('id', $id)
            ->where('staff_id', $this->selectedStaffId)
            ->update(['notes' => trim($notes)]);
    }

    public function saveTargets(): void
    {
        // TPKD chỉ được lưu cam kết của chính mình
        abort_unless($this->selectedStaffId === auth()->id(), 403);

        for ($m = 1; $m <= 12; $m++) {
            $normalizedTarget = $this->normalizeTargetValue($this->targets[$m] ?? 0);

            SalesTarget::updateOrCreate(
                [
                    'year'     => $this->year,
                    'month'    => $m,
                    'staff_id' => auth()->id(),
                ],
                ['target_amount' => $normalizedTarget]
            );

            $this->targets[$m] = number_format($normalizedTarget, 0, ',', '.');
        }

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã lưu cam kết doanh số theo tháng!']);
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'target' => $this->normalizeTargetValue($this->targets[$m] ?? 0),
                'actual' => 0,
            ];
        }

        foreach ($this->contractModels as $modelClass) {
            $rows = $modelClass::query()
                ->whereYear('signed_at', $this->year)
                ->where('staff_id', $this->selectedStaffId)
                ->selectRaw('MONTH(signed_at) as m, SUM(revenue) as total')
                ->groupBy('m')
                ->get();

            foreach ($rows as $r) {
                $months[(int) $r->m]['actual'] += (float) $r->total;
            }
        }

        $totals = ['target' => 0, 'actual' => 0];
        foreach ($months as $m) {
            $totals['target'] += $m['target'];
            $totals['actual'] += $m['actual'];
        }

        // Enrich mỗi tháng với các giá trị tính sẵn để blade không cần @php
        $currentMonth = (int) now()->format('n');
        $currentYear  = (int) now()->format('Y');
        foreach ($months as $m => &$data) {
            $t = (float) $data['target'];
            $a = (float) $data['actual'];
            $data['t']         = $t;
            $data['a']         = $a;
            $data['v']         = $a - $t;
            $data['p']         = $t > 0 ? round($a / $t * 100, 1) : null;
            $data['q']         = (int) ceil($m / 3);
            $data['isCurrent'] = ($m === $currentMonth && $this->year === $currentYear);
        }
        unset($data);

        $isTpkd    = auth()->user()->hasRole(Role::TP_KINH_DOANH->value);
        $staffList = $isTpkd
            ? User::role(Role::KINH_DOANH->value)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        if ($this->selectedStaffId === auth()->id()) {
            $selectedStaffName = auth()->user()->name;
        } else {
            $selectedStaffName = $staffList->firstWhere('id', $this->selectedStaffId)?->name
                ?? User::find($this->selectedStaffId)?->name
                ?? '—';
        }

        $monthTarget  = (float) ($months[$this->viewMonth]['target'] ?? 0);
        $monthActual  = (float) ($months[$this->viewMonth]['actual'] ?? 0);
        $monthPct     = $monthTarget > 0 ? round($monthActual / $monthTarget * 100, 1) : null;
        $totalPct     = $totals['target'] > 0 ? round($totals['actual'] / $totals['target'] * 100, 1) : null;

        return view('livewire.admin.sales.sales-target-registration', [
            'months'            => $months,
            'totals'            => $totals,
            'totalPct'          => $totalPct,
            'remaining'         => max(0, $totals['target'] - $totals['actual']),
            'monthTarget'       => $monthTarget,
            'monthActual'       => $monthActual,
            'monthPct'          => $monthPct,
            'monthRemain'       => max(0, $monthTarget - $monthActual),
            'years'             => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'monthContracts'    => $this->viewMode === 'month' ? $this->getMonthContracts() : collect(),
            'isTpkd'            => $isTpkd,
            'staffList'         => $staffList,
            'selectedStaffName' => $selectedStaffName,
            'canEditNote'       => $this->selectedStaffId === auth()->id() || $isTpkd,
        ])->layout('admin.layouts.app', ['title' => 'Đăng ký mục tiêu doanh số']);
    }
}
