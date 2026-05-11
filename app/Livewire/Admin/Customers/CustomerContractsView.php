<?php

namespace App\Livewire\Admin\Customers;

use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractProgressNote;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerContractsView extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Customer $customer;

    public string $sortField = 'signed_at';
    public string $sortDir = 'desc';
    public string $dateFrom = '';
    public string $dateTo = '';

    public ?object $selectedContract = null;
    public string $selectedContractLabel = '';
    public string $selectedContractType = '';
    public $selectedProgressNotes = [];

    public function paginationView(): string
    {
        return 'livewire.admin.users.pagination';
    }

    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }
    public function updatingSortField(): void { $this->resetPage(); }

    public function toggleDir(): void
    {
        $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'desc';
        }
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->resetPage();
    }

    public function viewDetail(int $id, string $modelClass): void
    {
        $allowed = [
            ContractWaste::class         => 'waste',
            ContractLegal::class          => 'consulting',
            ContractTechnical::class      => 'project',
            ContractResearch::class       => 'commercial',
            ContractSustainability::class => 'sustainability',
            ContractEmission::class       => 'energy',
        ];
        if (!array_key_exists($modelClass, $allowed)) return;

        $contract = $modelClass::with(['customer', 'handler', 'staff', 'department', 'assignments.user', 'assignments.assigner'])->find($id);
        if (!$contract || (int)$contract->customer_id !== $this->customer->id) return;

        $typeLabels = [
            'waste'          => 'Chất thải & Tiếng ồn',
            'consulting'     => 'Hồ sơ môi trường',
            'project'        => 'Kỹ thuật & Ứng phó SC',
            'commercial'     => 'NC & CĐ Công nghệ',
            'sustainability' => 'TV & BC PTBV',
            'energy'         => 'Phát thải & Năng lượng',
        ];

        $contractType = $allowed[$modelClass];

        $this->selectedContract = $contract;
        $this->selectedContractType = $contractType;
        $this->selectedContractLabel = $typeLabels[$contractType] ?? '';
        $this->selectedProgressNotes = ContractProgressNote::where('contract_type', $contractType)
            ->where('contract_id', $id)
            ->with('user')
            ->latest()
            ->get();

        $this->dispatch('open-contract-detail-modal');
    }

    public string $progressNote = '';

    public function addProgressNote(int $contractId): void
    {
        if (!$this->selectedContractType) return;

        $this->validate(
            ['progressNote' => 'required|min:1|max:2000'],
            ['progressNote.required' => 'Vui lòng nhập nội dung ghi chú.', 'progressNote.max' => 'Ghi chú không được vượt quá 2000 ký tự.'],
            ['progressNote' => 'ghi chú']
        );

        ContractProgressNote::create([
            'contract_type' => $this->selectedContractType,
            'contract_id'   => $contractId,
            'user_id'       => auth()->id(),
            'note'          => $this->progressNote,
        ]);

        $this->progressNote = '';
        $this->selectedProgressNotes = ContractProgressNote::where('contract_type', $this->selectedContractType)
            ->where('contract_id', $contractId)
            ->with('user')
            ->latest()
            ->get();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã thêm ghi chú tiến độ!']);
    }

    private function fetchAll(): Collection
    {
        $types = [
            ['model' => ContractWaste::class,          'label' => 'Chất thải & Tiếng ồn', 'route' => 'app.contracts.waste.index'],
            ['model' => ContractLegal::class,           'label' => 'Hồ sơ môi trường',      'route' => 'app.contracts.consulting.index'],
            ['model' => ContractTechnical::class,       'label' => 'Kỹ thuật & Ứng phó SC','route' => 'app.contracts.project.index'],
            ['model' => ContractResearch::class,        'label' => 'NC & CĐ Công nghệ',    'route' => 'app.contracts.commercial.index'],
            ['model' => ContractSustainability::class,  'label' => 'TV & BC PTBV',          'route' => 'app.contracts.sustainability.index'],
            ['model' => ContractEmission::class,        'label' => 'Phát thải & Năng lượng','route' => 'app.contracts.energy.index'],
        ];

        $all = collect();

        foreach ($types as $type) {
            $rows = $type['model']::query()
                ->where('customer_id', $this->customer->id)
                ->when($this->dateFrom, fn ($q) => $q->whereDate('signed_at', '>=', $this->dateFrom))
                ->when($this->dateTo,   fn ($q) => $q->whereDate('signed_at', '<=', $this->dateTo))
                ->with('handler')
                ->get()
                ->map(fn ($contract) => (object) [
                    'type_label'     => $type['label'],
                    'contract_route' => $type['route'],
                    'contract_id'    => $contract->id,
                    'model_class'    => $type['model'],
                    'shd_cxl'        => $contract->shd_cxl,
                    'shd_bc'         => $contract->shd_bc,
                    'handler'        => $contract->handler?->name ?? '—',
                    'signed_at'      => $contract->signed_at,
                    'value'          => $contract->value,
                    'status'         => $contract->status,
                ]);

            $all = $all->concat($rows);
        }

        return $all->sortBy(
            fn ($r) => match ($this->sortField) {
                'signed_at' => optional($r->signed_at)->timestamp ?? 0,
                'value'     => (float) $r->value,
                default     => $r->{$this->sortField} ?? '',
            },
            SORT_REGULAR,
            $this->sortDir === 'desc'
        );
    }

    private function paginate(Collection $items, int $perPage = 15): LengthAwarePaginator
    {
        $page  = $this->getPage();
        $total = $items->count();

        return new LengthAwarePaginator(
            $items->slice(($page - 1) * $perPage, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function render()
    {
        $all       = $this->fetchAll();
        $contracts = $this->paginate($all, 15);

        return view('livewire.admin.customers.customer-contracts-view', [
            'contracts'      => $contracts,
            'totalValue'     => $all->sum(fn ($r) => (float) $r->value),
            'totalContracts' => $all->count(),
        ])->layout('admin.layouts.app');
    }
}
