<?php

namespace App\Livewire\Admin\Reports\Technical;

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
        'waste'          => ['model' => ContractWaste::class,          'label' => 'BC Chất thải & Tiếng ồn'],
        'consulting'     => ['model' => ContractConsulting::class,     'label' => 'BC Pháp lý & Hồ sơ MT'],
        'project'        => ['model' => ContractProject::class,        'label' => 'BC Kỹ thuật & Ứng phó SC'],
        'commercial'     => ['model' => ContractCommercial::class,     'label' => 'BC NC & CĐ Công nghệ'],
        'sustainability' => ['model' => ContractSustainability::class, 'label' => 'BC TV & BC PTBV'],
        'energy'         => ['model' => ContractEnergy::class,         'label' => 'BC Phát thải & Năng lượng'],
    ];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);

        $routeName = Route::currentRouteName();
        $this->contract_type = match ($routeName) {
            'app.reports.technical.waste'          => 'waste',
            'app.reports.technical.consulting'     => 'consulting',
            'app.reports.technical.project'        => 'project',
            'app.reports.technical.commercial'     => 'commercial',
            'app.reports.technical.sustainability' => 'sustainability',
            'app.reports.technical.energy'         => 'energy',
            default                                => 'waste',
        };

        $this->page_title = self::TYPE_MAP[$this->contract_type]['label'];
    }

    public function updatedYear(): void          { $this->resetPage(); }
    public function updatedFilterService(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterStaff(): void   { $this->resetPage(); }

    private function getModelClass(): string
    {
        return self::TYPE_MAP[$this->contract_type]['model'];
    }

    private function baseQuery()
    {
        $modelClass = $this->getModelClass();

        return $modelClass::whereYear('signed_at', $this->year)
            ->when($this->filter_service, fn($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->when($this->filter_staff, fn($q) => $q->where('staff_id', $this->filter_staff));
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

        $staffs = User::orderBy('name')->get();
        $modelClass = $this->getModelClass();
        $serviceTypes = defined("$modelClass::SERVICE_TYPES") ? $modelClass::SERVICE_TYPES : [];

        return view('livewire.admin.reports.technical.technical-contract-report',
            compact('items', 'summary', 'staffs', 'serviceTypes', 'workflowProgress', 'stepLabels'))
            ->layout('admin.layouts.app');
    }
}
