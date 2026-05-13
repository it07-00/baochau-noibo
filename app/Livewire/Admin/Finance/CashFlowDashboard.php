<?php

namespace App\Livewire\Admin\Finance;

use App\Enums\Permission;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use Livewire\Component;
use Livewire\WithPagination;

class CashFlowDashboard extends Component
{
    use WithPagination;

    public int    $filterYear;
    public string $filterPeriodType  = 'year';  // year | quarter | month
    public int    $filterMonth       = 0;
    public int    $filterQuarter     = 0;
    public string $filterContractType = 'all';

    protected $paginationTheme = 'bootstrap';

    private const CONTRACT_SOURCES = [
        'waste'          => [ContractWaste::class,         'Chất thải & Tiếng ồn'],
        'consulting'     => [ContractLegal::class,         'Pháp lý & Hồ sơ MT'],
        'project'        => [ContractTechnical::class,     'Kỹ thuật & Ứng phó SC'],
        'commercial'     => [ContractResearch::class,      'NC & CĐ Công nghệ'],
        'sustainability' => [ContractSustainability::class,'TV & BC PTBV'],
        'energy'         => [ContractEmission::class,      'Phát thải & Năng lượng'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->can(Permission::CASH_FLOW_VIEW->value), 403);
        $this->filterYear = (int) now()->format('Y');
    }

    public function updatedFilterPeriodType(): void
    {
        $this->filterMonth   = 0;
        $this->filterQuarter = 0;
        $this->resetPage();
    }

    public function updatedFilterYear(): void        { $this->resetPage(); }
    public function updatedFilterMonth(): void       { $this->resetPage(); }
    public function updatedFilterQuarter(): void     { $this->resetPage(); }
    public function updatedFilterContractType(): void { $this->resetPage(); }

    private function buildQuery(string $modelClass)
    {
        $query = $modelClass::query()->with(['customer:id,name', 'staff:id,name']);

        $query->whereNotNull('signed_at')->whereYear('signed_at', $this->filterYear);

        if ($this->filterPeriodType === 'month' && $this->filterMonth > 0) {
            $query->whereMonth('signed_at', $this->filterMonth);
        } elseif ($this->filterPeriodType === 'quarter' && $this->filterQuarter > 0) {
            $start = ($this->filterQuarter - 1) * 3 + 1;
            $query->whereMonth('signed_at', '>=', $start)
                  ->whereMonth('signed_at', '<=', $start + 2);
        }

        return $query;
    }

    private function collectRows(): array
    {
        $sources = $this->filterContractType === 'all'
            ? self::CONTRACT_SOURCES
            : [$this->filterContractType => self::CONTRACT_SOURCES[$this->filterContractType]];

        $rows = [];
        foreach ($sources as $key => [$modelClass, $label]) {
            foreach ($this->buildQuery($modelClass)->get() as $contract) {
                $rows[] = [
                    'type'         => $label,
                    'shd_bc'       => $contract->shd_bc,
                    'customer'     => $contract->customer?->name,
                    'staff'        => $contract->staff?->name,
                    'signed_at'    => $contract->signed_at?->format('d/m/Y'),
                    'revenue'      => (int) $contract->revenue,
                    'commission'   => (int) $contract->commission,
                    'ncc_payment'  => (int) $contract->ncc_payment,
                    'net_received' => (int) $contract->revenue - (int) $contract->commission - (int) $contract->ncc_payment,
                ];
            }
        }

        usort($rows, fn ($a, $b) => strcmp($b['signed_at'] ?? '', $a['signed_at'] ?? ''));

        return $rows;
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(auth()->user()->can(Permission::CASH_FLOW_EXPORT->value), 403);

        $rows        = $this->collectRows();
        $periodLabel = $this->buildPeriodLabel();
        $totals      = $this->buildTotals($rows);

        return response()->streamDownload(
            function () use ($rows, $periodLabel, $totals) {
                echo view('admin.finance.cash-flow-export', compact('rows', 'periodLabel', 'totals'));
            },
            'DongTien_' . now()->format('d_m_Y') . '.xls',
            ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']
        );
    }

    private function buildTotals(array $rows): array
    {
        return [
            'revenue'      => array_sum(array_column($rows, 'revenue')),
            'commission'   => array_sum(array_column($rows, 'commission')),
            'ncc_payment'  => array_sum(array_column($rows, 'ncc_payment')),
            'net_received' => array_sum(array_column($rows, 'net_received')),
            'count'        => count($rows),
        ];
    }

    private function buildPeriodLabel(): string
    {
        return match ($this->filterPeriodType) {
            'month'   => "Tháng {$this->filterMonth}/{$this->filterYear}",
            'quarter' => "Quý {$this->filterQuarter}/{$this->filterYear}",
            default   => "Năm {$this->filterYear}",
        };
    }

    public function render()
    {
        $rows   = $this->collectRows();
        $totals = $this->buildTotals($rows);

        return view('livewire.admin.finance.cash-flow-dashboard', [
            'rows'           => $rows,
            'totals'         => $totals,
            'periodLabel'    => $this->buildPeriodLabel(),
            'contractTypes'  => array_map(fn ($s) => $s[1], self::CONTRACT_SOURCES),
            'availableYears' => range((int) now()->format('Y'), 2024),
        ])->layout('admin.layouts.app', ['title' => 'Dòng tiền']);
    }
}
