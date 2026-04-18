<?php

namespace App\Livewire\Admin\Handlers;

use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\Handler;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class HandlerContractsView extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Handler $handler;

    public string $sortField = 'signed_at';
    public string $sortDir = 'desc';

    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }
    public function updatingSortField(): void { $this->resetPage(); }

    public function toggleDir(): void
    {
        $this->sortDir = $this->sortDir === 'desc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->dateFrom = '';
        $this->dateTo   = '';
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire.admin.users.pagination';
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

    private function fetchAll(): Collection
    {
        $types = [
            ['model' => ContractWaste::class,          'label' => 'Chất thải & Tiếng ồn'],
            ['model' => ContractLegal::class,           'label' => 'Hồ sơ môi trường'],
            ['model' => ContractTechnical::class,       'label' => 'Kỹ thuật & Ứng phó SC'],
            ['model' => ContractResearch::class,        'label' => 'NC & CĐ Công nghệ'],
            ['model' => ContractSustainability::class,  'label' => 'TV & BC PTBV'],
            ['model' => ContractEmission::class,        'label' => 'Phát thải & Năng lượng'],
        ];

        $all = collect();

        foreach ($types as $type) {
            $rows = $type['model']::query()
                ->where('handler_id', $this->handler->id)
                ->when($this->dateFrom, fn ($q) => $q->whereDate('signed_at', '>=', $this->dateFrom))
                ->when($this->dateTo,   fn ($q) => $q->whereDate('signed_at', '<=', $this->dateTo))
                ->with('customer')
                ->get()
                ->map(fn ($contract) => (object) [
                    'type_label' => $type['label'],
                    'shd_cxl'   => $contract->shd_cxl,
                    'shd_bc'    => $contract->shd_bc,
                    'customer'  => $contract->customer?->name ?? '—',
                    'signed_at' => $contract->signed_at,
                    'value'     => $contract->value,
                    'status'    => $contract->status,
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

        return view('livewire.admin.handlers.handler-contracts-view', [
            'contracts'      => $contracts,
            'totalValue'     => $all->sum(fn ($r) => (float) $r->value),
            'totalContracts' => $all->count(),
        ])->layout('admin.layouts.app');
    }
}

