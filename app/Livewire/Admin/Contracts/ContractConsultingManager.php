<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\ContractRenewalStatus;
use App\Enums\ContractVoucherStatus;
use App\Enums\Role;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Livewire\Concerns\ContractValidation;
use App\Livewire\Concerns\HasContractFilters;
use App\Models\ContractAssignment;
use App\Models\ContractLegal;
use App\Models\ContractProgressNote;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\Customer;
use App\Models\Handler;
use App\Models\Department;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;
use App\Support\VietnamProvinces;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Enums\Permission;

class ContractConsultingManager extends Component
{
    use CleanMoneyInput, ContractValidation, WithPagination, HasContractFilters;

    private const ALLOWED_STATUSES = [
        'PTH đang kiểm tra',
        'Đang trình BGĐ ký',
        'Đã gửi khách hàng',
        'Đã hoàn thành',
        'Hợp đồng hủy',
    ];

    protected $paginationTheme = 'bootstrap';

    public string $contractTypeName = 'Hồ Sơ Môi Trường';

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

    public ?string $assignExternal = null;

    public array $createAssignUserIds = [];

    public ?string $createAssignDeadline = null;

    public ?string $createAssignExternal = null;

    public string $progressNote = '';

    public $progressNotes = [];

    public ?int $workflowContractId = null;

    public string $reportNumber = '';

    public ?int $quotation_id = null;

    public $formData = [
        'shd_cxl' => '',
        'shd_bc' => '',
        'customer_id' => '',
        'handler_id'     => '',
        'staff_id' => '',
        'department_id' => '',
        'signed_at' => '',
        'submitted_at' => '',
        'value' => 0,
        'commission' => 0,
        'revenue' => 0,
        'ncc_payment' => 0,
        'province' => '',
        'info_source' => 'MỚI',
        'payment_method' => 'Sau ký',
        'loai_dich_vu' => '',
        'status' => 'PTH đang kiểm tra',
        'renewal_status' => '',
        'voucher_status' => '',
        'is_offset' => false,
        'has_room_fund' => false,
        'is_overdue' => false,
        'notes' => '',
        'is_renewal' => false,
        'parent_contract_id' => '',
    ];

    public $filter = [
        'signed_from' => '',
        'signed_to' => '',
        'submitted_from' => '',
        'submitted_to' => '',
        'province' => '',
        'department_id' => '',
        'staff_id' => '',
        'info_source' => '',
        'payment_method' => '',
        'status' => '',
        'renewal_status' => '',
        'voucher_status' => '',
        'is_offset' => false,
        'has_room_fund' => false,
        'is_overdue' => false,
        'loai_dich_vu' => '',
        'handler_id'  => '',
    ];

    protected $queryString = ['search', 'quotation_id'];

    public function paginationView()
    {
        return 'livewire.admin.users.pagination';
    }

    public function mount(): void
    {
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
                $this->formData['value'] = $quotation->total_value ?? 0;
                $this->formData['commission'] = $quotation->commission_value ?? 0;
                $this->formData['revenue'] = $quotation->original_value ?? 0;
                $this->formData['staff_id'] = $quotation->staff_id ?? auth()->id();
                $this->formData['province'] = $quotation->province ?? '';
                $this->formData['notes'] = $quotation->notes ?? '';
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
        if (! $this->isEditing) {
            $this->formData['revenue'] = $this->formData['value'];
        }
    }

    public function updatedSortDirection($value): void
    {
        $this->sortDirection = $value === 'asc' ? 'asc' : 'desc';
        $this->resetPage();
    }

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
        abort_unless(auth()->user()->can(Permission::CONTRACTS_CONSULTING_EDIT->value), 403);
        $this->selectedDoc = ContractLegal::with(['assignments.user'])->findOrFail($id);
        $this->formData = $this->selectedDoc->toArray();
        if ($this->selectedDoc->signed_at) {
            $this->formData['signed_at'] = $this->selectedDoc->signed_at->format('Y-m-d');
        }
        if ($this->selectedDoc->submitted_at) {
            $this->formData['submitted_at'] = $this->selectedDoc->submitted_at->format('Y-m-d');
        }
        $this->normalizeContractEnumFields();
        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function save(): void
    {
        $user = auth()->user();

        if (! $user->can($this->isEditing ? Permission::CONTRACTS_CONSULTING_EDIT->value : Permission::CONTRACTS_CONSULTING_CREATE->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu hợp đồng này.']);

            return;
        }

        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);
        if ($this->isEditing && $isRestrictedTpKd && $this->selectedDoc->staff_id !== $user->id) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật hợp đồng do bạn phụ trách.']);

            return;
        }

        // Staff field is hidden for some roles, so keep a stable value before validation.
        if (! $user->hasAnyRole([Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value])) {
            $this->formData['staff_id'] = ($this->isEditing && $this->selectedDoc)
                ? ($this->selectedDoc->staff_id ?: $user->id)
                : $user->id;
        }

        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue', 'ncc_payment']);
        $this->ensureDepartmentId();
        $this->normalizeContractEnumFields();

        try {
            $this->validate($this->baseContractRules(), $this->contractValidationMessages());
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            if ($firstError) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => $firstError]);
            }
            throw $e;
        }

        $data = collect($this->formData)->map(fn ($v) => $v === '' ? null : $v)->toArray();

        $isAccountant = $user->hasRole(Role::KE_TOAN->value);
        if (! $this->isEditing) {
            $data['shd_bc']      = null;
            $data['ncc_payment'] = 0;
        } elseif (! $isAccountant && $this->selectedDoc) {
            $data['shd_bc']      = $this->selectedDoc->shd_bc;
            $data['ncc_payment'] = $this->selectedDoc->ncc_payment;
        }

        if ($this->isEditing && $this->selectedDoc) {
            $this->selectedDoc->update($data);
        } else {
            $newContract = ContractLegal::create($data);

            if (count($this->createAssignUserIds) > 0 || !empty($this->createAssignExternal)) {
                $contractLabel = $newContract->shd_bc ?: ($newContract->customer?->name ?: 'HĐ #' . $newContract->id);
                foreach ($this->createAssignUserIds as $userId) {
                    ContractAssignment::create([
                        'assignable_type' => ContractLegal::class,
                        'assignable_id'   => $newContract->id,
                        'user_id'         => (int) $userId,
                        'assigned_by'     => auth()->id(),
                        'deadline'        => $this->createAssignDeadline ?: null,
                    ]);
                    $assignedUser = User::find($userId);
                    if ($assignedUser && $assignedUser->id !== auth()->id()) {
                        $assignedUser->notify(new ContractAssignedNotification('consulting', $newContract->id, $contractLabel, auth()->user()->name));
                    }
                }
                if (!empty($this->createAssignExternal)) {
                    ContractAssignment::create([
                        'assignable_type'   => ContractLegal::class,
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
        $doc = ContractLegal::findOrFail($id);
        $user = auth()->user();
        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);

        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        } else {
            abort_if($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]), 403);
        }
        abort_unless($user->can(Permission::CONTRACTS_CONSULTING_EDIT->value), 403);

        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            return;
        }

        $updateData = ['status' => $status];
        if ($status === 'Đã hoàn thành') {
            $updateData['submitted_at'] = now()->toDateString();
        }

        ContractLegal::findOrFail($id)->update($updateData);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tình trạng!']);
    }

    public function delete(int $id): void
    {
        $doc = ContractLegal::findOrFail($id);
        $user = auth()->user();
        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);

        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        }
        abort_unless($user->can(Permission::CONTRACTS_CONSULTING_DELETE->value), 403);

        $doc->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hợp đồng!']);
    }

    public function duplicate(int $id): void
    {
        abort_unless(auth()->user()->can(Permission::CONTRACTS_CONSULTING_CREATE->value), 403);
        $doc = ContractLegal::findOrFail($id);
        $this->resetForm();
        $this->formData = $doc->toArray();
        $this->formData['signed_at'] = $doc->signed_at ? $doc->signed_at->format('Y-m-d') : date('Y-m-d');
        $this->formData['submitted_at'] = '';
        $this->formData['shd_cxl'] = '';
        $this->formData['shd_bc'] = '';
        unset($this->formData['id'], $this->formData['created_at'], $this->formData['updated_at']);
        $this->normalizeContractEnumFields();
        $this->isEditing = false;
        $this->isDuplicating = true;
        $this->selectedDoc = null;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function bulkDeleteSelected(): void
    {
        $user = auth()->user();
        abort_unless($user->can(Permission::CONTRACTS_CONSULTING_DELETE->value), 403);

        $selectedIds = collect($this->selectedDocIds)
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Vui lòng chọn ít nhất 1 hợp đồng để xóa.']);

            return;
        }

        $isRestrictedTpKd = $user->hasRole(Role::TP_KINH_DOANH->value) && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value]);
        $deletedCount = 0;
        $skippedCount = 0;

        $docs = ContractLegal::whereIn('id', $selectedIds)->get();
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

    public function viewDetail(int $id): void
    {
        $this->selectedDoc = ContractLegal::with(['customer', 'staff', 'department', 'assignments.user', 'assignments.assigner', 'handler'])->find($id);
        if ($this->selectedDoc) {
            $this->reportNumber = $this->selectedDoc->report_number ?? '';
            $this->progressNotes = ContractProgressNote::where('contract_type', 'consulting')
                ->where('contract_id', $id)
                ->with('user')
                ->latest()
                ->get();
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    public function saveReportNumber(): void
    {
        if (!auth()->user()->hasRole(Role::KY_THUAT->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Chỉ nhân viên Kỹ thuật mới được cập nhật Báo cáo số.']);
            return;
        }

        if (!$this->selectedDoc) {
            return;
        }

        $this->selectedDoc->update(['report_number' => $this->reportNumber ?: null]);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật Báo cáo số!']);
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
        $this->assignContractId = $id;
        $existing = ContractAssignment::where('assignable_type', ContractLegal::class)
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
        ContractAssignment::where('assignable_type', ContractLegal::class)
            ->where('assignable_id', $this->assignContractId)
            ->delete();
        foreach ($this->assignUserIds as $userId) {
            ContractAssignment::create([
                'assignable_type' => ContractLegal::class,
                'assignable_id' => $this->assignContractId,
                'user_id' => (int) $userId,
                'assigned_by' => auth()->id(),
                'deadline'    => $this->assignDeadline ?: null,
            ]);
        }
        if (!empty($this->assignExternal)) {
            ContractAssignment::create([
                'assignable_type'   => ContractLegal::class,
                'assignable_id'     => $this->assignContractId,
                'user_id'           => null,
                'external_assignee' => $this->assignExternal,
                'assigned_by'       => auth()->id(),
                'deadline'          => $this->assignDeadline ?: null,
            ]);
        }
        // Gửi thông báo đến users được giao
        $contract = ContractLegal::with('customer')->find($this->assignContractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$this->assignContractId);
        foreach ($this->assignUserIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                $user->notify(new ContractAssignedNotification('consulting', $this->assignContractId, $contractLabel, auth()->user()->name));
            }
        }
        $this->assignContractId = null;
        $this->assignUserIds = [];
        $this->assignExternal = null;
        $this->assignDeadline = null;
        $this->dispatch('closeAssignModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Giao việc thành công!']);
    }

    public function addProgressNote(int $contractId): void
    {
        $this->validate(['progressNote' => 'required|min:1|max:2000']);
        $noteText = $this->progressNote;
        ContractProgressNote::create([
            'contract_type' => 'consulting',
            'contract_id' => $contractId,
            'user_id' => auth()->id(),
            'note' => $noteText,
        ]);
        $this->progressNote = '';
        $this->progressNotes = ContractProgressNote::where('contract_type', 'consulting')
            ->where('contract_id', $contractId)
            ->with('user')
            ->latest()
            ->get();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã thêm ghi chú!']);

        // Gửi thông báo đến quản lý + NV kinh doanh phụ trách
        $contract = ContractLegal::with('customer')->find($contractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$contractId);
        $recipients = User::whereHas('roles', fn ($q) => $q->whereIn('name', [Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]))->get();

        $assignmentUserIds = ContractAssignment::where('assignable_type', ContractLegal::class)
            ->where('assignable_id', $contractId)
            ->whereNotNull('user_id')
            ->get(['user_id', 'assigned_by'])
            ->flatMap(fn ($assignment) => [(int) $assignment->user_id, (int) $assignment->assigned_by])
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
                $recipient->notify(new ContractProgressNoteNotification('consulting', $contractId, $contractLabel, Str::limit($noteText, 50), auth()->user()->name));
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
            'signed_from' => '',
            'signed_to' => '',
            'submitted_from' => '',
            'submitted_to' => '',
            'province' => '',
            'department_id' => '',
            'staff_id' => '',
            'info_source' => '',
            'payment_method' => '',
            'status' => '',
            'renewal_status' => '',
            'voucher_status' => '',
            'is_offset' => false,
            'has_room_fund' => false,
            'is_overdue' => false,
            'loai_dich_vu' => '',
            'handler_id'  => '',
        ];
        $this->selectedDocIds = [];
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->formData = [
            'shd_cxl' => '',
            'shd_bc' => '',
            'customer_id' => '',
        'handler_id'     => '',
            'staff_id' => auth()->id(),
            'department_id' => 3, // Phòng Kinh doanh
            'signed_at' => date('Y-m-d'),
            'submitted_at' => '',
            'value' => 0,
            'commission' => 0,
            'revenue' => 0,
            'province' => '',
            'info_source' => 'MỚI',
            'payment_method' => 'Sau ký',
            'loai_dich_vu' => '',
            'status' => 'PTH đang kiểm tra',
            'renewal_status' => '',
            'voucher_status' => '',
            'is_offset' => false,
            'has_room_fund' => false,
            'is_overdue' => false,
            'notes' => '',
            'is_renewal' => false,
            'parent_contract_id' => '',
        ];
        $this->isDuplicating = false;
        $this->selectedDoc = null;
        $this->createAssignUserIds  = [];
        $this->createAssignDeadline = null;
        $this->createAssignExternal = null;
    }

    public function exportExcel(): StreamedResponse
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = ContractLegal::with(['customer', 'staff', 'department', 'handler'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn ($q) => $q->whereHas('assignments', fn ($sq) => $sq->where('user_id', $user->id)));

        $this->applyContractFilters($query);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs = $query->orderBy('id', $orderDirection)->get();
        $title = 'Hợp đồng tư vấn';
        $showFinancials = ! auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, 'HopDong_TuVan_'.now()->format('d_m_Y').'.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    private function getWorkflowProgress($items)
    {
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
        $stepKeys = $stepsData['stepKeys'];
        $stepLabels = $stepsData['steps'];
        $totalSteps = count($stepKeys);
        $modelClass = ContractLegal::class;

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

    #[Computed]
    public function canBulkDelete(): bool
    {
        return auth()->user()->can(Permission::CONTRACTS_CONSULTING_DELETE->value);
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

        ContractLegal::findOrFail($docId)->update(['report_number' => $value ?: null]);

        if ($this->selectedDoc && $this->selectedDoc->id === $docId) {
            $this->selectedDoc->report_number = $value ?: null;
        }

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật Báo cáo số!']);
    }

    public function render()
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::QUAN_LY->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = ContractLegal::with(['customer', 'staff', 'department', 'assignments.user', 'handler', 'workflowSteps'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($isRestrictedSales,
                fn ($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn ($q) => $q->whereHas('assignments', fn ($sq) => $sq->where('user_id', $user->id)));

        $this->applyContractFilters($query);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs = $query->orderBy('id', $orderDirection)->paginate(10);

        $workflowProgress = $this->getWorkflowProgress($docs);

        return view('livewire.admin.contracts.contract-consulting-manager', [
            'workflowProgress' => $workflowProgress,
            'docs' => $docs,
            'customers' => Customer::orderBy('name')->get(),
            'staffs' => User::role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value])->orderBy('name')->get(),
            'departments' => Department::all(),
            'assignable_users' => User::whereHas('roles', fn ($q) => $q->whereIn('name', [Role::TU_VAN->value, Role::KY_THUAT->value, Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]))->orderBy('name')->get(),
            'provinces' => $user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value])
                ? ContractLegal::whereHas('assignments', fn ($q) => $q->where('user_id', $user->id))
                    ->whereNotNull('province')->where('province', '!=', '')
                    ->distinct()->orderBy('province')->pluck('province')->toArray()
                : ContractLegal::whereNotNull('province')->where('province', '!=', '')
                    ->distinct()->orderBy('province')->pluck('province')->toArray(),
            'all_statuses' => self::ALLOWED_STATUSES,
            'renewal_statuses' => ContractLegal::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'renewal_status_options' => ContractRenewalStatus::map(),
            'voucher_status_options' => ContractVoucherStatus::values(),
            'loai_dich_vu_options' => ContractLegal::SERVICE_TYPES,
            'payment_methods' => ['Sau ký', 'Trước ký'],
            'info_sources' => ContractLegal::whereNotNull('info_source')->where('info_source', '!=', '')->distinct()->pluck('info_source')->toArray(),
            'parentContracts' => ContractLegal::with('customer')->where('is_renewal', false)->orderByDesc('id')->get(),
            'handlers' => Handler::orderBy('name')->get(),
        ])->layout('admin.layouts.app', ['title' => 'Hồ sơ môi trường']);
    }
}
