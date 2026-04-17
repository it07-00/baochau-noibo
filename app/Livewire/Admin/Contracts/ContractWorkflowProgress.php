<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractMilestoneFile;
use App\Models\ContractWorkflowStep;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ContractWorkflowProgress extends Component
{
    public string $contractType;
    public int $contractId;

    protected array $modelMap = [
        'waste'          => \App\Models\ContractWaste::class,
        'consulting'     => \App\Models\ContractLegal::class,
        'project'        => \App\Models\ContractTechnical::class,
        'commercial'     => \App\Models\ContractResearch::class,
        'sustainability' => \App\Models\ContractSustainability::class,
        'energy'         => \App\Models\ContractEmission::class,
    ];

    public function mount(string $contractType, int $contractId): void
    {
        $this->contractType = $contractType;
        $this->contractId   = $contractId;
    }

    public function render()
    {
        $modelClass     = $this->modelMap[$this->contractType] ?? null;
        $completedSteps = $modelClass
            ? ContractWorkflowStep::where('contract_type', $modelClass)
                ->where('contract_id', $this->contractId)
                ->pluck('step_name')
                ->toArray()
            : [];

        $filesByStep = $modelClass
            ? ContractMilestoneFile::with('uploader')
                ->where('contract_type', $modelClass)
                ->where('contract_id', $this->contractId)
                ->get()
                ->groupBy('milestone')
            : collect();

        // Xác định role của user hiện tại
        $user = Auth::user();
        $userRole = null;
        if ($user) {
            // Kiểm tra các role liên quan
            if ($user->hasRole('ky-thuat')) {
                $userRole = 'ky-thuat';
            } elseif ($user->hasRole('tu-van')) {
                $userRole = 'tu-van';
            }
        }

        // Lấy danh sách bước workflow phù hợp với role
        $stepsData = ContractWorkflowStep::getStepsByRole($userRole);

        return view('livewire.admin.contracts.contract-workflow-progress', [
            'steps'          => $stepsData['steps'],
            'stepKeys'       => $stepsData['stepKeys'],
            'completedSteps' => $completedSteps,
            'filesByStep'    => $filesByStep,
        ]);
    }
}

