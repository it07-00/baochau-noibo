<?php

namespace App\Livewire\Admin\Reports\Consulting;

use App\Enums\Role;
use App\Models\ContractResearch;
use App\Models\ContractLegal;
use App\Models\ContractEmission;
use App\Models\ContractTechnical;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        'waste' => ['model' => ContractWaste::class,          'label' => 'BC CV Chất thải & Tiếng ồn'],
        'consulting' => ['model' => ContractLegal::class,     'label' => 'BC CV Pháp lý & Hồ sơ MT'],
        'project' => ['model' => ContractTechnical::class,        'label' => 'BC CV Kỹ thuật & Ứng phó SC'],
        'commercial' => ['model' => ContractResearch::class,     'label' => 'BC CV NC & CĐ Công nghệ'],
        'sustainability' => ['model' => ContractSustainability::class, 'label' => 'BC CV TV & BC PTBV'],
        'energy' => ['model' => ContractEmission::class,         'label' => 'BC CV Phát thải & Năng lượng'],
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
            'app.reports.consulting-work.waste' => 'waste',
            'app.reports.consulting-work.consulting' => 'consulting',
            'app.reports.consulting-work.project' => 'project',
            'app.reports.consulting-work.commercial' => 'commercial',
            'app.reports.consulting-work.sustainability' => 'sustainability',
            'app.reports.consulting-work.energy' => 'energy',
            default => 'waste',
        };

        $this->page_title = self::TYPE_MAP[$this->contract_type]['label'];
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterService(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

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

        return $user->hasRole(Role::TU_VAN->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
    }

    private function baseQuery()
    {
        $modelClass = $this->getModelClass();
        $user = auth()->user();
        $isRestrictedConsultant = $this->isRestrictedConsultant($user);
        $effectiveStaffFilter = $isRestrictedConsultant ? (string) $user->id : (string) $this->filter_staff;

        $query = $modelClass::whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
            ->when($this->filter_service, fn ($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->when($this->filter_status === 'not_started', fn ($q) => $q->whereDoesntHave('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass))
            )
            ->when($this->filter_status === 'in_progress', fn ($q) => $q->whereHas('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass))
                ->whereDoesntHave('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass)->where('step_name', 'finished'))
            )
            ->when($this->filter_status === 'finished', fn ($q) => $q->whereHas('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass)->where('step_name', 'finished'))
            );

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
        // Xác định role của user hiện tại
        $user = auth()->user();
        $userRole = null;
        if ($user) {
            if ($user->hasRole(Role::KY_THUAT->value)) {
                $userRole = 'ky-thuat';
            } elseif ($user->hasRole(Role::TU_VAN->value)) {
                $userRole = 'tu-van';
            }
        }

        // Lấy danh sách bước workflow phù hợp với role
        $stepsData = ContractWorkflowStep::getStepsByRole($userRole);
        $stepKeys = $stepsData['stepKeys'];
        $stepLabels = $stepsData['steps'];
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
                'total_steps' => $totalSteps,
                'percent' => $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0,
                'current_label' => $currentStep ? ($stepLabels[$currentStep] ?? $currentStep) : 'Chưa bắt đầu',
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

        $modelClass = $this->getModelClass();
        $allIds = $this->baseQuery()->pluck('id');
        $total = $allIds->count();

        $stepsByContract = ContractWorkflowStep::where('contract_type', $modelClass)
            ->whereIn('contract_id', $allIds)
            ->get()
            ->groupBy('contract_id');

        $completed = 0;
        $active = 0;
        foreach ($allIds as $id) {
            $steps = $stepsByContract->get($id, collect());
            $stepNames = $steps->pluck('step_name')->unique()->toArray();
            if (in_array('finished', $stepNames)) {
                $completed++;
            } elseif (count($stepNames) > 0) {
                $active++;
            }
        }

        $summary = (object) ['total' => $total, 'completed' => $completed, 'active' => $active];

        $workflowProgress = $this->getWorkflowProgress($items);

        // Xác định role của user hiện tại
        $userRole = null;
        if ($user) {
            if ($user->hasRole(Role::KY_THUAT->value)) {
                $userRole = 'ky-thuat';
            } elseif ($user->hasRole(Role::TU_VAN->value)) {
                $userRole = 'tu-van';
            }
        }

        // Lấy danh sách step labels phù hợp với role
        $stepsData = ContractWorkflowStep::getStepsByRole($userRole);
        $stepLabels = $stepsData['steps'];

        $staffs = User::role('tu-van')
            ->when($isRestrictedConsultant, fn ($q) => $q->where('id', $user->id))
            ->orderBy('name')
            ->get();
        $serviceTypes = defined("$modelClass::SERVICE_TYPES") ? $modelClass::SERVICE_TYPES : [];

        return view('livewire.admin.reports.consulting.consulting-contract-report',
            compact('items', 'summary', 'staffs', 'serviceTypes', 'workflowProgress', 'stepLabels', 'isRestrictedConsultant'))
            ->layout('admin.layouts.app');
    }
}
