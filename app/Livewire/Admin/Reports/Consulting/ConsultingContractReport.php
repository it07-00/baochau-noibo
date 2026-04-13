<?php

namespace App\Livewire\Admin\Reports\Consulting;

use App\Models\ContractAssignment;
use App\Models\ContractWaste;
use App\Models\ContractConsulting;
use App\Models\ContractProject;
use App\Models\ContractCommercial;
use App\Models\ContractSustainability;
use App\Models\ContractEnergy;
use App\Models\ContractWorkflowStep;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\WithPagination;

class ConsultingContractReport extends Component
{
    use WithPagination;

    public int $year;
    public string $filter_service = '';
    public string $filter_status = '';
    public int|string $filter_staff = '';
    public string $contract_type = 'waste';
    public string $page_title = 'Báo cáo công việc tư vấn';
    public array $years = [];

    private const TYPE_MAP = [
        'waste'          => ['model' => ContractWaste::class,          'label' => 'BC CV Chất thải & Tiếng ồn'],
        'consulting'     => ['model' => ContractConsulting::class,     'label' => 'BC CV Pháp lý & Hồ sơ MT'],
        'project'        => ['model' => ContractProject::class,        'label' => 'BC CV Kỹ thuật & Ứng phó SC'],
        'commercial'     => ['model' => ContractCommercial::class,     'label' => 'BC CV NC & CĐ Công nghệ'],
        'sustainability' => ['model' => ContractSustainability::class, 'label' => 'BC CV TV & BC PTBV'],
        'energy'         => ['model' => ContractEnergy::class,         'label' => 'BC CV Phát thải & Năng lượng'],
    ];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);

        if ($this->isRestrictedConsultant()) {
            $this->filter_staff = (string) auth()->id();
        }

        $routeName = Route::currentRouteName();
        $this->contract_type = match ($routeName) {
            'app.reports.consulting-work.waste'          => 'waste',
            'app.reports.consulting-work.consulting'     => 'consulting',
            'app.reports.consulting-work.project'        => 'project',
            'app.reports.consulting-work.commercial'     => 'commercial',
            'app.reports.consulting-work.sustainability' => 'sustainability',
            'app.reports.consulting-work.energy'         => 'energy',
            default                                      => 'waste',
        };

        $this->page_title = self::TYPE_MAP[$this->contract_type]['label'];
    }

    public function updatedYear(): void          { $this->resetPage(); }
    public function updatedFilterService(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterStaff(): void
    {
        if ($this->isRestrictedConsultant()) {
            $this->filter_staff = (string) auth()->id();
        }

        $this->resetPage();
    }

    private function getModelClass(): string
    {
        return self::TYPE_MAP[$this->contract_type]['model'];
    }

    private function isRestrictedConsultant(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user->hasRole('tu-van')
            && !$user->hasAnyRole(['admin', 'giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']);
    }

    private function baseQuery()
    {
        $modelClass = $this->getModelClass();
        $user = auth()->user();
        $isRestrictedConsultant = $this->isRestrictedConsultant($user);
        $effectiveStaffFilter = $isRestrictedConsultant ? (string) $user->id : (string) $this->filter_staff;

        $query = $modelClass::whereYear('signed_at', $this->year)
            ->when($this->filter_service, fn ($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->when($this->filter_status, fn ($q) => $q->where('status', $this->filter_status));

        // Scope to contracts that have tu-van assignments
        $query->whereHas('assignments', function ($q) use ($user) {
            $q->whereHas('user', fn ($u) => $u->role('tu-van'));

            // Restrict pure tu-van users to their own assignments.
            if ($this->isRestrictedConsultant($user)) {
                $q->where('user_id', $user->id);
            }
        });

        // Filter by specific tu-van staff
        if ($effectiveStaffFilter !== '') {
            $query->whereHas('assignments', fn ($q) => $q->where('user_id', $effectiveStaffFilter));
        }

        return $query;
    }

    private function getWorkflowProgress($items)
    {
        $stepKeys = ContractWorkflowStep::STEP_KEYS;
        $stepLabels = ContractWorkflowStep::STEPS;
        $totalSteps = count($stepKeys);
        $modelClass = $this->getModelClass();

        $contractIds = $items->pluck('id');
        $allSteps = ContractWorkflowStep::where('contract_type', $modelClass)
            ->whereIn('contract_id', $contractIds)
            ->get()
            ->groupBy('contract_id');

        $progress = [];
        foreach ($items as $item) {
            $steps = $allSteps->get($item->id, collect());
            $completedSteps = $steps->pluck('step_name')->unique()->toArray();
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

            $progress[$item->id] = [
                'completed_count' => $completedCount,
                'total_steps'     => $totalSteps,
                'percent'         => $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0,
                'current_label'   => $currentStep ? ($stepLabels[$currentStep] ?? $currentStep) : 'Chưa bắt đầu',
            ];
        }

        return $progress;
    }

    public function render()
    {
        $user = auth()->user();
        $isRestrictedConsultant = $this->isRestrictedConsultant($user);

        $items = $this->baseQuery()
            ->with(['customer', 'staff', 'assignments.user'])
            ->orderByDesc('signed_at')
            ->paginate(20);

        $summary = $this->baseQuery()
            ->selectRaw('COUNT(*) as total,
                SUM(CASE WHEN status = "HOÀN THÀNH" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "ĐANG THỰC HIỆN" THEN 1 ELSE 0 END) as active')
            ->first();

        $workflowProgress = $this->getWorkflowProgress($items);
        $stepLabels = ContractWorkflowStep::STEPS;

        $staffs = User::role('tu-van')
            ->when($isRestrictedConsultant, fn ($q) => $q->where('id', $user->id))
            ->orderBy('name')
            ->get();
        $modelClass = $this->getModelClass();
        $serviceTypes = defined("$modelClass::SERVICE_TYPES") ? $modelClass::SERVICE_TYPES : [];

        return view('livewire.admin.reports.consulting.consulting-contract-report',
            compact('items', 'summary', 'staffs', 'serviceTypes', 'workflowProgress', 'stepLabels', 'isRestrictedConsultant'))
            ->layout('admin.layouts.app');
    }
}
