<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Enums\Role;
use App\Models\ContractWorkflowStep;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SalesProjectProgressReport extends Component
{
    use WithPagination;

    public int $year;
    public array $years = [];
    public string $filter_department = 'all'; // all | consulting | technical
    public string $filter_contract_type = 'all'; // all | waste | consulting | project | commercial | sustainability | energy
    public string $filter_status = 'all'; // all | not_started | in_progress | finished
    public string $search = '';

    // Selected contract properties for the detail modal
    public $selectedContract = null;
    public string $selectedContractSourceKey = '';
    public string $detailActiveTab = 'info';

    protected $queryString = [
        'year' => ['except' => ''],
        'filter_department' => ['except' => 'all'],
        'filter_contract_type' => ['except' => 'all'],
        'filter_status' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDepartment(): void
    {
        $this->resetPage();
    }

    public function updatedFilterContractType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function contractSources(): array
    {
        return [
            'waste' => [\App\Models\ContractWaste::class, 'BC Chất thải'],
            'consulting' => [\App\Models\ContractLegal::class, 'Hồ sơ môi trường'],
            'project' => [\App\Models\ContractTechnical::class, 'BC Ứng phó sự cố'],
            'commercial' => [\App\Models\ContractResearch::class, 'BC Nghiên cứu và chuyển đổi công nghệ'],
            'sustainability' => [\App\Models\ContractSustainability::class, 'BC Phát triển bền vững'],
            'energy' => [\App\Models\ContractEmission::class, 'BC Giảm phát thải, tiết kiệm năng lượng'],
        ];
    }

    private function getContractProgress($contract, array $completedSteps): array
    {
        $isTechnical = false;
        foreach ($contract->assignments as $assign) {
            if ($assign->user && $assign->user->hasRole(Role::KY_THUAT->value)) {
                $isTechnical = true;
                break;
            }
        }

        $stepKeys = $isTechnical ? ContractWorkflowStep::STEP_KEYS_TECHNICAL : ContractWorkflowStep::STEP_KEYS;
        $stepLabels = $isTechnical ? ContractWorkflowStep::STEPS_TECHNICAL : ContractWorkflowStep::STEPS;
        $totalSteps = count($stepKeys);

        $completedCount = 0;
        $currentStep = null;

        foreach ($stepKeys as $key) {
            if (in_array($key, $completedSteps)) {
                $completedCount++;
                $currentStep = $key;
            } else {
                break;
            }
        }

        return [
            'completed_count' => $completedCount,
            'total_steps' => $totalSteps,
            'percent' => $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0,
            'current_label' => $currentStep ? ($stepLabels[$currentStep] ?? $currentStep) : 'Chưa bắt đầu',
            'step_keys' => $stepKeys,
            'step_labels' => $stepLabels,
        ];
    }

    private function collectContracts(): array
    {
        $contractSources = $this->contractSources();
        $sources = $this->filter_contract_type === 'all'
            ? $contractSources
            : [$this->filter_contract_type => $contractSources[$this->filter_contract_type]];

        $rows = [];
        foreach ($sources as $key => $source) {
            [$modelClass, $label] = $source;

            $query = $modelClass::query()
                ->with(['customer', 'staff', 'assignments.user', 'workflowSteps']);

            // Year filter (signed_at or submitted_at)
            $query->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year);

            // Department filter (consultant or technical staff assigned)
            if ($this->filter_department === 'consulting') {
                $query->whereHas('assignments.user', fn($u) => $u->role(Role::TU_VAN->value));
            } elseif ($this->filter_department === 'technical') {
                $query->whereHas('assignments.user', fn($u) => $u->role(Role::KY_THUAT->value));
            }

            // Workflow status filter
            if ($this->filter_status === 'not_started') {
                $query->whereDoesntHave('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass));
            } elseif ($this->filter_status === 'in_progress') {
                $query->whereHas('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass))
                    ->whereDoesntHave('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass)->where('step_name', 'finished'));
            } elseif ($this->filter_status === 'finished') {
                $query->whereHas('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass)->where('step_name', 'finished'));
            }

            // Search filter
            if ($this->search !== '') {
                $search = '%' . $this->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('shd_bc', 'like', $search)
                        ->orWhere('shd_cxl', 'like', $search)
                        ->orWhereHas('customer', fn($cust) => $cust->where('name', 'like', $search))
                        ->orWhereHas('staff', fn($st) => $st->where('name', 'like', $search))
                        ->orWhereHas('assignments.user', fn($au) => $au->where('name', 'like', $search));
                });
            }

            foreach ($query->get() as $contract) {
                // Determine departments & staffs assigned
                $assignedDepts = [];
                $assignedStaff = [];
                foreach ($contract->assignments as $assign) {
                    if ($assign->user) {
                        $assignedStaff[] = $assign->user->name;
                        if ($assign->user->hasRole(Role::TU_VAN->value)) {
                            $assignedDepts[] = 'Tư vấn';
                        }
                        if ($assign->user->hasRole(Role::KY_THUAT->value)) {
                            $assignedDepts[] = 'Kỹ thuật';
                        }
                    }
                }
                $deptLabel = count($assignedDepts) > 0 ? implode(' + ', array_unique($assignedDepts)) : 'Chưa phân công';
                $staffLabel = count($assignedStaff) > 0 ? implode(', ', array_unique($assignedStaff)) : 'Chưa phân công';

                // Calculate progress
                $completedSteps = $contract->workflowSteps->pluck('step_name')->unique()->toArray();
                $progress = $this->getContractProgress($contract, $completedSteps);

                $rows[] = [
                    'id' => $contract->id,
                    'source_key' => $key,
                    'type' => $label,
                    'shd' => $contract->shd_bc ?: $contract->shd_cxl ?: '—',
                    'customer' => $contract->customer?->name ?? '—',
                    'customer_slug' => $contract->customer?->slug,
                    'staff' => $contract->staff?->name ?? '—',
                    'assigned_staff' => $staffLabel,
                    'department' => $deptLabel,
                    'province' => $contract->province ?? '—',
                    'signed_at' => $contract->signed_at,
                    'workflow_progress' => $progress,
                    'status_label' => $contract->status_label ?? $contract->status ?? '—',
                    'status_color' => $contract->status_color ?? 'secondary',
                ];
            }
        }

        // Sort signed_at DESC
        usort($rows, function ($a, $b) {
            $dateA = $a['signed_at'] ? $a['signed_at']->timestamp : 0;
            $dateB = $b['signed_at'] ? $b['signed_at']->timestamp : 0;
            return $dateB <=> $dateA;
        });

        return $rows;
    }

    public function showDetails(string $sourceKey, int $id): void
    {
        $this->selectedContractSourceKey = $sourceKey;
        $modelClass = $this->contractSources()[$sourceKey][0] ?? null;
        if (!$modelClass) return;

        $this->selectedContract = $modelClass::with([
            'customer',
            'staff',
            'assignments.user',
            'workflowSteps.user',
            'milestoneFiles.uploader'
        ])->findOrFail($id);

        $this->detailActiveTab = 'info';
        $this->dispatch('open-detail-modal');
    }

    public function render()
    {
        $allRows = $this->collectContracts();

        // Calculate summary counters
        $totalContracts = count($allRows);
        $inProgressContracts = 0;
        $completedContracts = 0;

        foreach ($allRows as $row) {
            if ($row['workflow_progress']['percent'] == 100) {
                $completedContracts++;
            } elseif ($row['workflow_progress']['completed_count'] > 0) {
                $inProgressContracts++;
            }
        }

        $summary = (object)[
            'total' => $totalContracts,
            'active' => $inProgressContracts,
            'completed' => $completedContracts
        ];

        // Manual Pagination
        $perPage = 20;
        $currentPage = $this->getPage();
        $currentItems = array_slice($allRows, ($currentPage - 1) * $perPage, $perPage);
        $paginatedRows = new LengthAwarePaginator(
            $currentItems,
            count($allRows),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $contractTypes = array_map(fn($s) => $s[1], $this->contractSources());

        return view('livewire.admin.reports.sales.sales-project-progress-report', [
            'items' => $paginatedRows,
            'summary' => $summary,
            'contractTypes' => $contractTypes
        ])->layout('admin.layouts.app');
    }
}
