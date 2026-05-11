<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\ContractVoucherStatus;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\ContractAssignment;
use App\Models\ContractProgressNote;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Livewire\Concerns\ContractValidation;
use App\Livewire\Concerns\HasContractFilters;

abstract class AbstractContractGenericManager extends Component
{
    use WithPagination, CleanMoneyInput, ContractValidation, HasContractFilters;

    private const ALLOWED_STATUSES = [
        'PTH đang kiểm tra',
        'Đang trình BGĐ ký',
        'Đã gửi khách hàng',
        'Đã hoàn thành',
        'Hợp đồng hủy',
    ];

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $sortDirection = 'desc';
    public array $selectedDocIds = [];
    public bool $showModal = false;
    public bool $isEditing = false;
    public bool $isDuplicating = false;
    public $showDetail = false;
    public $selectedDoc = null;
    public bool $showAssignModal = false;
    public ?int $assignContractId = null;
    public array $assignUserIds = [];
    public ?string $assignDeadline = null;
    public array $createAssignUserIds = [];
    public ?string $createAssignDeadline = null;
    public ?string $assignExternal = null;
    public ?string $createAssignExternal = null;
    public string $progressNote = '';
    public $progressNotes = [];
    public ?int $workflowContractId = null;
    public ?int $quotation_id = null;

    public $formData = [
        'shd_cxl'            => '',
        'shd_bc'             => '',
        'customer_id'        => '',
        'handler_id'         => '',
        'staff_id'           => '',
        'department_id'      => '',
        'signed_at'          => '',
        'submitted_at'       => '',
        'value'              => 0,
        'commission'         => 0,
        'revenue'            => 0,
        'province'           => '',
        'info_source'        => 'MỚI',
        'payment_method'     => 'Sau ký',
        'loai_dich_vu'       => '',
        'status'             => 'PTH đang kiểm tra',
        'renewal_status'     => '',
        'voucher_status'     => '',
        'is_offset'          => false,
        'has_room_fund'      => false,
        'is_overdue'         => false,
        'notes'              => '',
        'is_renewal'         => false,
        'parent_contract_id' => '',
    ];

    public $filter = [
        'signed_from'    => '',
        'signed_to'      => '',
        'submitted_from' => '',
        'submitted_to'   => '',
        'province'       => '',
        'department_id'  => '',
        'staff_id'       => '',
        'info_source'    => '',
        'payment_method' => '',
        'status'         => '',
        'renewal_status' => '',
        'voucher_status' => '',
        'is_offset'      => false,
        'has_room_fund'  => false,
        'is_overdue'     => false,
        'loai_dich_vu'   => '',
        'handler_id'     => '',
    ];

    public string $contractTypeName = '';

    protected $queryString = ['search', 'quotation_id'];

    // ── Template methods (subclasses must implement) ─────────────────────────

    abstract protected function getModelClass(): string;

    abstract protected function getContractType(): string;

    abstract protected function getPermCreate(): Permission;

    abstract protected function getPermEdit(): Permission;

    abstract protected function getPermDelete(): Permission;

    abstract protected function getViewName(): string;

    abstract protected function getPageTitle(): string;

    abstract protected function getExportTitle(): string;

    abstract protected function getExportFilenamePrefix(): string;

    // ── Common lifecycle ─────────────────────────────────────────────────────

    public function paginationView(): string
    {
        return 'livewire.admin.users.pagination';
    }

    public function mount(): void
    {
        $this->contractTypeName = $this->getPageTitle();

        if ($this->quotation_id) {
            $quotation = Quotation::find($this->quotation_id);
            if ($quotation) {
                $customer = Customer::firstOrCreate(
                    ['name' => $quotation->company_name ?? ''],
                    [
                        'address'        => $quotation->address ?? '',
                        'province'       => $quotation->province ?? '',
                        'representative' => $quotation->contact_person ?? '',
                    ]
                );
                $this->formData['customer_id'] = $customer->id;
                $this->formData['value']       = $quotation->total_value ?? 0;
                $this->formData['commission']  = $quotation->commission_value ?? 0;
                $this->formData['revenue']     = $quotation->original_value ?? 0;
                $this->formData['staff_id']    = $quotation->staff_id ?? auth()->id();
                $this->formData['province']    = $quotation->province ?? '';
                $this->formData['notes']       = $quotation->notes ?? '';
                $this->formData['info_source'] = 'MỚI';
                $this->ensureDepartmentId();
                $this->showModal = true;
                $this->dispatch('openFormModal');
            }
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFormDataValue(): void
    {
        if (!$this->isEditing) {
            $this->formData['revenue'] = $this->formData['value'];
        }
    }

    public function updatedSortDirection($value): void
    {
        $this->sortDirection = $value === 'asc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    // ── CRUD actions ─────────────────────────────────────────────────────────

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isDuplicating = false;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()->can($this->getPermEdit()->value), 403);
        $modelClass        = $this->getModelClass();
        $this->selectedDoc = $modelClass::with(['assignments.user'])->findOrFail($id);
        $this->formData    = $this->selectedDoc->toArray();
        if ($this->selectedDoc->signed_at) {
            $this->formData['signed_at'] = $this->selectedDoc->signed_at->format('Y-m-d');
        }
        if ($this->selectedDoc->submitted_at) {
            $this->formData['submitted_at'] = $this->selectedDoc->submitted_at->format('Y-m-d');
        }
        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function save(): void
    {
        $user       = auth()->user();
        $modelClass = $this->getModelClass();

        if (!$user->can($this->isEditing ? $this->getPermEdit()->value : $this->getPermCreate()->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu hợp đồng này.']);
            return;
        }

        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);
        if ($this->isEditing && $isRestrictedTpKd && $this->selectedDoc->staff_id !== $user->id) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật hợp đồng do bạn phụ trách.']);
            return;
        }

        if (!$user->hasAnyRole([Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value])) {
            $this->formData['staff_id'] = ($this->isEditing && $this->selectedDoc)
                ? ($this->selectedDoc->staff_id ?: $user->id)
                : $user->id;
        }

        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue']);
        $this->ensureDepartmentId();

        try {
            $this->validate($this->baseContractRules(), $this->contractValidationMessages());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            if ($firstError) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => $firstError]);
            }
            throw $e;
        }

        $data = collect($this->formData)->map(fn($v) => $v === '' ? null : $v)->toArray();

        $isAccountant = $user->hasRole(Role::KE_TOAN->value);
        if (!$this->isEditing) {
            $data['shd_bc'] = null;
        } elseif (!$isAccountant && $this->selectedDoc) {
            $data['shd_bc'] = $this->selectedDoc->shd_bc;
        }

        if ($this->isEditing && $this->selectedDoc) {
            $this->selectedDoc->update($data);
        } else {
            $newContract = $modelClass::create($data);

            if (count($this->createAssignUserIds) > 0 || !empty($this->createAssignExternal)) {
                $contractType  = $this->getContractType();
                $contractLabel = $newContract->shd_bc ?: ($newContract->customer?->name ?: 'HĐ #' . $newContract->id);
                foreach ($this->createAssignUserIds as $userId) {
                    ContractAssignment::create([
                        'assignable_type' => $modelClass,
                        'assignable_id'   => $newContract->id,
                        'user_id'         => (int) $userId,
                        'assigned_by'     => auth()->id(),
                        'deadline'        => $this->createAssignDeadline ?: null,
                    ]);
                    $assignedUser = User::find($userId);
                    if ($assignedUser && $assignedUser->id !== auth()->id()) {
                        $assignedUser->notify(new ContractAssignedNotification($contractType, $newContract->id, $contractLabel, auth()->user()->name));
                    }
                }
                if (!empty($this->createAssignExternal)) {
                    ContractAssignment::create([
                        'assignable_type'   => $modelClass,
                        'assignable_id'     => $newContract->id,
                        'user_id'           => null,
                        'external_assignee' => $this->createAssignExternal,
                        'assigned_by'       => auth()->id(),
                        'deadline'          => $this->createAssignDeadline ?: null,
                    ]);
                }
            }
        }

        $this->dispatch('closeFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Lưu hợp đồng thành công!']);
        $this->resetForm();
    }

    public function updateStatus(int $id, string $status): void
    {
        $modelClass = $this->getModelClass();
        $doc        = $modelClass::findOrFail($id);
        $user       = auth()->user();

        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);
        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        } else {
            abort_if($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]), 403);
        }
        abort_unless($user->can($this->getPermEdit()->value), 403);

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            return;
        }

        $updateData = ['status' => $status];
        if ($status === 'Đã hoàn thành') {
            $updateData['submitted_at'] = now()->toDateString();
        }

        $modelClass::findOrFail($id)->update($updateData);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tình trạng!']);
    }

    public function delete(int $id): void
    {
        $modelClass = $this->getModelClass();
        $doc        = $modelClass::findOrFail($id);
        $user       = auth()->user();

        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);
        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        }
        abort_unless($user->can($this->getPermDelete()->value), 403);

        $doc->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hợp đồng!']);
    }

    public function duplicate(int $id): void
    {
        abort_unless(auth()->user()->can($this->getPermCreate()->value), 403);
        $modelClass = $this->getModelClass();
        $doc        = $modelClass::findOrFail($id);
        $this->resetForm();
        $this->formData                = $doc->toArray();
        $this->formData['signed_at']   = $doc->signed_at ? $doc->signed_at->format('Y-m-d') : date('Y-m-d');
        $this->formData['submitted_at'] = '';
        $this->formData['shd_cxl']     = '';
        $this->formData['shd_bc']      = '';
        unset($this->formData['id'], $this->formData['created_at'], $this->formData['updated_at']);
        $this->isEditing     = false;
        $this->isDuplicating = true;
        $this->selectedDoc   = null;
        $this->showModal     = true;
        $this->dispatch('openFormModal');
    }

    public function bulkDeleteSelected(): void
    {
        $user = auth()->user();
        abort_unless($user->can($this->getPermDelete()->value), 403);

        $selectedIds = collect($this->selectedDocIds)
            ->map(static fn($id) => (int) $id)
            ->filter(static fn($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Vui lòng chọn ít nhất 1 hợp đồng để xóa.']);
            return;
        }

        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);
        $deletedCount     = 0;
        $skippedCount     = 0;

        $modelClass = $this->getModelClass();
        $docs = $modelClass::whereIn('id', $selectedIds)->get();
        foreach ($docs as $doc) {
            if ($isRestrictedTpKd && (int) $doc->staff_id !== (int) $user->id) {
                $skippedCount++;
                continue;
            }
            $doc->delete();
            $deletedCount++;
        }

        $this->selectedDocIds = [];

        if ($deletedCount === 0) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Không có hợp đồng nào được xóa.']);
            return;
        }

        $message = "Đã xóa {$deletedCount} hợp đồng.";
        if ($skippedCount > 0) {
            $message .= " Bỏ qua {$skippedCount} hợp đồng không thuộc quyền.";
        }

        $this->resetPage();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $message]);
    }

    // ── View / detail / workflow ──────────────────────────────────────────────

    #[Computed]
    public function canBulkDelete(): bool
    {
        return auth()->user()->can($this->getPermDelete()->value);
    }

    #[Computed]
    public function isRestrictedRole(): bool
    {
        return auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
    }

    public function updateInlineReportNumber(int $docId, ?string $value): void
    {
        if (!auth()->user()->hasRole(Role::KY_THUAT->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Chỉ nhân viên Kỹ thuật mới được cập nhật Báo cáo số.']);
            return;
        }

        $modelClass = $this->getModelClass();
        $modelClass::findOrFail($docId)->update(['report_number' => $value ?: null]);

        if ($this->selectedDoc && $this->selectedDoc->id === $docId) {
            $this->selectedDoc->report_number = $value ?: null;
        }

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật Báo cáo số!']);
    }

    public function viewDetail(int $id): void
    {
        $modelClass        = $this->getModelClass();
        $this->selectedDoc = $modelClass::with(['customer', 'staff', 'department', 'assignments.user', 'assignments.assigner', 'handler'])->find($id);
        if ($this->selectedDoc) {
            $this->progressNotes = ContractProgressNote::where('contract_type', $this->getContractType())
                ->where('contract_id', $id)
                ->with('user')
                ->latest()
                ->get();
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    #[Computed]
    public function canAssign(): bool
    {
        return auth()->user()->hasAnyRole([
            Role::GIAM_DOC->value,
            Role::QUAN_LY->value,
            Role::TP_KINH_DOANH->value,
            Role::KINH_DOANH->value,
            Role::IT->value,
        ]);
    }

    public function openAssign(int $id): void
    {
        if (!$this->canAssign()) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền giao việc.']);
            return;
        }

        $modelClass             = $this->getModelClass();
        $this->assignContractId = $id;
        $existing               = ContractAssignment::where('assignable_type', $modelClass)
            ->where('assignable_id', $id)
            ->get();
        $this->assignUserIds  = $existing->whereNotNull('user_id')->pluck('user_id')->toArray();
        $this->assignExternal = $existing->whereNull('user_id')->first()?->external_assignee;
        $this->assignDeadline = $existing->first()?->deadline?->format('Y-m-d');
        $this->dispatch('openAssignModal');
    }

    public function saveAssign(): void
    {
        if (!$this->canAssign()) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền giao việc.']);
            return;
        }

        $modelClass    = $this->getModelClass();
        $contractType  = $this->getContractType();

        ContractAssignment::where('assignable_type', $modelClass)
            ->where('assignable_id', $this->assignContractId)
            ->delete();

        foreach ($this->assignUserIds as $userId) {
            ContractAssignment::create([
                'assignable_type' => $modelClass,
                'assignable_id'   => $this->assignContractId,
                'user_id'         => (int) $userId,
                'assigned_by'     => auth()->id(),
                'deadline'        => $this->assignDeadline ?: null,
            ]);
        }

        if (!empty($this->assignExternal)) {
            ContractAssignment::create([
                'assignable_type'   => $modelClass,
                'assignable_id'     => $this->assignContractId,
                'user_id'           => null,
                'external_assignee' => $this->assignExternal,
                'assigned_by'       => auth()->id(),
                'deadline'          => $this->assignDeadline ?: null,
            ]);
        }

        $contract      = $modelClass::with('customer')->find($this->assignContractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #' . $this->assignContractId);

        foreach ($this->assignUserIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                $user->notify(new ContractAssignedNotification($contractType, $this->assignContractId, $contractLabel, auth()->user()->name));
            }
        }

        $this->assignContractId = null;
        $this->assignUserIds    = [];
        $this->assignExternal   = null;
        $this->assignDeadline   = null;
        $this->dispatch('closeAssignModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Giao việc thành công!']);
    }

    public function addProgressNote(int $contractId): void
    {
        $this->validate(['progressNote' => 'required|min:1|max:2000']);
        $modelClass   = $this->getModelClass();
        $contractType = $this->getContractType();
        $noteText     = $this->progressNote;

        ContractProgressNote::create([
            'contract_type' => $contractType,
            'contract_id'   => $contractId,
            'user_id'       => auth()->id(),
            'note'          => $noteText,
        ]);

        $this->progressNote  = '';
        $this->progressNotes = ContractProgressNote::where('contract_type', $contractType)
            ->where('contract_id', $contractId)
            ->with('user')
            ->latest()
            ->get();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã thêm ghi chú!']);

        $contract      = $modelClass::with('customer')->find($contractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #' . $contractId);
        $recipients    = User::whereHas('roles', fn($q) => $q->whereIn('name', [
            Role::GIAM_DOC->value,
            Role::QUAN_LY->value,
            Role::TP_KINH_DOANH->value,
            Role::IT->value,
        ]))->get();

        $assignmentUserIds = ContractAssignment::where('assignable_type', $modelClass)
            ->where('assignable_id', $contractId)
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
            if ($staff) {
                $recipients->push($staff);
            }
        }

        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->id !== auth()->id()) {
                $recipient->notify(new ContractProgressNoteNotification(
                    $contractType,
                    $contractId,
                    $contractLabel,
                    Str::limit($noteText, 50),
                    auth()->user()->name
                ));
            }
        }
    }

    public function openWorkflow(int $id): void
    {
        $this->workflowContractId = $id;
        $this->dispatch('openWorkflowModal');
    }

    public function closeWorkflow(): void
    {
        $this->workflowContractId = null;
    }

    public function resetFilters(): void
    {
        $this->filter = [
            'signed_from'    => '',
            'signed_to'      => '',
            'submitted_from' => '',
            'submitted_to'   => '',
            'province'       => '',
            'department_id'  => '',
            'staff_id'       => '',
            'info_source'    => '',
            'payment_method' => '',
            'status'         => '',
            'renewal_status' => '',
            'voucher_status' => '',
            'is_offset'      => false,
            'has_room_fund'  => false,
            'is_overdue'     => false,
            'loai_dich_vu'   => '',
            'handler_id'     => '',
        ];
        $this->selectedDocIds = [];
        $this->sortDirection  = 'desc';
        $this->resetPage();
    }

    // ── Export ───────────────────────────────────────────────────────────────

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user             = auth()->user();
        $modelClass       = $this->getModelClass();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = $modelClass::with(['customer', 'staff', 'department', 'handler', 'workflowSteps'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', $user->id)));

        $this->applyFilters($query);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs           = $query->orderBy('id', $orderDirection)->get();
        $title          = $this->getExportTitle();
        $showFinancials = !$user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, $this->getExportFilenamePrefix() . '_' . now()->format('d_m_Y') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $user             = auth()->user();
        $modelClass       = $this->getModelClass();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = $modelClass::with(['customer', 'staff', 'department', 'assignments.user', 'handler', 'workflowSteps'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($isRestrictedSales, fn($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', $user->id)));

        $this->applyFilters($query);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs           = $query->orderBy('id', $orderDirection)->paginate(10);

        return view('livewire.admin.contracts.' . $this->getViewName(), [
            'docs'                   => $docs,
            'customers'              => Customer::orderBy('name')->get(),
            'staffs'                 => User::role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value])->orderBy('name')->get(),
            'departments'            => Department::all(),
            'assignable_users'       => User::whereHas('roles', fn($q) => $q->whereIn('name', [Role::TU_VAN->value, Role::KY_THUAT->value, Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]))->orderBy('name')->get(),
            'provinces'              => $modelClass::whereNotNull('province')->where('province', '!=', '')->distinct()->orderBy('province')->pluck('province')->toArray(),
            'all_statuses'           => self::ALLOWED_STATUSES,
            'renewal_statuses'       => $modelClass::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'voucher_status_options' => ContractVoucherStatus::values(),
            'loai_dich_vu_options'   => $modelClass::SERVICE_TYPES,
            'payment_methods'        => ['Sau ký', 'Trước ký'],
            'info_sources'           => $modelClass::whereNotNull('info_source')->where('info_source', '!=', '')->distinct()->pluck('info_source')->toArray(),
            'parentContracts'        => $modelClass::with('customer')->where('is_renewal', false)->orderByDesc('id')->get(),
            'handlers'               => Handler::orderBy('name')->get(),
        ])->layout('admin.layouts.app', ['title' => $this->getPageTitle()]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function applyFilters($query): void
    {
        $this->applyContractFilters($query);
    }

    private function resetForm(): void
    {
        $this->formData = [
            'shd_cxl'            => '',
            'shd_bc'             => '',
            'customer_id'        => '',
            'handler_id'         => '',
            'staff_id'           => auth()->id(),
            'department_id'      => 3,
            'signed_at'          => date('Y-m-d'),
            'submitted_at'       => '',
            'value'              => 0,
            'commission'         => 0,
            'revenue'            => 0,
            'province'           => '',
            'info_source'        => 'MỚI',
            'payment_method'     => 'Sau ký',
            'loai_dich_vu'       => '',
            'status'             => 'PTH đang kiểm tra',
            'renewal_status'     => '',
            'voucher_status'     => '',
            'is_offset'          => false,
            'has_room_fund'      => false,
            'is_overdue'         => false,
            'notes'              => '',
            'is_renewal'         => false,
            'parent_contract_id' => '',
        ];
        $this->isDuplicating         = false;
        $this->selectedDoc            = null;
        $this->createAssignUserIds    = [];
        $this->createAssignDeadline   = null;
        $this->createAssignExternal   = null;
    }
}
