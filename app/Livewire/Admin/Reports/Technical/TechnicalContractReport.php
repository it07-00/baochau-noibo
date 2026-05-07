<?php

namespace App\Livewire\Admin\Reports\Technical;

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

class TechnicalContractReport extends Component
{
    use WithPagination;

    public int $year;

    public string $filter_service = '';

    public string $filter_status = '';

    public int|string $filter_staff = '';

    public string $contract_type = 'waste';

    public string $page_title = 'Báo cáo kỹ thuật';

    public array $years = [];

    private const TYPE_MAP = [
        'waste' => ['model' => ContractWaste::class,          'label' => 'BC Chất thải & Tiếng ồn'],
        'consulting' => ['model' => ContractLegal::class,     'label' => 'Hồ sơ môi trường'],
        'project' => ['model' => ContractTechnical::class,        'label' => 'BC Kỹ thuật & Ứng phó SC'],
        'commercial' => ['model' => ContractResearch::class,     'label' => 'BC NC & CĐ Công nghệ'],
        'sustainability' => ['model' => ContractSustainability::class, 'label' => 'BC TV & BC PTBV'],
        'energy' => ['model' => ContractEmission::class,         'label' => 'BC Phát thải & Năng lượng'],
    ];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);

        $routeName = Route::currentRouteName();
        $this->contract_type = match ($routeName) {
            'app.reports.technical.waste' => 'waste',
            'app.reports.technical.consulting' => 'consulting',
            'app.reports.technical.project' => 'project',
            'app.reports.technical.commercial' => 'commercial',
            'app.reports.technical.sustainability' => 'sustainability',
            'app.reports.technical.energy' => 'energy',
            default => 'waste',
        };

        $this->page_title = self::TYPE_MAP[$this->contract_type]['label'];

        // Nhân viên kỹ thuật chỉ xem hợp đồng được giao cho mình
        if ($this->isRestrictedTechnical()) {
            $this->filter_staff = (string) auth()->id();
        }
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
        // Khóa bộ lọc nếu là nhân viên kỹ thuật bị hạn chế
        if ($this->isRestrictedTechnical()) {
            $this->filter_staff = (string) auth()->id();
        }
        $this->resetPage();
    }

    private function isRestrictedTechnical(): bool
    {
        $user = auth()->user();

        return $user->hasRole(Role::KY_THUAT->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
    }

    private function getModelClass(): string
    {
        return self::TYPE_MAP[$this->contract_type]['model'];
    }

    private function baseQuery()
    {
        $modelClass = $this->getModelClass();
        $isRestricted = $this->isRestrictedTechnical();
        $effectiveStaff = $isRestricted ? (string) auth()->id() : $this->filter_staff;

        $query = $modelClass::whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $this->year)
            ->when($this->filter_service, fn ($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->when($this->filter_status === 'not_started', fn ($q) => $q->whereDoesntHave('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass))
            )
            ->when($this->filter_status === 'in_progress', fn ($q) => $q->whereHas('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass))
                ->whereDoesntHave('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass)->where('step_name', 'finished'))
            )
            ->when($this->filter_status === 'finished', fn ($q) => $q->whereHas('workflowSteps', fn ($s) => $s->where('contract_type', $modelClass)->where('step_name', 'finished'))
            );

        // Luôn scope theo bộ phận kỹ thuật (chỉ hiển thị hợp đồng được giao cho nhân viên kỹ thuật)
        $query->whereHas('assignments', function ($q) use ($effectiveStaff) {
            $q->whereHas('user', fn ($u) => $u->role('ky-thuat'));
            if ($effectiveStaff !== '') {
                $q->where('user_id', $effectiveStaff);
            }
        });

        return $query;
    }

    private function getWorkflowProgress($items)
    {
        // Xác định role của user hiện tại
        $user = auth()->user();
        $userRole = null;
        if ($user) {
            if ($user->hasRole('ky-thuat')) {
                $userRole = 'ky-thuat';
            } elseif ($user->hasRole('tu-van')) {
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
        $items = $this->baseQuery()
            ->with(['customer', 'staff', 'assignments.user'])
            ->orderByDesc('signed_at')
            ->paginate(20);

        $modelClass = $this->getModelClass();
        $allIds = $this->baseQuery()->pluck('id');
        $total = $allIds->count();

        // Lấy tất cả bước workflow của các hợp đồng này
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
        $user = auth()->user();
        $userRole = null;
        if ($user) {
            if ($user->hasRole('ky-thuat')) {
                $userRole = 'ky-thuat';
            } elseif ($user->hasRole('tu-van')) {
                $userRole = 'tu-van';
            }
        }

        // Lấy danh sách step labels phù hợp với role
        $stepsData = ContractWorkflowStep::getStepsByRole($userRole);
        $stepLabels = $stepsData['steps'];

        $isRestricted = $this->isRestrictedTechnical();
        $staffs = $isRestricted
            ? User::where('id', auth()->id())->get()
            : User::role('ky-thuat')->orderBy('name')->get();

        $serviceTypes = defined("$modelClass::SERVICE_TYPES") ? $modelClass::SERVICE_TYPES : [];

        return view('livewire.admin.reports.technical.technical-contract-report',
            compact('items', 'summary', 'staffs', 'serviceTypes', 'workflowProgress', 'stepLabels', 'isRestricted'))
            ->layout('admin.layouts.app');
    }
}
