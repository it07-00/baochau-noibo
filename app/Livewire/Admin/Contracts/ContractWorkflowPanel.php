<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractWorkflowStep;
use App\Models\ContractMilestoneFile;
use App\Models\User;
use App\Notifications\ContractWorkflowUpdatedNotification;
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

    public array $uploadFiles = [];
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
        $this->uploadFiles = [];
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

        $rules = [
            'uploadFiles'   => ($this->activeStep === 'receiving' ? 'nullable' : 'required') . '|array|max:10' . ($this->activeStep === 'receiving' ? '' : '|min:1'),
            'uploadFiles.*' => 'file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'comment'       => 'nullable|string|max:1000',
        ];

        $messages = [
            'uploadFiles.required' => 'Vui lòng đính kèm ít nhất 1 file trước khi xác nhận bước này.',
            'uploadFiles.min'      => 'Vui lòng đính kèm ít nhất 1 file trước khi xác nhận bước này.',
            'uploadFiles.*.max'        => 'Mỗi file không được vượt quá 20MB.',
            'uploadFiles.*.extensions' => 'Chỉ chấp nhận file PDF, Word, Excel, JPG, PNG.',
        ];

        $this->validate($rules, $messages);

        $uploadDisk = config('filesystems.upload_disk', 'public');

        if (!empty($this->uploadFiles)) {
            foreach ($this->uploadFiles as $file) {
                $path = $file->storePublicly(
                    'contract-files/' . $this->contractType . '/' . $this->activeStep,
                    $uploadDisk
                );

                ContractMilestoneFile::create([
                    'contract_type' => $this->getModelClass(),
                    'contract_id'   => $this->contractId,
                    'milestone'     => $this->activeStep,
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploader_id'   => auth()->id(),
                ]);
            }
        }

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
        $completedStep = $this->activeStep;

        $this->activeStep  = null;
        $this->uploadFiles = [];
        $this->comment     = '';

        $this->dispatch('swal:toast', [
            'type'    => 'success',
            'message' => 'Đã hoàn thành bước: ' . $stepLabel,
        ]);

        // Gửi thông báo đến quản lý + NV kinh doanh phụ trách
        $contract = $modelClass ? $modelClass::with('customer')->find($this->contractId) : null;
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$this->contractId);

        // Map contract type key từ model class
        $typeKey = array_search($modelClass, $this->modelMap) ?: $this->contractType;

        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', ['giam-doc', 'quan-ly', 'it']))->get();
        if ($contract?->staff_id && $contract->staff_id !== auth()->id()) {
            $staff = User::find($contract->staff_id);
            if ($staff) $recipients->push($staff);
        }
        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->id !== auth()->id()) {
                $recipient->notify(new ContractWorkflowUpdatedNotification($typeKey, $this->contractId, $contractLabel, $completedStep, auth()->user()->name));
            }
        }
    }

    public function cancelStep(): void
    {
        $this->activeStep  = null;
        $this->uploadFiles = [];
        $this->comment     = '';
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
