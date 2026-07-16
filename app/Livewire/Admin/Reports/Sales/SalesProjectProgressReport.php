<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Enums\Role;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\User;
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

    public string $filter_contract_type = 'waste'; // waste | consulting | project | commercial | sustainability | energy

    public string $filter_staff_id = 'all'; // all | user_id

    public string $filter_service = 'all';

    // Selected contract properties for the detail modal
    public $selectedContract = null;

    public string $selectedContractSourceKey = '';

    public string $detailActiveTab = 'info';

    protected $queryString = [
        'year' => ['except' => ''],
        'filter_contract_type' => ['except' => 'waste'],
        'filter_staff_id' => ['except' => 'all'],
        'filter_service' => ['except' => 'all'],
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

    public function updatedFilterContractType(): void
    {
        $this->resetPage();
    }

    public function selectContractType(string $contractType): void
    {
        abort_unless(array_key_exists($contractType, $this->contractSources()), 404);

        $this->filter_contract_type = $contractType;
        $this->filter_service = 'all';
        $this->resetPage();
    }

    public function updatedFilterStaffId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterService(): void
    {
        $this->resetPage();
    }

    public function contractSources(): array
    {
        return [
            'waste' => [ContractWaste::class, 'BC Chất thải'],
            'consulting' => [ContractLegal::class, 'Hồ sơ môi trường'],
            'project' => [ContractTechnical::class, 'BC Ứng phó sự cố'],
            'commercial' => [ContractResearch::class, 'BC Nghiên cứu và chuyển đổi công nghệ'],
            'sustainability' => [ContractSustainability::class, 'BC Phát triển bền vững'],
            'energy' => [ContractEmission::class, 'BC Giảm phát thải, tiết kiệm năng lượng'],
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

        if ($completedCount === 0 && $completedSteps === []) {
            $completedCount = $this->completedCountFromWorkflowStatus($contract->workflow_status);
            $currentStep = $completedCount > 0 ? ($stepKeys[$completedCount - 1] ?? null) : null;
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

    private function getWorkflowMatrix($contract): array
    {
        $isTechnical = $contract->assignments->contains(
            fn ($assignment): bool => $assignment->user?->hasRole(Role::KY_THUAT->value) === true,
        );
        $labels = $isTechnical ? ContractWorkflowStep::STEPS_TECHNICAL : ContractWorkflowStep::STEPS;
        $recordsByStep = $contract->workflowSteps->groupBy('step_name');
        $fallbackCompletedCount = $recordsByStep->isEmpty()
            ? $this->completedCountFromWorkflowStatus($contract->workflow_status)
            : 0;
        $firstPendingFound = false;

        return collect(ContractWorkflowStep::STEP_KEYS)
            ->map(function (string $key, int $index) use ($labels, $recordsByStep, $fallbackCompletedCount, &$firstPendingFound): array {
                $record = $recordsByStep->get($key)?->sortByDesc('created_at')->first();
                $isCompleted = $record !== null || $index < $fallbackCompletedCount;
                $isCurrent = ! $isCompleted && ! $firstPendingFound;

                if ($isCurrent) {
                    $firstPendingFound = true;
                }

                return [
                    'key' => $key,
                    'label' => $labels[$key] ?? $key,
                    'state' => $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending'),
                    'completed_at' => $record?->created_at,
                    'completed_by' => $record?->user?->name,
                ];
            })
            ->all();
    }

    private function completedCountFromWorkflowStatus(?string $status): int
    {
        return match ($status) {
            'receiving' => 1,
            'survey', 'consulting_survey' => 2,
            'processing', 'consulting_processing' => 3,
            'waiting_client' => 4,
            'client_confirmed' => 5,
            'finished', 'pending_accounting' => 6,
            default => 0,
        };
    }

    private function collectContracts(): array
    {
        $contractSources = $this->contractSources();
        $sources = [$this->filter_contract_type => $contractSources[$this->filter_contract_type]];

        $rows = [];
        foreach ($sources as $key => $source) {
            [$modelClass, $label] = $source;

            $query = $modelClass::query()
                ->with(['customer', 'staff', 'assignments.user', 'workflowSteps.user']);

            // Role-based salesperson visibility limit
            $user = auth()->user();
            $isKinhDoanhOnly = $user && $user->hasRole(Role::KINH_DOANH->value) &&
                               ! $user->hasAnyRole([Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value, Role::IT->value]);
            if ($isKinhDoanhOnly) {
                $query->where('staff_id', $user->id);
            }

            // Assigned project staff filter
            if ($this->filter_staff_id !== 'all') {
                $query->whereHas('assignments', fn ($q) => $q->where('user_id', $this->filter_staff_id));
            }

            // Year filter (signed_at or submitted_at)
            $query->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year);

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
                $workflowMatrix = $this->getWorkflowMatrix($contract);

                $rows[] = [
                    'id' => $contract->id,
                    'source_key' => $key,
                    'type' => filled($contract->loai_dich_vu) ? trim((string) $contract->loai_dich_vu) : $label,
                    'contract_type' => $label,
                    'shd' => $contract->shd_bc ?: $contract->shd_cxl ?: '—',
                    'customer' => $contract->customer?->name ?? '—',
                    'customer_slug' => $contract->customer?->slug,
                    'staff' => $contract->staff?->name ?? '—',
                    'assigned_staff' => $staffLabel,
                    'department' => $deptLabel,
                    'province' => $contract->province ?? '—',
                    'signed_at' => $contract->signed_at,
                    'workflow_progress' => $progress,
                    'workflow_steps' => $workflowMatrix,
                    'current_step_key' => collect($workflowMatrix)
                        ->firstWhere('state', 'current')['key'] ?? 'finished',
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
        if (! $modelClass) {
            return;
        }

        $this->selectedContract = $modelClass::with([
            'customer',
            'staff',
            'assignments.user',
            'workflowSteps.user',
            'milestoneFiles.uploader',
        ])->findOrFail($id);

        $this->detailActiveTab = 'info';
        $this->dispatch('open-detail-modal');
    }

    public function render()
    {
        $unfilteredRows = $this->collectContracts();
        $serviceOptions = collect($unfilteredRows)
            ->pluck('type')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
        $allRows = $this->filter_service === 'all'
            ? $unfilteredRows
            : collect($unfilteredRows)->where('type', $this->filter_service)->values()->all();
        $totalContracts = count($allRows);
        $completedContracts = collect($allRows)
            ->where('workflow_progress.completed_count', count(ContractWorkflowStep::STEP_KEYS))
            ->count();
        $notStartedContracts = collect($allRows)
            ->where('workflow_progress.completed_count', 0)
            ->count();
        $inProgressContracts = collect($allRows)
            ->filter(fn (array $row): bool => $row['workflow_progress']['completed_count'] > 0
                && $row['workflow_progress']['completed_count'] < count(ContractWorkflowStep::STEP_KEYS))
            ->count();
        $overallProgress = $totalContracts > 0
            ? round(collect($allRows)->avg('workflow_progress.percent'), 1)
            : 0;
        // Manual Pagination
        $perPage = 10;
        $currentPage = $this->getPage();
        $currentItems = array_slice($allRows, ($currentPage - 1) * $perPage, $perPage);
        $pipeline = collect(ContractWorkflowStep::STEP_KEYS)
            ->mapWithKeys(fn (string $stepKey): array => [
                $stepKey => collect($allRows)
                    ->where('current_step_key', $stepKey)
                    ->values()
                    ->all(),
            ])
            ->all();
        $paginatedRows = new LengthAwarePaginator(
            $currentItems,
            count($allRows),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $contractTypes = array_map(fn ($s) => $s[1], $this->contractSources());

        $assignedStaffs = User::role([Role::TU_VAN->value, Role::KY_THUAT->value])
            ->orderBy('name')
            ->get();

        return view('livewire.admin.reports.sales.sales-project-progress-report', [
            'items' => $paginatedRows,
            'summary' => (object) [
                'total' => $totalContracts,
                'not_started' => $notStartedContracts,
                'active' => $inProgressContracts,
                'completed' => $completedContracts,
                'progress' => $overallProgress,
            ],
            'pipeline' => $pipeline,
            'serviceOptions' => $serviceOptions,
            'contractTypes' => $contractTypes,
            'assignedStaffs' => $assignedStaffs,
        ])->layout('admin.layouts.app');
    }
}
