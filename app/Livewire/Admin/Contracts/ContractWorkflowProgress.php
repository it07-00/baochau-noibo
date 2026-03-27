<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractMilestoneFile;
use App\Models\ContractWorkflowStep;
use Livewire\Component;

class ContractWorkflowProgress extends Component
{
    public string $contractType;
    public int $contractId;

    protected array $modelMap = [
        'waste'          => \App\Models\ContractWaste::class,
        'consulting'     => \App\Models\ContractConsulting::class,
        'project'        => \App\Models\ContractProject::class,
        'commercial'     => \App\Models\ContractCommercial::class,
        'sustainability' => \App\Models\ContractSustainability::class,
        'energy'         => \App\Models\ContractEnergy::class,
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

        return view('livewire.admin.contracts.contract-workflow-progress', [
            'steps'          => ContractWorkflowStep::STEPS,
            'stepKeys'       => ContractWorkflowStep::STEP_KEYS,
            'completedSteps' => $completedSteps,
            'filesByStep'    => $filesByStep,
        ]);
    }
}
