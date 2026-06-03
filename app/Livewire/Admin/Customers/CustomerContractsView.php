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

    public function statusColor(?string $status): array
    {
        $statusText = trim((string) $status);
        $statusKey = mb_strtolower($statusText);

        return match (true) {
            in_array($statusText, ['HOÀN THÀNH', 'Đã hoàn thành', 'Đã hoàn thành KH ký trước'], true)
                || in_array($statusKey, ['hoàn thành', 'đã hoàn thành', 'đã hoàn thành kh ký trước'], true)
                => ['bg' => '#d1e7dd', 'text' => '#198754'],

            in_array($statusText, ['Hợp đồng hủy', 'ĐÃ HỦY', 'Đã hủy', 'Hủy bỏ'], true)
                || in_array($statusKey, ['hợp đồng hủy', 'đã hủy', 'hủy bỏ'], true)
                => ['bg' => '#f8d7da', 'text' => '#dc3545'],

            in_array($statusText, ['PTH đang kiểm tra', 'ĐANG THỰC HIỆN', 'ĐANG THỰC HIÊN'], true)
                || in_array($statusKey, ['đang thực hiện', 'pth đang kiểm tra', ''], true)
                => ['bg' => '#cfe2ff', 'text' => '#0d6efd'],

            in_array($statusText, ['Đang trình BGĐ ký'], true)
                || in_array($statusKey, ['đã trình ký nhà thầu phụ', 'đang trình bgđ ký'], true)
                => ['bg' => '#fff3cd', 'text' => '#b45309'],

            in_array($statusKey, ['nhà thầu phụ đã gửi về'], true)
                => ['bg' => '#d1ecf1', 'text' => '#0c5460'],

            in_array($statusText, ['Đã gửi khách hàng'], true)
                || in_array($statusKey, ['đã gửi khách hàng'], true)
                => ['bg' => '#e2d9f3', 'text' => '#6f42c1'],

            in_array($statusKey, ['tạm dừng'], true)
                => ['bg' => '#fff8e1', 'text' => '#e65100'],

            default => ['bg' => '#e9ecef', 'text' => '#495057'],
        };
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
            'waste'          => 'Chất thải',
            'consulting'     => 'Quan trắc và hồ sơ môi trường',
            'project'        => 'Ứng phó sự cố',
            'commercial'     => 'Nghiên cứu và chuyển đổi công nghệ',
            'sustainability' => 'Phát triển bền vững',
            'energy'         => 'Giảm phát thải, tiết kiệm năng lượng',
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
            ['model' => ContractWaste::class,          'label' => 'Chất thải',                             'route' => 'app.contracts.waste.index'],
            ['model' => ContractLegal::class,           'label' => 'Quan trắc và hồ sơ môi trường',         'route' => 'app.contracts.consulting.index'],
            ['model' => ContractTechnical::class,       'label' => 'Ứng phó sự cố',                         'route' => 'app.contracts.project.index'],
            ['model' => ContractResearch::class,        'label' => 'Nghiên cứu và chuyển đổi công nghệ',    'route' => 'app.contracts.commercial.index'],
            ['model' => ContractSustainability::class,  'label' => 'Phát triển bền vững',                   'route' => 'app.contracts.sustainability.index'],
            ['model' => ContractEmission::class,        'label' => 'Giảm phát thải, tiết kiệm năng lượng',  'route' => 'app.contracts.energy.index'],
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
