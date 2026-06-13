<?php

namespace App\Livewire\Admin\Contracts;

use App\Actions\Contracts\UpsertContractWasteAction;
use App\Enums\ContractRenewalStatus;
use App\Enums\ContractVoucherStatus;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Handler;
use App\Models\User;
use App\Models\Department;
use App\Models\ContractAssignment;
use App\Models\ContractMilestoneFile;
use App\Models\ContractProgressNote;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Livewire\Concerns\ContractValidation;
use App\Livewire\Concerns\HasContractFilters;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;

class ContractWasteManager extends Component
{
    use WithPagination, WithFileUploads, CleanMoneyInput, ContractValidation, HasContractFilters;

    private const ALLOWED_STATUSES = [
        'Đã trình ký nhà thầu phụ',
        'Nhà thầu phụ đã gửi về',
        'Đã gửi khách hàng',
        'Đã hoàn thành KH ký trước',
        'Đã hoàn thành',
        'Hợp đồng hủy',
    ];

    protected $paginationTheme = 'bootstrap';

    public string $contractTypeName = 'Chất thải';

    public $search = '';
    public $sortDirection = 'desc';
    public array $selectedDocIds = [];

    public $showDetail = false;
    public $showModal = false;
    public $isEditing = false;
    public $isDuplicating = false;
    public $selectedDoc = null;
    public string $detailActiveTab = 'info';
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
    public array $newContractFiles = [];
    public $existingContractFiles = [];

    public $formData = [
        'shd_cxl' => '',
        'shd_bc' => '',
        'customer_id' => '',
        'handler_id' => '',
        'staff_id' => '',
        'department_id' => '',
        'content' => '',
        'value' => 0,
        'commission' => 0,
        'revenue' => 0,
        'ncc_payment' => 0,
        'ncc_payment_sheet_url' => '',
        'ncc_payment_status' => 'unpaid',
        'ncc_payment_paid_at' => '',
        'payment_method' => 'Sau ký',
        'source' => 'MỚI',
        'signed_at' => '',
        'effective_at' => '',
        'end_at' => '',
        'submitted_at' => '',
        'billing_address' => '',
        'execution_address' => '',
        'mailing_address' => '',
        'status' => '',
        'renewal_status' => '',
        'voucher_status' => '',
        'is_offset' => false,
        'is_overdue' => false,
        'note' => '',
        'loai_dich_vu' => '',
        'province' => '',
        'is_renewal' => false,
        'parent_contract_id' => '',
    ];
    public $filter = [
        'signed_from' => '',
        'signed_to' => '',
        'end_from' => '',
        'end_to' => '',
        'returned_from' => '',
        'returned_to' => '',
        'submitted_from' => '',
        'submitted_to' => '',
        'handler_id' => '',
        'staff_id' => '',
        'province' => '',
        'is_offset' => false,
        'is_overdue' => false,
        'department_id' => '',
        'source' => '',
        'payment_method' => '',
        'service_type' => '',
        'waste_type' => '',
        'loai_dich_vu' => '',
        'status' => '',
        'renewal_status' => '',
        'voucher_status' => '',
        'hide_completed_workflow' => false,
    ];

    protected $queryString = ['search', 'quotation_id'];
    public $quotation_id;

    public function paginationView()
    {
        return 'livewire.admin.contracts.pagination';
    }

    public function mount()
    {
        $this->filter['hide_completed_workflow'] = auth()->user()->hasAnyRole([
            Role::TU_VAN->value,
            Role::KY_THUAT->value,
        ]);

        if ($this->quotation_id) {
            abort_unless(auth()->user()->can(Permission::CONTRACTS_WASTE_CREATE->value), 403);

            $quotation = \App\Models\Quotation::find($this->quotation_id);
            if ($quotation) {
                // Find or create customer
                $customer = \App\Models\Customer::firstOrCreate(
                    ['name' => $quotation->company_name],
                    [
                        'address'        => $quotation->address,
                        'province'       => $quotation->province ?? '',
                        'representative' => $quotation->contact_person ?? '',
                    ]
                );

                $this->formData['customer_id'] = $customer->id;
                $this->formData['content'] = $quotation->work_description;
                $this->formData['value'] = $quotation->total_value;
                $this->formData['commission'] = $quotation->commission_value;
                $this->formData['revenue'] = $quotation->original_value;
                $this->formData['staff_id'] = $quotation->staff_id;
                $this->formData['billing_address'] = $quotation->address;
                $this->formData['province'] = $quotation->province ?? '';
                $this->formData['note'] = $quotation->notes;
                $this->formData['source'] = 'MỚI';
                $this->formData['status'] = self::ALLOWED_STATUSES[0];
                $this->ensureDepartmentId();

                $this->showModal = true;
                $this->dispatch('openFormModal');
            }
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFormDataValue(): void
    {
        if (!$this->isEditing) {
            $this->formData['revenue'] = $this->formData['value'];
        }
    }

    public function updatedSortDirection($value)
    {
        $this->sortDirection = $value === 'asc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isDuplicating = false;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function edit($id)
    {
        $user = auth()->user();
        if (!$user || !$user->can(Permission::CONTRACTS_WASTE_EDIT->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa hợp đồng này.']);
            return;
        }
        $doc = ContractWaste::with(['assignments.user'])->findOrFail($id);
        $this->selectedDoc = $doc;
        $this->formData = $doc->toArray();
        // Format dates for input
        $this->formData['signed_at'] = $doc->signed_at ? $doc->signed_at->format('Y-m-d') : '';
        $this->formData['effective_at'] = $doc->effective_at ? $doc->effective_at->format('Y-m-d') : '';
        $this->formData['end_at'] = $doc->end_at ? $doc->end_at->format('Y-m-d') : '';
        $this->formData['submitted_at'] = $doc->submitted_at ? $doc->submitted_at->format('Y-m-d') : '';
        $this->normalizeContractEnumFields();

        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function duplicate($id): void
    {
        $user = auth()->user();
        if (!$user || !$user->can(Permission::CONTRACTS_WASTE_CREATE->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
            return;
        }
        $doc = ContractWaste::findOrFail($id);
        $this->resetForm();
        $this->formData = $doc->toArray();
        $this->formData['signed_at'] = $doc->signed_at ? $doc->signed_at->format('Y-m-d') : date('Y-m-d');
        $this->formData['effective_at'] = $doc->effective_at ? $doc->effective_at->format('Y-m-d') : '';
        $this->formData['end_at'] = $doc->end_at ? $doc->end_at->format('Y-m-d') : '';
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

    public function save()
    {
        $user = auth()->user();

        if (!$user->can($this->isEditing ? Permission::CONTRACTS_WASTE_EDIT->value : Permission::CONTRACTS_WASTE_CREATE->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu hợp đồng này.']);
            return;
        }

        $isRestrictedTpKd = false; // TPKD has permission to edit contracts of all staff
        if ($this->isEditing && $isRestrictedTpKd && $this->selectedDoc->staff_id !== $user->id) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật hợp đồng do bạn phụ trách.']);
            return;
        }

        if (!$user->hasAnyRole([Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value])) {
            $this->formData['staff_id'] = ($this->isEditing && $this->selectedDoc)
                ? ($this->selectedDoc->staff_id ?: $user->id)
                : $user->id;
        }

        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue', 'ncc_payment']);
        $this->ensureDepartmentId();
        $this->normalizeContractEnumFields();

        try {
            $this->validate($this->wasteContractRules(), $this->contractValidationMessages());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            if ($firstError) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => $firstError]);
            }
            throw $e;
        }

        $data = collect($this->formData)->map(function ($value) {
            return $value === '' ? null : $value;
        })->toArray();

        [$newContract, $msg] = app(UpsertContractWasteAction::class)->execute($data, $user, $this->isEditing ? $this->selectedDoc : null);

        if (!$this->isEditing && (count($this->createAssignUserIds) > 0 || !empty($this->createAssignExternal))) {
            $contractLabel = $newContract->shd_bc ?: ($newContract->customer?->name ?: 'HĐ #' . $newContract->id);
            foreach ($this->createAssignUserIds as $userId) {
                ContractAssignment::create([
                    'assignable_type' => ContractWaste::class,
                    'assignable_id'   => $newContract->id,
                    'user_id'         => (int) $userId,
                    'assigned_by'     => auth()->id(),
                    'deadline'        => $this->createAssignDeadline ?: null,
                ]);
                $assignedUser = User::find($userId);
                if ($assignedUser && $assignedUser->id !== auth()->id()) {
                    $assignedUser->notify(new ContractAssignedNotification('waste', $newContract->id, $contractLabel, auth()->user()->name));
                }
            }
            if (!empty($this->createAssignExternal)) {
                ContractAssignment::create([
                    'assignable_type'   => ContractWaste::class,
                    'assignable_id'     => $newContract->id,
                    'user_id'           => null,
                    'external_assignee' => $this->createAssignExternal,
                    'assigned_by'       => auth()->id(),
                    'deadline'          => $this->createAssignDeadline ?: null,
                ]);
            }
        }

        $this->showModal = false;
        $this->dispatch('closeFormModal');
        $this->dispatch('swal:toast', ['message' => $msg, 'type' => 'success']);
        $this->resetForm();
    }

    public function updateStatus(int $id, string $status): void
    {
        $doc = ContractWaste::findOrFail($id);
        $user = auth()->user();
        if (!$user) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Phiên đăng nhập hết hạn, vui lòng tải lại trang.']);
            return;
        }
        $isRestrictedTpKd = false; // TPKD has permission to edit contracts of all staff
        if ($isRestrictedTpKd) {
            if ((int) $doc->staff_id !== (int) $user->id) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền cập nhật trạng thái hợp đồng này.']);
                return;
            }
        } else {
            if ($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value])) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền cập nhật trạng thái hợp đồng.']);
                return;
            }
        }
        if (!$user->can(Permission::CONTRACTS_WASTE_EDIT->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa hợp đồng.']);
            return;
        }

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Trạng thái không hợp lệ!']);
            return;
        }

        $updateData = ['status' => $status];
        if (in_array($status, ['Đã hoàn thành', 'Đã hoàn thành KH ký trước'], true)) {
            $updateData['submitted_at'] = now()->toDateString();
        }

        ContractWaste::findOrFail($id)->update($updateData);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tình trạng!']);
    }

    public function delete($id)
    {
        $doc = ContractWaste::findOrFail($id);
        $user = auth()->user();
        if (!$user) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Phiên đăng nhập hết hạn, vui lòng tải lại trang.']);
            return;
        }
        $isRestrictedTpKd = false; // TPKD has permission to edit contracts of all staff
        if ($isRestrictedTpKd) {
            if ((int) $doc->staff_id !== (int) $user->id) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền xóa hợp đồng này.']);
                return;
            }
        }
        if (!$user->can(Permission::CONTRACTS_WASTE_DELETE->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền xóa hợp đồng.']);
            return;
        }

        $doc->delete();
        $this->dispatch('swal:toast', ['message' => 'Đã xóa hợp đồng', 'type' => 'success']);
    }

    public function bulkDeleteSelected()
    {
        $user = auth()->user();
        if (!$user || !$user->can(Permission::CONTRACTS_WASTE_DELETE->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền xóa hợp đồng.']);
            return;
        }

        $selectedIds = collect($this->selectedDocIds)
            ->map(static fn($id) => (int) $id)
            ->filter(static fn($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Vui lòng chọn ít nhất 1 hợp đồng để xóa.']);
            return;
        }

        $isRestrictedTpKd = false; // TPKD has permission to edit contracts of all staff
        $deletedCount = 0;
        $skippedCount = 0;

        $docs = ContractWaste::whereIn('id', $selectedIds)->get();
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

    private function resetForm()
    {
        $this->formData = [
            'shd_cxl' => '',
            'shd_bc' => '',
            'customer_id' => '',
            'handler_id' => '',
            'staff_id' => auth()->id(),
            'department_id' => 3, // Phòng Kinh doanh
            'content' => '',
            'value' => 0,
            'commission' => 0,
            'revenue' => 0,
            'ncc_payment' => 0,
            'ncc_payment_sheet_url' => '',
            'ncc_payment_status' => 'unpaid',
            'ncc_payment_paid_at' => '',
            'payment_method' => 'Sau ký',
            'source' => 'MỚI',
            'signed_at' => date('Y-m-d'),
            'effective_at' => '',
            'end_at' => '',
            'submitted_at' => '',
            'billing_address' => '',
            'execution_address' => '',
            'mailing_address' => '',
            'status' => self::ALLOWED_STATUSES[0],
            'renewal_status' => 'CHƯA ĐẾN HẠN',
            'voucher_status' => '',
            'is_offset' => false,
            'is_overdue' => false,
            'note' => '',
            'loai_dich_vu' => '',
            'is_renewal' => false,
            'parent_contract_id' => '',
        ];
        $this->selectedDoc          = null;
        $this->createAssignUserIds  = [];
        $this->createAssignDeadline = null;
        $this->createAssignExternal = null;
    }

    #[Computed]
    public function canAssign(): bool
    {
        return auth()->user()->hasAnyRole([
            Role::GIAM_DOC->value,
            Role::TP_KINH_DOANH->value,
            Role::KINH_DOANH->value,
            Role::IT->value,
        ]);
    }

    #[Computed]
    public function canManageContractFiles(): bool
    {
        return auth()->user()->hasRole(Role::KE_TOAN->value);
    }

    #[Computed]
    public function canBulkDelete(): bool
    {
        return auth()->user()->can(Permission::CONTRACTS_WASTE_DELETE->value);
    }

    #[Computed]
    public function isRestrictedRole(): bool
    {
        return auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
    }

    public function canManageOwnedDoc($doc): bool
    {
        return true;
    }

    public function canUpdateStatusForDoc($doc): bool
    {
        $currentUser = auth()->user();

        return !$currentUser->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
    }

    public function workflowProgressMeta($doc): array
    {
        $completedSteps = $doc->workflowSteps->pluck('step_name')->unique()->count();
        $totalSteps = 6;
        $progressPercent = $totalSteps > 0 ? (int) round(($completedSteps / $totalSteps) * 100) : 0;
        $progressColor = $progressPercent >= 100 ? 'success' : ($progressPercent >= 50 ? 'primary' : 'warning');

        return [
            'completedSteps' => $completedSteps,
            'totalSteps' => $totalSteps,
            'progressPercent' => $progressPercent,
            'progressColor' => $progressColor,
        ];
    }

    public function deadlineMeta($doc): array
    {
        $deadline = $doc->assignments->first()?->deadline;
        $isFinished = in_array($doc->status ?? '', ['Đã hoàn thành', 'Hợp đồng hủy', 'HOÀN THÀNH'], true);
        $isOverdue = $deadline && $deadline->isPast() && !$isFinished;
        $daysLeft = $deadline ? (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false) : null;

        return [
            'deadline' => $deadline,
            'daysLeft' => $daysLeft,
            'isFinished' => $isFinished,
            'isOverdue' => $isOverdue,
            'isNearDue' => $deadline && !$isOverdue && !$isFinished && $daysLeft !== null && $daysLeft <= 3,
        ];
    }

    public function voucherBadgeInfoForDoc($doc): array
    {
        $voucherStatusValue = trim((string) ($doc->voucher_status ?? ''));
        $voucherStatusKey = mb_strtolower($voucherStatusValue);
        $voucherBadgeClass = match ($voucherStatusKey) {
            'đã đề nghị thanh toán/tạm ứng' => 'bg-info text-dark',
            'đã xuất hóa đơn' => 'bg-warning text-dark',
            'đã làm biên bản bàn giao hồ sơ' => 'bg-primary text-white',
            'đã làm bb bàn giao và nghiệm thu kết thúc hợp đồng' => 'bg-success text-white',
            '', 'chưa có', 'chưa chọn' => 'bg-light text-dark border',
            default => 'bg-secondary text-white',
        };

        $voucherBadgeLabel = match ($voucherStatusKey) {
            'đã đề nghị thanh toán/tạm ứng' => 'Đề nghị TT/TƯ',
            'đã xuất hóa đơn' => 'Xuất hóa đơn',
            'đã làm biên bản bàn giao hồ sơ' => 'BB bàn giao hồ sơ',
            'đã làm bb bàn giao và nghiệm thu kết thúc hợp đồng' => 'BB nghiệm thu kết thúc HĐ',
            '', 'chưa có', 'chưa chọn' => 'Chưa chọn',
            default => $voucherStatusValue !== '' ? $voucherStatusValue : 'Chưa chọn',
        };

        return [
            'class' => $voucherBadgeClass,
            'label' => $voucherBadgeLabel,
            'full_value' => $voucherStatusValue !== '' ? $voucherStatusValue : 'Chưa chọn',
        ];
    }

    public function renewalBadgeClassForDoc($doc): string
    {
        $renewalStatusKey = mb_strtolower(trim((string) ($doc->renewal_status ?? '')));

        return match ($renewalStatusKey) {
            'đã tái ký' => 'bg-success text-white',
            'chưa tái ký' => 'bg-danger text-white',
            'chưa đến hạn' => 'bg-warning text-dark',
            '', 'chưa chọn' => 'bg-light text-dark border',
            default => 'bg-secondary text-white',
        };
    }

    public function wasteStatusColorForDoc($doc): array
    {
        $statusKey = mb_strtolower(trim((string) ($doc->status ?? '')));

        return match ($statusKey) {
            'hoàn thành', 'đã hoàn thành', 'đã hoàn thành kh ký trước' => ['bg' => '#d1e7dd', 'text' => '#198754'],
            'đã hủy', 'hợp đồng hủy', 'hủy bỏ' => ['bg' => '#f8d7da', 'text' => '#dc3545'],
            'đã trình ký nhà thầu phụ' => ['bg' => '#fff3cd', 'text' => '#b45309'],
            'nhà thầu phụ đã gửi về' => ['bg' => '#d1ecf1', 'text' => '#0c5460'],
            'đã gửi khách hàng' => ['bg' => '#e2d9f3', 'text' => '#6f42c1'],
            'đang thực hiện', '' => ['bg' => '#cfe2ff', 'text' => '#0d6efd'],
            default => ['bg' => '#e9ecef', 'text' => '#495057'],
        };
    }

    public function roleDisplayFromSlug(string $roleSlug): string
    {
        return match ($roleSlug) {
            'it' => 'IT Admin',
            'giam-doc' => 'Giám đốc',
            'tp-kinh-doanh' => 'Trưởng phòng KD',
            'kinh-doanh' => 'Nhân viên KD',
            'ke-toan' => 'Kế toán',
            'tu-van' => 'Tư vấn',
            'ky-thuat' => 'Kỹ thuật',
            'marketing' => 'Marketing',
            default => $roleSlug,
        };
    }

    public function openAssign(int $id): void
    {
        if (!$this->canAssign()) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền giao việc.']);
            return;
        }
        $this->assignContractId = $id;
        $existing = ContractAssignment::where('assignable_type', ContractWaste::class)
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
        ContractAssignment::where('assignable_type', ContractWaste::class)
            ->where('assignable_id', $this->assignContractId)
            ->delete();
        foreach ($this->assignUserIds as $userId) {
            ContractAssignment::create([
                'assignable_type' => ContractWaste::class,
                'assignable_id'   => $this->assignContractId,
                'user_id'         => (int) $userId,
                'assigned_by'     => auth()->id(),
                'deadline'        => $this->assignDeadline ?: null,
            ]);
        }
        if (!empty($this->assignExternal)) {
            ContractAssignment::create([
                'assignable_type'   => ContractWaste::class,
                'assignable_id'     => $this->assignContractId,
                'user_id'           => null,
                'external_assignee' => $this->assignExternal,
                'assigned_by'       => auth()->id(),
                'deadline'          => $this->assignDeadline ?: null,
            ]);
        }
        // Gửi thông báo đến users được giao
        $contract = ContractWaste::with('customer')->find($this->assignContractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$this->assignContractId);
        foreach ($this->assignUserIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                $user->notify(new ContractAssignedNotification('waste', $this->assignContractId, $contractLabel, auth()->user()->name));
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
            'contract_type' => 'waste',
            'contract_id'   => $contractId,
            'user_id'       => auth()->id(),
            'note'          => $noteText,
        ]);
        $this->progressNote = '';
        $this->progressNotes = ContractProgressNote::where('contract_type', 'waste')
            ->where('contract_id', $contractId)
            ->with('user')
            ->latest()
            ->get();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã thêm ghi chú!']);

        // Gửi thông báo đến quản lý + NV kinh doanh phụ trách
        $contract = ContractWaste::with('customer')->find($contractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$contractId);
        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', [Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]))->get();

        $assignmentUserIds = ContractAssignment::where('assignable_type', ContractWaste::class)
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
            if ($staff) $recipients->push($staff);
        }
        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->id !== auth()->id()) {
                $recipient->notify(new ContractProgressNoteNotification('waste', $contractId, $contractLabel, \Illuminate\Support\Str::limit($noteText, 50), auth()->user()->name));
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

    public function resetFilters()
    {
        $this->filter = [
            'signed_from' => '',
            'signed_to' => '',
            'end_from' => '',
            'end_to' => '',
            'returned_from' => '',
            'returned_to' => '',
            'submitted_from' => '',
            'submitted_to' => '',
            'handler_id' => '',
            'staff_id' => '',
            'province' => '',
            'is_offset' => false,
            'is_overdue' => false,
            'department_id' => '',
            'source' => '',
            'payment_method' => '',
            'service_type' => '',
            'waste_type' => '',
            'loai_dich_vu' => '',
            'status' => '',
            'renewal_status' => '',
            'voucher_status' => '',
            'hide_completed_workflow' => auth()->user()->hasAnyRole([
                Role::TU_VAN->value,
                Role::KY_THUAT->value,
            ]),
        ];
        $this->selectedDocIds = [];
        $this->sortDirection = 'desc';
    }

    public function viewDetail($id)
    {
        $this->detailActiveTab = 'info';
        $this->selectedDoc = ContractWaste::with(['customer', 'handler', 'staff', 'department', 'assignments.user', 'assignments.assigner'])->find($id);
        if ($this->selectedDoc) {
            $this->progressNotes = ContractProgressNote::where('contract_type', 'waste')
                ->where('contract_id', $id)
                ->with('user')
                ->latest()
                ->get();
            $this->existingContractFiles = ContractMilestoneFile::where('contract_type', ContractWaste::class)
                ->where('contract_id', $id)
                ->where('milestone', 'contract_document')
                ->with('uploader')
                ->latest()
                ->get();
            $this->newContractFiles = [];
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    public function viewDetailDocs($id): void
    {
        $this->viewDetail($id);
        $this->detailActiveTab = 'docs';
        $this->dispatch('openDetailModal');
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->selectedDoc = null;
    }

    public function uploadContractFile(): void
    {
        $this->validate([
            'newContractFiles'   => 'required|array|max:10',
            'newContractFiles.*' => 'file|max:51200|mimes:pdf',
        ], [
            'newContractFiles.required' => 'Vui lòng chọn ít nhất 1 file.',
            'newContractFiles.*.max'    => 'File PDF không được vượt quá 50MB.',
            'newContractFiles.*.mimes'  => 'Chỉ chấp nhận file PDF.',
        ]);

        $disk = config('filesystems.upload_disk', 'public');

        foreach ($this->newContractFiles as $file) {
            $path = $file->storePublicly('contract-files/waste/contract_document', $disk);
            ContractMilestoneFile::create([
                'contract_type' => ContractWaste::class,
                'contract_id'   => $this->selectedDoc->id,
                'milestone'     => 'contract_document',
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploader_id'   => auth()->id(),
            ]);
        }

        $this->newContractFiles = [];
        $this->existingContractFiles = ContractMilestoneFile::where('contract_type', ContractWaste::class)
            ->where('contract_id', $this->selectedDoc->id)
            ->where('milestone', 'contract_document')
            ->with('uploader')
            ->latest()
            ->get();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã lưu file PDF.']);
    }

    public function deleteContractFile(int $fileId): void
    {
        $file = ContractMilestoneFile::findOrFail($fileId);
        $disk = config('filesystems.upload_disk', 'public');

        if (Storage::disk($disk)->exists($file->file_path)) {
            Storage::disk($disk)->delete($file->file_path);
        }

        $file->delete();

        $this->existingContractFiles = ContractMilestoneFile::where('contract_type', ContractWaste::class)
            ->where('contract_id', $this->selectedDoc->id)
            ->where('milestone', 'contract_document')
            ->with('uploader')
            ->latest()
            ->get();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa file.']);
    }

    private function applyFilters($query): void
    {
        $this->applyContractFilters($query);

        $f = $this->filter;
        if ($f['end_from'] ?? null)     $query->whereDate('end_at', '>=', $f['end_from']);
        if ($f['end_to'] ?? null)       $query->whereDate('end_at', '<=', $f['end_to']);
        if ($f['service_type'] ?? null) $query->where('service_type', $f['service_type']);
        if ($f['waste_type'] ?? null)   $query->where('waste_type', $f['waste_type']);
        if ($f['source'] ?? null)       $query->where('source', $f['source']);
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = ContractWaste::with(['customer', 'handler', 'staff', 'department'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_cxl', 'like', '%' . $this->search . '%')
                        ->orWhere('shd_bc', 'like', '%' . $this->search . '%')
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
        $docs           = $query->orderBy('id', $orderDirection)->get();
        $title          = 'Hợp đồng chất thải';
        $showFinancials = !auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, 'HopDong_ChatThai_' . now()->format('d_m_Y') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && !$user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = ContractWaste::with(['customer', 'handler', 'staff', 'department', 'assignments.user', 'workflowSteps'])
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('shd_cxl', 'like', '%'.$this->search.'%')
                      ->orWhere('shd_bc', 'like', '%'.$this->search.'%')
                      ->orWhereHas('customer', function($csq) {
                          $csq->where('name', 'like', '%'.$this->search.'%');
                      });
                });
            })
            ->when($isRestrictedSales,
                fn($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', $user->id)));

        // Apply filters
        $this->applyFilters($query);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs = $query->orderBy('id', $orderDirection)->paginate(10);
        $voucherStatuses = collect(ContractVoucherStatus::values())
            ->merge(
                ContractWaste::whereNotNull('voucher_status')
                    ->where('voucher_status', '!=', '')
                    ->distinct()
                    ->pluck('voucher_status')
                    ->toArray()
            )
            ->unique()
            ->values()
            ->toArray();

        $isRestrictedUser = $isRestrictedSales || $user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
        $baseUserQuery = ContractWaste::query()
            ->when($isRestrictedSales, fn($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', $user->id)));

        $loaiDichVuOptions = ContractWaste::SERVICE_TYPES;

        $scopedProvinces = $isRestrictedUser
            ? (clone $baseUserQuery)->whereNotNull('province')->where('province', '!=', '')->distinct()->orderBy('province')->pluck('province')->toArray()
            : ContractWaste::whereNotNull('province')->where('province', '!=', '')->distinct()->orderBy('province')->pluck('province')->toArray();

        $scopedServiceTypes = $isRestrictedUser
            ? (clone $baseUserQuery)->whereNotNull('service_type')->where('service_type', '!=', '')->distinct()->pluck('service_type')->toArray()
            : ContractWaste::whereNotNull('service_type')->where('service_type', '!=', '')->distinct()->pluck('service_type')->toArray();

        $scopedWasteTypes = $isRestrictedUser
            ? (clone $baseUserQuery)->whereNotNull('waste_type')->where('waste_type', '!=', '')->distinct()->pluck('waste_type')->toArray()
            : ContractWaste::whereNotNull('waste_type')->where('waste_type', '!=', '')->distinct()->pluck('waste_type')->toArray();

        return view('livewire.admin.contracts.contract-waste-manager', [
            'docs' => $docs,
            'handlers' => Handler::orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'staffs' => User::role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value])->where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::all(),
            'assignable_users' => \App\Models\User::where('is_active', true)->whereHas('roles', fn($q) =>
                $q->whereIn('name', [Role::TU_VAN->value, Role::KY_THUAT->value, Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]))->orderBy('name')->get(),
            // Dynamic filter options
            'service_types' => $scopedServiceTypes,
            'waste_types' => $scopedWasteTypes,
            'loai_dich_vu_options' => $loaiDichVuOptions,
            'all_statuses' => self::ALLOWED_STATUSES,
            'renewal_statuses' => ContractWaste::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'renewal_status_options' => ContractRenewalStatus::map(),
            'voucher_statuses' => $voucherStatuses,
            'voucher_status_options' => ContractVoucherStatus::values(),
            'payment_methods' => ['Sau ký', 'Trước ký'],
            'provinces' => $scopedProvinces,
            'source_options' => ContractWaste::whereNotNull('source')->where('source', '!=', '')->distinct()->pluck('source')->toArray(),
            'parentContracts' => ContractWaste::with('customer')->where('is_renewal', false)->orderByDesc('id')->get(),
        ])->layout('admin.layouts.app', ['title' => 'Chất thải']);
    }
}
