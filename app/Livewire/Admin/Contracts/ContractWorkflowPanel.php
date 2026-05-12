<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\Role;
use App\Models\ContractWorkflowStep;
use App\Models\ContractMilestoneFile;
use App\Models\ContractAssignment;
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
        'consulting'     => \App\Models\ContractLegal::class,
        'project'        => \App\Models\ContractTechnical::class,
        'commercial'     => \App\Models\ContractResearch::class,
        'sustainability' => \App\Models\ContractSustainability::class,
        'energy'         => \App\Models\ContractEmission::class,
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
        if (!auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value])) {
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
        if (!auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value])) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            return;
        }

        // Bộ phận kỹ thuật: survey, waiting_client, client_confirmed không bắt buộc upload file
        $isKyThuat = auth()->user()->hasRole(Role::KY_THUAT->value);
        $kyThuatOptionalSteps = ['survey', 'waiting_client', 'client_confirmed'];
        $fileRequired = !($this->activeStep === 'receiving' || ($isKyThuat && in_array($this->activeStep, $kyThuatOptionalSteps)));

        $rules = [
            'uploadFiles'   => ($fileRequired ? 'required|array|max:10|min:1' : 'nullable|array|max:10'),
            'uploadFiles.*' => 'file|max:204800|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'comment'       => 'nullable|string|max:1000',
        ];

        $messages = [
            'uploadFiles.required' => 'Vui lòng đính kèm ít nhất 1 file trước khi xác nhận bước này.',
            'uploadFiles.min'      => 'Vui lòng đính kèm ít nhất 1 file trước khi xác nhận bước này.',
            'uploadFiles.*.max'        => 'Mỗi file không được vượt quá 200MB.',
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

        $stepLabel = $this->getStepLabel($this->activeStep);
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

        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', [Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]))->get();

        $assignmentUserIds = ContractAssignment::where('assignable_type', $modelClass)
            ->where('assignable_id', $this->contractId)
            ->whereNotNull('user_id')
            ->get(['user_id', 'assigned_by'])
            ->flatMap(fn($assignment) => [(int) $assignment->user_id, (int) $assignment->assigned_by])
            ->filter()
            ->unique()
            ->values();

        if ($assignmentUserIds->isNotEmpty()) {
            $recipients = $recipients->merge(User::whereIn('id', $assignmentUserIds)->get());
        }

        if ($contract?->staff_id && $contract->staff_id !== auth()->id()) {
            $staff = User::find($contract->staff_id);
            if ($staff) $recipients->push($staff);
        }
        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->id !== auth()->id()) {
                $recipient->notify(new ContractWorkflowUpdatedNotification($typeKey, $this->contractId, $contractLabel, $completedStep, $stepLabel, auth()->user()->name));
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

    private function getStepLabel(?string $stepName): string
    {
        if (!$stepName) {
            return $stepName ?? '';
        }

        // Xác định role của user hiện tại
        $user = auth()->user();
        $userRole = null;
        if ($user) {
            if ($user->hasRole(Role::KY_THUAT->value)) {
                $userRole = Role::KY_THUAT->value;
            } elseif ($user->hasRole(Role::TU_VAN->value)) {
                $userRole = Role::TU_VAN->value;
            }
        }

        // Lấy danh sách step labels phù hợp với role
        $stepsData = ContractWorkflowStep::getStepsByRole($userRole);
        $stepLabels = $stepsData['steps'];

        return $stepLabels[$stepName] ?? $stepName;
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

        // Xác định role của user hiện tại
        $user = auth()->user();
        $userRole = null;
        if ($user) {
            if ($user->hasRole(Role::KY_THUAT->value)) {
                $userRole = Role::KY_THUAT->value;
            } elseif ($user->hasRole(Role::TU_VAN->value)) {
                $userRole = Role::TU_VAN->value;
            }
        }

        // Lấy danh sách bước workflow phù hợp với role
        $stepsData = ContractWorkflowStep::getStepsByRole($userRole);

        return view('livewire.admin.contracts.contract-workflow-panel', [
            'steps'          => $stepsData['steps'],
            'stepKeys'       => $stepsData['stepKeys'],
            'completedSteps' => $completedSteps,
            'currentStatus'  => $currentStatus,
            'filesByStep'    => $filesByStep,
            'canEdit'        => auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
        ]);
    }
}
