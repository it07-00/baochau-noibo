<?php

namespace App\Livewire\Admin\Sales;

use App\Models\ContractCommercial;
use App\Models\ContractConsulting;
use App\Models\ContractEnergy;
use App\Models\ContractProject;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\SalesTarget;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesTargetRegistration extends Component
{
    public int $year;
    public array $targets = [];

    protected array $contractModels = [
        ContractWaste::class,
        ContractConsulting::class,
        ContractProject::class,
        ContractCommercial::class,
        ContractSustainability::class,
        ContractEnergy::class,
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyRole(['kinh-doanh', 'tp-kinh-doanh']), 403);

        $this->year = (int) now()->format('Y');
        $this->loadTargets();
    }

    public function updatedYear(): void
    {
        $this->loadTargets();
    }

    public function updatedTargets(mixed $value, mixed $key): void
    {
        $month = (int) $key;
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
            ->where('staff_id', auth()->id())
            ->get()
            ->each(fn($t) => $this->targets[(int) $t->month] = number_format((int) $t->target_amount, 0, ',', '.'));
    }

    public function saveTargets(): void
    {
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
                ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
                ->where('staff_id', auth()->id())
                ->selectRaw('MONTH(COALESCE(submitted_at, signed_at)) as m, SUM(value) as total')
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

        return view('livewire.admin.sales.sales-target-registration', [
            'months' => $months,
            'totals' => $totals,
            'years'  => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
        ])->layout('admin.layouts.app', ['title' => 'Đăng ký mục tiêu doanh số']);
    }
}
