<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\ContractRenewalStatus;
use App\Enums\ContractVoucherStatus;
use App\Enums\Permission;
use App\Enums\Role;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Livewire\Concerns\ContractValidation;
use App\Livewire\Concerns\HasContractFilters;
use App\Models\ContractAssignment;
use App\Models\ContractMilestoneFile;
use App\Models\ContractProgressNote;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AbstractContractGenericManager extends Component
{
    use CleanMoneyInput, ContractValidation, HasContractFilters, WithFileUploads, WithPagination;

    private const ALLOWED_STATUSES = [
        'PTH đang kiểm tra',
        'Đang trình BGĐ ký',
        'Đã gửi khách hàng',
        'Đã hoàn thành',
        'Hợp đồng hủy',
    ];

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public string $sortBy = 'id';

    public $sortDirection = 'desc';

    public bool $showModal = false;

    public bool $isEditing = false;

    public bool $isDuplicating = false;

    public $showDetail = false;

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

    public ?int $quotation_id = null;

    public array $newContractFiles = [];

    public $existingContractFiles = [];

    public $formData = [
        'shd_cxl' => '',
        'shd_bc' => '',
        'customer_id' => '',
        'handler_id' => '',
        'staff_id' => '',
        'department_id' => '',
        'signed_at' => '',
        'submitted_at' => '',
        'value' => 0,
        'commission' => 0,
        'revenue' => 0,
        'payment_percentage' => 100,
        'service_content' => '',
        'submission_place' => '',
        'ncc_payment' => 0,
        'ncc_payment_sheet_url' => '',
        'ncc_payment_status' => 'unpaid',
        'ncc_payment_paid_at' => '',
        'province' => '',
        'info_source' => 'MỚI',
        'payment_method' => 'Sau khi ký HĐ',
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

    public string $newCustomerName = '';

    public array $financialBase = ['value' => 0, 'commission' => 0, 'revenue' => 0];

    public array $paymentMethods = ['Sau khi ký HĐ'];

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
        'handler_id' => '',
        'hide_completed_workflow' => false,
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
        return 'livewire.admin.contracts.pagination';
    }

    public function mount(): void
    {
        $this->contractTypeName = $this->getPageTitle();
        $this->filter['hide_completed_workflow'] = auth()->user()->hasAnyRole([
            Role::TU_VAN->value,
            Role::KY_THUAT->value,
        ]);

        if ($this->quotation_id) {
            $quotation = Quotation::find($this->quotation_id);
            if ($quotation) {
                $customer = Customer::firstOrCreate(
                    ['name' => $quotation->company_name ?? ''],
                    [
                        'address' => $quotation->address ?? '',
                        'province' => $quotation->province ?? '',
                        'representative' => $quotation->contact_person ?? '',
                    ]
                );
                $this->formData['customer_id'] = $customer->id;
                $this->formData['value'] = $quotation->total_value ?? 0;
                $this->formData['commission'] = $quotation->commission_value ?? 0;
                $this->formData['revenue'] = $quotation->original_value ?? 0;
                $this->formData['payment_percentage'] = 100;
                $this->formData['service_content'] = $quotation->service ?? '';
                $this->formData['staff_id'] = $quotation->staff_id ?? auth()->id();
                $this->formData['province'] = $quotation->province ?? '';
                $this->formData['notes'] = $quotation->notes ?? '';
                $this->formData['info_source'] = $quotation->source ?: 'MỚI';
                $this->captureFinancialBase();
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

    public function updatedFormDataPaymentPercentage(): void
    {
        $percentage = max(0, min(100, (float) ($this->formData['payment_percentage'] ?? 100)));
        $this->formData['payment_percentage'] = $percentage;
        foreach (['value', 'commission', 'revenue'] as $field) {
            $this->formData[$field] = (int) round($this->financialBase[$field] * $percentage / 100);
        }
    }

    private function captureFinancialBase(): void
    {
        $percentage = max(0.01, (float) ($this->formData['payment_percentage'] ?? 100));
        foreach (['value', 'commission', 'revenue'] as $field) {
            $value = preg_replace('/\D+/', '', (string) ($this->formData[$field] ?? 0));
            $this->financialBase[$field] = ((int) $value) * 100 / $percentage;
        }
    }

    public function updatedSortDirection($value): void
    {
        $this->sortDirection = $value === 'asc' ? 'asc' : 'desc';
        $this->resetPage();
    }

    public function updatedSortBy($value): void
    {
        $this->sortBy = in_array($value, ['id', 'report_number'], true) ? $value : 'id';
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
        $user = auth()->user();
        if (! $user || ! $user->can($this->getPermEdit()->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa hợp đồng này.']);

            return;
        }

        $modelClass = $this->getModelClass();
        $this->selectedDoc = $modelClass::with(['assignments.user'])->findOrFail($id);

        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
        if ($isRestrictedSales && (int) $this->selectedDoc->staff_id !== (int) $user->id) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa hợp đồng này.']);

            return;
        }

        $this->formData = $this->selectedDoc->toArray();
        if ($this->selectedDoc->signed_at) {
            $this->formData['signed_at'] = $this->selectedDoc->signed_at->format('Y-m-d');
        }
        if ($this->selectedDoc->submitted_at) {
            $this->formData['submitted_at'] = $this->selectedDoc->submitted_at->format('Y-m-d');
        }
        $this->normalizeContractEnumFields();
        $this->captureFinancialBase();
        $paymentMethod = trim((string) ($this->formData['payment_method'] ?? ''));
        $this->paymentMethods = $paymentMethod === '' ? [] : preg_split('/\s*\|\s*/', $paymentMethod);
        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function save(): void
    {
        $user = auth()->user();
        $modelClass = $this->getModelClass();

        if (! $user || ! $user->can($this->isEditing ? $this->getPermEdit()->value : $this->getPermCreate()->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu hợp đồng này.']);

            return;
        }

        $isRestrictedTpKd = false; // TPKD has permission to edit contracts of all staff
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
        if ($this->isEditing && ($isRestrictedTpKd || $isRestrictedSales) && (int) $this->selectedDoc->staff_id !== (int) $user->id) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật hợp đồng do bạn phụ trách.']);

            return;
        }

        if (! $user->hasAnyRole([Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value])) {
            $this->formData['staff_id'] = ($this->isEditing && $this->selectedDoc)
                ? ($this->selectedDoc->staff_id ?: $user->id)
                : $user->id;
        }

        $this->formData['payment_method'] = implode(' | ', $this->paymentMethods);
        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue', 'ncc_payment'], true);
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

        $this->resolveManualCustomer();
        $data = collect($this->formData)->map(fn ($v) => $v === '' ? null : $v)->toArray();

        $isAccountant = $user->hasRole(Role::KE_TOAN->value);
        if (! $this->isEditing) {
            $data['shd_bc'] = null;
            $data['ncc_payment'] = 0;
            $data['ncc_payment_sheet_url'] = null;
            $data['ncc_payment_updated_at'] = null;
            $data['ncc_payment_status'] = 'unpaid';
            $data['ncc_payment_paid_at'] = null;
        } elseif (! $isAccountant && $this->selectedDoc) {
            $data['shd_bc'] = $this->selectedDoc->shd_bc;
            $data['ncc_payment'] = $this->selectedDoc->ncc_payment;
            $data['ncc_payment_sheet_url'] = $this->selectedDoc->ncc_payment_sheet_url;
            $data['ncc_payment_updated_at'] = $this->selectedDoc->ncc_payment_updated_at;
            $data['ncc_payment_status'] = $this->selectedDoc->ncc_payment_status;
            $data['ncc_payment_paid_at'] = $this->selectedDoc->ncc_payment_paid_at;
        } elseif ($isAccountant && $this->selectedDoc) {
            $nccChanged = (int) ($data['ncc_payment'] ?? 0) !== (int) $this->selectedDoc->ncc_payment
                || (string) ($data['ncc_payment_sheet_url'] ?? '') !== (string) ($this->selectedDoc->ncc_payment_sheet_url ?? '');

            $data['ncc_payment_updated_at'] = $nccChanged
                ? now()
                : $this->selectedDoc->ncc_payment_updated_at;

            if (($data['ncc_payment_status'] ?? 'unpaid') !== 'paid') {
                $data['ncc_payment_status'] = 'unpaid';
                $data['ncc_payment_paid_at'] = null;
            }
        }

        $data = $this->filterDataForModelTable($modelClass, $data);

        if ($this->isEditing && $this->selectedDoc) {
            $this->selectedDoc->update($data);
        } else {
            $newContract = $modelClass::create($data);

            if (count($this->createAssignUserIds) > 0 || ! empty($this->createAssignExternal)) {
                $contractType = $this->getContractType();
                $contractLabel = $newContract->shd_bc ?: ($newContract->customer?->name ?: 'HĐ #'.$newContract->id);
                foreach ($this->createAssignUserIds as $userId) {
                    ContractAssignment::create([
                        'assignable_type' => $modelClass,
                        'assignable_id' => $newContract->id,
                        'user_id' => (int) $userId,
                        'assigned_by' => auth()->id(),
                        'deadline' => $this->createAssignDeadline ?: null,
                    ]);
                    $assignedUser = User::find($userId);
                    if ($assignedUser && $assignedUser->id !== auth()->id()) {
                        $assignedUser->notify(new ContractAssignedNotification($contractType, $newContract->id, $contractLabel, auth()->user()->name));
                    }
                }
                if (! empty($this->createAssignExternal)) {
                    ContractAssignment::create([
                        'assignable_type' => $modelClass,
                        'assignable_id' => $newContract->id,
                        'user_id' => null,
                        'external_assignee' => $this->createAssignExternal,
                        'assigned_by' => auth()->id(),
                        'deadline' => $this->createAssignDeadline ?: null,
                    ]);
                }
            }
        }

        $this->showModal = false;
        $this->dispatch('closeFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Lưu hợp đồng thành công!']);
        $this->resetForm();
    }

    public function updateStatus(int $id, string $status): void
    {
        $modelClass = $this->getModelClass();
        $doc = $modelClass::findOrFail($id);
        $user = auth()->user();

        if (! $user) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Phiên đăng nhập hết hạn, vui lòng tải lại trang.']);

            return;
        }

        $isRestrictedTpKd = false; // TPKD has permission to edit contracts of all staff
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
        if ($isRestrictedTpKd || $isRestrictedSales) {
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

        if (! $user->can($this->getPermEdit()->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền chỉnh sửa hợp đồng.']);

            return;
        }

        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
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
        $doc = $modelClass::findOrFail($id);
        $user = auth()->user();

        if (! $user) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Phiên đăng nhập hết hạn, vui lòng tải lại trang.']);

            return;
        }

        $isRestrictedTpKd = false; // TPKD has permission to edit contracts of all staff
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
        if ($isRestrictedTpKd || $isRestrictedSales) {
            if ((int) $doc->staff_id !== (int) $user->id) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền xóa hợp đồng này.']);

                return;
            }
        }

        if (! $user->can($this->getPermDelete()->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền xóa hợp đồng.']);

            return;
        }

        $doc->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hợp đồng!']);
    }

    public function duplicate(int $id): void
    {
        $user = auth()->user();
        if (! $user || ! $user->can($this->getPermCreate()->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền thực hiện thao tác này.']);

            return;
        }
        $modelClass = $this->getModelClass();
        $doc = $modelClass::findOrFail($id);
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

    // ── View / detail / workflow ──────────────────────────────────────────────

    #[Computed]
    public function canManageContractFiles(): bool
    {
        return auth()->user()->hasRole(Role::KE_TOAN->value);
    }

    #[Computed]
    public function canBulkDelete(): bool
    {
        return false;
    }

    #[Computed]
    public function isRestrictedRole(): bool
    {
        return auth()->user()->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
    }

    public function workflowProgressMeta($doc): array
    {
        $completedSteps = $doc->workflowSteps->pluck('step_name')->unique()->count();
        $totalSteps = $doc->getMorphClass()::TOTAL_STEPS;
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
        $daysLeft = $deadline ? (int) now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false) : null;
        $isOverdue = $deadline && $deadline->isPast() && ! $isFinished;

        return [
            'deadline' => $deadline,
            'daysLeft' => $daysLeft,
            'isFinished' => $isFinished,
            'isOverdue' => $isOverdue,
            'isNearDue' => $deadline && ! $isOverdue && ! $isFinished && $daysLeft !== null && $daysLeft <= 3,
        ];
    }

    public function canUpdateStatusForDoc($doc): bool
    {
        $user = auth()->user();

        return ! $user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
    }

    public function canManageOwnedDoc($doc): bool
    {
        return true;
    }

    public function statusColorForDoc($doc): array
    {
        return match ($doc->status) {
            'PTH đang kiểm tra', 'ĐANG THỰC HIỆN' => ['bg' => '#cfe2ff', 'text' => '#0d6efd'],
            'Đang trình BGĐ ký' => ['bg' => '#fff3cd', 'text' => '#b45309'],
            'Đã gửi khách hàng' => ['bg' => '#e2d9f3', 'text' => '#6f42c1'],
            'Đã hoàn thành', 'HOÀN THÀNH' => ['bg' => '#d1e7dd', 'text' => '#198754'],
            'Hợp đồng hủy', 'ĐÃ HỦY' => ['bg' => '#f8d7da', 'text' => '#dc3545'],
            default => ['bg' => '#e9ecef', 'text' => '#6c757d'],
        };
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

    public function updateInlineReportNumber(int $docId, ?string $value): void
    {
        if (! auth()->user()->hasRole(Role::KY_THUAT->value)) {
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
        $this->detailActiveTab = 'info';
        $modelClass = $this->getModelClass();
        $this->selectedDoc = $modelClass::with(['customer', 'staff', 'department', 'assignments.user', 'assignments.assigner', 'handler'])->find($id);
        if ($this->selectedDoc) {
            $user = auth()->user();
            if (! $user) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Phiên đăng nhập hết hạn, vui lòng tải lại trang.']);

                return;
            }
            $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
                && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);
            if ($isRestrictedSales && (int) $this->selectedDoc->staff_id !== (int) $user->id) {
                $this->selectedDoc = null;
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền xem chi tiết hợp đồng này.']);

                return;
            }
            $this->progressNotes = ContractProgressNote::where('contract_type', $this->getContractType())
                ->where('contract_id', $id)
                ->with('user')
                ->latest()
                ->get();
            $this->existingContractFiles = ContractMilestoneFile::where('contract_type', $modelClass)
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

    public function viewDetailDocs(int $id): void
    {
        $this->viewDetail($id);
        $this->detailActiveTab = 'docs';
        $this->dispatch('openDetailModal');
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

    public function openAssign(int $id): void
    {
        if (! $this->canAssign()) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền giao việc.']);

            return;
        }

        $modelClass = $this->getModelClass();
        $this->assignContractId = $id;
        $existing = ContractAssignment::where('assignable_type', $modelClass)
            ->where('assignable_id', $id)
            ->get();
        $this->assignUserIds = $existing->whereNotNull('user_id')->pluck('user_id')->toArray();
        $this->assignExternal = $existing->whereNull('user_id')->first()?->external_assignee;
        $this->assignDeadline = $existing->first()?->deadline?->format('Y-m-d');
        $this->dispatch('openAssignModal');
    }

    public function saveAssign(): void
    {
        if (! $this->canAssign()) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền giao việc.']);

            return;
        }

        $modelClass = $this->getModelClass();
        $contractType = $this->getContractType();

        ContractAssignment::where('assignable_type', $modelClass)
            ->where('assignable_id', $this->assignContractId)
            ->delete();

        foreach ($this->assignUserIds as $userId) {
            ContractAssignment::create([
                'assignable_type' => $modelClass,
                'assignable_id' => $this->assignContractId,
                'user_id' => (int) $userId,
                'assigned_by' => auth()->id(),
                'deadline' => $this->assignDeadline ?: null,
            ]);
        }

        if (! empty($this->assignExternal)) {
            ContractAssignment::create([
                'assignable_type' => $modelClass,
                'assignable_id' => $this->assignContractId,
                'user_id' => null,
                'external_assignee' => $this->assignExternal,
                'assigned_by' => auth()->id(),
                'deadline' => $this->assignDeadline ?: null,
            ]);
        }

        $contract = $modelClass::with('customer')->find($this->assignContractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$this->assignContractId);

        foreach ($this->assignUserIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                $user->notify(new ContractAssignedNotification($contractType, $this->assignContractId, $contractLabel, auth()->user()->name));
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
        $modelClass = $this->getModelClass();
        $contractType = $this->getContractType();
        $noteText = $this->progressNote;

        ContractProgressNote::create([
            'contract_type' => $contractType,
            'contract_id' => $contractId,
            'user_id' => auth()->id(),
            'note' => $noteText,
        ]);

        $this->progressNote = '';
        $this->progressNotes = ContractProgressNote::where('contract_type', $contractType)
            ->where('contract_id', $contractId)
            ->with('user')
            ->latest()
            ->get();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã thêm ghi chú!']);

        $contract = $modelClass::with('customer')->find($contractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$contractId);
        $recipients = User::whereHas('roles', fn ($q) => $q->whereIn('name', [
            Role::GIAM_DOC->value,
            Role::TP_KINH_DOANH->value,
            Role::IT->value,
        ]))->get();

        $assignmentUserIds = ContractAssignment::where('assignable_type', $modelClass)
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
            'handler_id' => '',
            'hide_completed_workflow' => auth()->user()->hasAnyRole([
                Role::TU_VAN->value,
                Role::KY_THUAT->value,
            ]),
        ];
        $this->sortBy = 'id';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    // ── Export ───────────────────────────────────────────────────────────────

    public function exportExcel(): StreamedResponse
    {
        $user = auth()->user();
        $modelClass = $this->getModelClass();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = $modelClass::with(['customer', 'staff', 'department', 'handler', 'workflowSteps'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($isRestrictedSales, fn ($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn ($q) => $q->whereHas('assignments', fn ($sq) => $sq->where('user_id', $user->id)));

        $this->applyFilters($query);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $supportsReportNumberSorting = Schema::hasColumn((new $modelClass)->getTable(), 'report_number');
        $sortColumn = $supportsReportNumberSorting && ($this->sortBy === 'report_number' || $user->hasRole(Role::KY_THUAT->value)) ? 'report_number' : 'id';
        $docs = $query->orderBy($sortColumn, $orderDirection)->orderBy('id', 'desc')->get();
        $title = $this->getExportTitle();
        $showFinancials = ! $user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, $this->getExportFilenamePrefix().'_'.now()->format('d_m_Y').'.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $user = auth()->user();
        $modelClass = $this->getModelClass();
        $isRestrictedSales = $user->hasRole(Role::KINH_DOANH->value)
            && ! $user->hasAnyRole([Role::GIAM_DOC->value, Role::TP_KINH_DOANH->value, Role::IT->value]);

        $query = $modelClass::with(['customer', 'staff', 'department', 'assignments.user', 'handler', 'workflowSteps'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($isRestrictedSales, fn ($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn ($q) => $q->whereHas('assignments', fn ($sq) => $sq->where('user_id', $user->id)));

        $this->applyFilters($query);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $supportsReportNumberSorting = Schema::hasColumn((new $modelClass)->getTable(), 'report_number');
        $sortColumn = $supportsReportNumberSorting && ($this->sortBy === 'report_number' || $user->hasRole(Role::KY_THUAT->value)) ? 'report_number' : 'id';
        $docs = $query->orderBy($sortColumn, $orderDirection)->orderBy('id', 'desc')->paginate(10);

        $isRestrictedUser = $isRestrictedSales || $user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]);
        $baseUserQuery = $modelClass::query()
            ->when($isRestrictedSales, fn ($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole([Role::TU_VAN->value, Role::KY_THUAT->value]),
                fn ($q) => $q->whereHas('assignments', fn ($sq) => $sq->where('user_id', $user->id)));

        $loaiDichVuOptions = $modelClass::SERVICE_TYPES;

        $scopedProvinces = $isRestrictedUser
            ? (clone $baseUserQuery)->whereNotNull('province')->where('province', '!=', '')->distinct()->orderBy('province')->pluck('province')->toArray()
            : $modelClass::whereNotNull('province')->where('province', '!=', '')->distinct()->orderBy('province')->pluck('province')->toArray();

        return view('livewire.admin.contracts.'.$this->getViewName(), [
            'docs' => $docs,
            'customers' => Customer::orderBy('name')->get(),
            'staffs' => User::role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value])->where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::all(),
            'assignable_users' => User::where('is_active', true)->whereHas('roles', fn ($q) => $q->whereIn('name', [Role::TU_VAN->value, Role::KY_THUAT->value, Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value]))->orderBy('name')->get(),
            'provinces' => $scopedProvinces,
            'all_statuses' => self::ALLOWED_STATUSES,
            'renewal_statuses' => $modelClass::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'renewal_status_options' => ContractRenewalStatus::map(),
            'voucher_status_options' => ContractVoucherStatus::values(),
            'loai_dich_vu_options' => $loaiDichVuOptions,
            'payment_methods' => ['Sau khi ký HĐ', 'Sau khi có kết quả/báo cáo', 'Sau khi bàn giao + Nghiệm thu'],
            'info_sources' => collect(['MỚI', 'Sale', 'Tái ký', 'Thông tin chuyển', 'Thông tin chuyển MKT'])
                ->merge($modelClass::whereNotNull('info_source')->where('info_source', '!=', '')->distinct()->pluck('info_source'))
                ->unique()->values()->all(),
            'parentContracts' => $modelClass::with('customer')->where('is_renewal', false)->orderByDesc('id')->get(),
            'handlers' => Handler::orderBy('name')->get(),
            'supports_report_number_sorting' => $supportsReportNumberSorting,
        ])->layout('admin.layouts.app', ['title' => $this->getPageTitle()]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function applyFilters($query): void
    {
        $this->applyContractFilters($query);
    }

    public function uploadContractFile(): void
    {
        $this->validate([
            'newContractFiles' => 'required|array|max:10',
            'newContractFiles.*' => 'file|max:51200|mimes:pdf',
        ], [
            'newContractFiles.required' => 'Vui lòng chọn ít nhất 1 file.',
            'newContractFiles.*.max' => 'File PDF không được vượt quá 50MB.',
            'newContractFiles.*.mimes' => 'Chỉ chấp nhận file PDF.',
        ]);

        $disk = config('filesystems.upload_disk', 'public');
        $contractType = $this->getContractType();
        $modelClass = $this->getModelClass();

        foreach ($this->newContractFiles as $file) {
            $path = $file->storePublicly("contract-files/{$contractType}/contract_document", $disk);
            ContractMilestoneFile::create([
                'contract_type' => $modelClass,
                'contract_id' => $this->selectedDoc->id,
                'milestone' => 'contract_document',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploader_id' => auth()->id(),
            ]);
        }

        $this->newContractFiles = [];
        $this->existingContractFiles = ContractMilestoneFile::where('contract_type', $modelClass)
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

        $modelClass = $this->getModelClass();
        $this->existingContractFiles = ContractMilestoneFile::where('contract_type', $modelClass)
            ->where('contract_id', $this->selectedDoc->id)
            ->where('milestone', 'contract_document')
            ->with('uploader')
            ->latest()
            ->get();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa file.']);
    }

    private function resetForm(): void
    {
        $this->newCustomerName = '';
        $this->paymentMethods = ['Sau khi ký HĐ'];
        $this->formData = [
            'shd_cxl' => '',
            'shd_bc' => '',
            'customer_id' => '',
            'handler_id' => '',
            'staff_id' => auth()->id(),
            'department_id' => 3,
            'signed_at' => date('Y-m-d'),
            'submitted_at' => '',
            'value' => 0,
            'commission' => 0,
            'revenue' => 0,
            'payment_percentage' => 100,
            'service_content' => '',
            'submission_place' => '',
            'ncc_payment' => 0,
            'ncc_payment_sheet_url' => '',
            'ncc_payment_status' => 'unpaid',
            'ncc_payment_paid_at' => '',
            'province' => '',
            'info_source' => 'MỚI',
            'payment_method' => 'Sau khi ký HĐ',
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
        $this->createAssignUserIds = [];
        $this->createAssignDeadline = null;
        $this->createAssignExternal = null;
    }

    private function filterDataForModelTable(string $modelClass, array $data): array
    {
        $table = (new $modelClass)->getTable();

        return collect($data)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->toArray();
    }
}
