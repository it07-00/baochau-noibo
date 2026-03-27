<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractWorkflowStep;
use App\Models\ContractMilestoneFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContractWorkflowPanel extends Component
{
    use WithFileUploads;

    public string $contractType;
    public int $contractId;

    // model class map
    protected array $modelMap = [
        'waste'          => \App\Models\ContractWaste::class,
        'consulting'     => \App\Models\ContractConsulting::class,
        'project'        => \App\Models\ContractProject::class,
        'commercial'     => \App\Models\ContractCommercial::class,
        'sustainability' => \App\Models\ContractSustainability::class,
        'energy'         => \App\Models\ContractEnergy::class,
    ];

    public $uploadFile = null;
    public string $comment = '';
    public ?string $activeStep = null; // step đang mở để confirm

    public function mount(string $contractType, int $contractId): void
    {
        $this->contractType = $contractType;
        $this->contractId   = $contractId;
    }

    /**
     * Mở confirm panel cho 1 bước — chỉ tu-van và ky-thuat
     */
    public function openStep(string $step): void
    {
        if (!auth()->user()->hasAnyRole(['tu-van', 'ky-thuat'])) {
            return;
        }
        $this->activeStep  = $step;
        $this->uploadFile  = null;
        $this->comment     = '';
    }

    /**
     * Ghi nhận hoàn thành bước — chỉ tu-van và ky-thuat
     */
    public function completeStep(): void
    {
        if (!auth()->user()->hasAnyRole(['tu-van', 'ky-thuat'])) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            return;
        }

        $this->validate([
            'uploadFile' => 'required|file|max:20480', // max 20MB
            'comment'    => 'nullable|max:1000',
        ], [
            'uploadFile.required' => 'Vui lòng đính kèm file trước khi xác nhận bước này.',
        ]);

        // Lưu file
        $path = $this->uploadFile->storePublicly(
            'contract-files/' . $this->contractType . '/' . $this->activeStep,
            'public'
        );

        // Lưu milestone file record
        ContractMilestoneFile::create([
            'contract_type' => $this->getModelClass(),
            'contract_id'   => $this->contractId,
            'milestone'     => $this->activeStep,
            'file_path'     => $path,
            'original_name' => $this->uploadFile->getClientOriginalName(),
            'uploader_id'   => auth()->id(),
        ]);

        // Lưu workflow step record
        ContractWorkflowStep::create([
            'contract_type' => $this->getModelClass(),
            'contract_id'   => $this->contractId,
            'user_id'       => auth()->id(),
            'step_name'     => $this->activeStep,
            'action'        => 'complete',
            'comment'       => $this->comment ?: null,
        ]);

        // Cập nhật workflow_status trên contract
        $modelClass = $this->getModelClass();
        $modelClass::find($this->contractId)?->update([
            'workflow_status' => $this->activeStep,
        ]);

        $stepLabel = ContractWorkflowStep::STEPS[$this->activeStep] ?? $this->activeStep;

        $this->activeStep = null;
        $this->uploadFile = null;
        $this->comment    = '';

        $this->dispatch('swal:toast', [
            'type'    => 'success',
            'message' => 'Đã hoàn thành bước: ' . $stepLabel,
        ]);
    }

    public function cancelStep(): void
    {
        $this->activeStep = null;
        $this->uploadFile = null;
        $this->comment    = '';
    }

    private function getModelClass(): string
    {
        return $this->modelMap[$this->contractType] ?? '';
    }

    public function render()
    {
        $modelClass    = $this->getModelClass();
        $contract      = $modelClass ? $modelClass::find($this->contractId) : null;
        $currentStatus = $contract?->workflow_status;

        // Steps đã hoàn thành (lấy distinct step_name từ db)
        $completedSteps = ContractWorkflowStep::where('contract_type', $modelClass)
            ->where('contract_id', $this->contractId)
            ->pluck('step_name')
            ->toArray();

        // Files đã upload theo từng bước
        $filesByStep = ContractMilestoneFile::where('contract_type', $modelClass)
            ->where('contract_id', $this->contractId)
            ->get()
            ->groupBy('milestone');

        return view('livewire.admin.contracts.contract-workflow-panel', [
            'steps'          => ContractWorkflowStep::STEPS,
            'stepKeys'       => ContractWorkflowStep::STEP_KEYS,
            'completedSteps' => $completedSteps,
            'currentStatus'  => $currentStatus,
            'filesByStep'    => $filesByStep,
            'canEdit'        => auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']),
        ]);
    }
}
