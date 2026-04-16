<?php

namespace App\Livewire\Admin\Contracts;

use App\Livewire\Concerns\CleanMoneyInput;
use App\Livewire\Concerns\ContractValidation;
use App\Models\ContractAssignment;
use App\Models\ContractConsulting;
use App\Models\ContractProgressNote;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;
use App\Support\VietnamProvinces;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractConsultingManager extends Component
{
    use CleanMoneyInput, ContractValidation, WithPagination;

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

    public $showDetail = false;

    public $selectedDoc = null;

    public bool $showAssignModal = false;

    public ?int $assignContractId = null;

    public array $assignUserIds = [];

    public string $progressNote = '';

    public $progressNotes = [];

    public ?int $workflowContractId = null;

    public ?int $quotation_id = null;

    public $formData = [
        'shd_bc' => '',
        'customer_id' => '',
        'staff_id' => '',
        'department_id' => '',
        'signed_at' => '',
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
                    ['address' => $quotation->address ?? '']
                );
                $this->formData['customer_id'] = $customer->id;
                $this->formData['value'] = $quotation->original_value ?? 0;
                $this->formData['commission'] = $quotation->commission_value ?? 0;
                $this->formData['revenue'] = $quotation->original_value ?? 0;
                $this->formData['staff_id'] = $quotation->staff_id ?? auth()->id();
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
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function edit(int $id): void
    {
        $this->selectedDoc = ContractConsulting::findOrFail($id);
        $this->formData = $this->selectedDoc->toArray();
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
        $user = auth()->user();

        if (! $user->can($this->isEditing ? 'contracts-consulting.edit' : 'contracts-consulting.create')) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu hợp đồng này.']);

            return;
        }

        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && ! $user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);
        if ($this->isEditing && $isRestrictedTpKd && $this->selectedDoc->staff_id !== $user->id) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật hợp đồng do bạn phụ trách.']);

            return;
        }

        // Staff field is hidden for some roles, so keep a stable value before validation.
        if (! $user->hasAnyRole(['tp-kinh-doanh', 'giam-doc'])) {
            $this->formData['staff_id'] = ($this->isEditing && $this->selectedDoc)
                ? ($this->selectedDoc->staff_id ?: $user->id)
                : $user->id;
        }

        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue']);
        $this->ensureDepartmentId();

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

        $isAccountant = $user->hasRole('ke-toan');
        if (! $this->isEditing) {
            // Số HĐ BC do kế toán bổ sung sau khi tạo.
            $data['shd_bc'] = null;
        } elseif (! $isAccountant && $this->selectedDoc) {
            $data['shd_bc'] = $this->selectedDoc->shd_bc;
        }

        if ($this->isEditing && $this->selectedDoc) {
            $this->selectedDoc->update($data);
        } else {
            ContractConsulting::create($data);
        }

        $this->dispatch('closeFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Lưu hợp đồng thành công!']);
        $this->resetForm();
    }

    public function updateStatus(int $id, string $status): void
    {
        $doc = ContractConsulting::findOrFail($id);
        $user = auth()->user();
        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && ! $user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);

        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        } else {
            abort_if($user->hasAnyRole(['tu-van', 'ky-thuat']), 403);
        }
        abort_unless($user->can('contracts-consulting.edit'), 403);

        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            return;
        }

        $updateData = ['status' => $status];
        if ($status === 'Đã hoàn thành') {
            $updateData['submitted_at'] = now()->toDateString();
        }

        ContractConsulting::findOrFail($id)->update($updateData);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tình trạng!']);
    }

    public function delete(int $id): void
    {
        $doc = ContractConsulting::findOrFail($id);
        $user = auth()->user();
        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && ! $user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);

        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        }
        abort_unless($user->can('contracts-consulting.delete'), 403);

        $doc->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hợp đồng!']);
    }

    public function bulkDeleteSelected(): void
    {
        $user = auth()->user();
        abort_unless($user->can('contracts-consulting.delete'), 403);

        $selectedIds = collect($this->selectedDocIds)
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Vui lòng chọn ít nhất 1 hợp đồng để xóa.']);

            return;
        }

        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && ! $user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);
        $deletedCount = 0;
        $skippedCount = 0;

        $docs = ContractConsulting::whereIn('id', $selectedIds)->get();
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
        $this->selectedDoc = ContractConsulting::with(['customer', 'staff', 'department', 'assignments.user', 'assignments.assigner'])->find($id);
        if ($this->selectedDoc) {
            $this->progressNotes = ContractProgressNote::where('contract_type', 'consulting')
                ->where('contract_id', $id)
                ->with('user')
                ->latest()
                ->get();
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    public function openAssign(int $id): void
    {
        $this->assignContractId = $id;
        $this->assignUserIds = ContractAssignment::where('assignable_type', ContractConsulting::class)
            ->where('assignable_id', $id)
            ->pluck('user_id')
            ->toArray();
        $this->dispatch('openAssignModal');
    }

    public function saveAssign(): void
    {
        ContractAssignment::where('assignable_type', ContractConsulting::class)
            ->where('assignable_id', $this->assignContractId)
            ->delete();
        foreach ($this->assignUserIds as $userId) {
            ContractAssignment::create([
                'assignable_type' => ContractConsulting::class,
                'assignable_id' => $this->assignContractId,
                'user_id' => (int) $userId,
                'assigned_by' => auth()->id(),
            ]);
        }
        // Gửi thông báo đến users được giao
        $contract = ContractConsulting::with('customer')->find($this->assignContractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$this->assignContractId);
        foreach ($this->assignUserIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                $user->notify(new ContractAssignedNotification('consulting', $this->assignContractId, $contractLabel, auth()->user()->name));
            }
        }
        $this->assignContractId = null;
        $this->assignUserIds = [];
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
        $contract = ContractConsulting::with('customer')->find($contractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$contractId);
        $recipients = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']))->get();

        $assignmentUserIds = ContractAssignment::where('assignable_type', ContractConsulting::class)
            ->where('assignable_id', $contractId)
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
        ];
        $this->selectedDocIds = [];
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->formData = [
            'shd_bc' => '',
            'customer_id' => '',
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
        $this->selectedDoc = null;
    }

    public function exportExcel(): StreamedResponse
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole('kinh-doanh')
            && ! $user->hasAnyRole(['admin', 'giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']);

        $query = ContractConsulting::with(['customer', 'staff', 'department'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($isRestrictedSales, fn ($q) => $q->where('staff_id', $user->id))
            ->when($user->hasAnyRole(['tu-van', 'ky-thuat']),
                fn ($q) => $q->whereHas('assignments', fn ($sq) => $sq->where('user_id', $user->id)));

        if ($this->filter['signed_from']) {
            $query->whereDate('signed_at', '>=', $this->filter['signed_from']);
        }
        if ($this->filter['signed_to']) {
            $query->whereDate('signed_at', '<=', $this->filter['signed_to']);
        }
        if ($this->filter['submitted_from']) {
            $query->whereDate('submitted_at', '>=', $this->filter['submitted_from']);
        }
        if ($this->filter['submitted_to']) {
            $query->whereDate('submitted_at', '<=', $this->filter['submitted_to']);
        }
        if ($this->filter['province']) {
            $query->where('province', $this->filter['province']);
        }
        if ($this->filter['department_id']) {
            $query->where('department_id', $this->filter['department_id']);
        }
        if ($this->filter['staff_id']) {
            $query->where('staff_id', $this->filter['staff_id']);
        }
        if ($this->filter['info_source']) {
            $query->where('info_source', $this->filter['info_source']);
        }
        if ($this->filter['payment_method']) {
            $query->where('payment_method', $this->filter['payment_method']);
        }
        if ($this->filter['status']) {
            $query->where('status', $this->filter['status']);
        }
        if ($this->filter['renewal_status']) {
            $query->where('renewal_status', $this->filter['renewal_status']);
        }
        if ($this->filter['voucher_status']) {
            $query->where('voucher_status', $this->filter['voucher_status']);
        }
        if ($this->filter['is_offset']) {
            $query->where('is_offset', true);
        }
        if ($this->filter['has_room_fund']) {
            $query->where('has_room_fund', true);
        }
        if ($this->filter['is_overdue']) {
            $query->where('is_overdue', true);
        }
        if ($this->filter['loai_dich_vu']) {
            $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);
        }

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs = $query->orderBy('id', $orderDirection)->get();
        $title = 'Hợp đồng tư vấn';
        $showFinancials = ! auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, 'HopDong_TuVan_'.now()->format('d_m_Y').'.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    private function getWorkflowProgress($items)
    {
        $stepKeys = ContractWorkflowStep::STEP_KEYS;
        $stepLabels = ContractWorkflowStep::STEPS;
        $totalSteps = count($stepKeys);
        $modelClass = ContractConsulting::class;

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
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole('kinh-doanh')
            && ! $user->hasAnyRole(['admin', 'giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']);

        $query = ContractConsulting::with(['customer', 'staff', 'department', 'assignments.user'])
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
            ->when($user->hasAnyRole(['tu-van', 'ky-thuat']),
                fn ($q) => $q->whereHas('assignments', fn ($sq) => $sq->where('user_id', $user->id)));

        if ($this->filter['signed_from']) {
            $query->whereDate('signed_at', '>=', $this->filter['signed_from']);
        }
        if ($this->filter['signed_to']) {
            $query->whereDate('signed_at', '<=', $this->filter['signed_to']);
        }
        if ($this->filter['submitted_from']) {
            $query->whereDate('submitted_at', '>=', $this->filter['submitted_from']);
        }
        if ($this->filter['submitted_to']) {
            $query->whereDate('submitted_at', '<=', $this->filter['submitted_to']);
        }
        if ($this->filter['province']) {
            $query->where('province', $this->filter['province']);
        }
        if ($this->filter['department_id']) {
            $query->where('department_id', $this->filter['department_id']);
        }
        if ($this->filter['staff_id']) {
            $query->where('staff_id', $this->filter['staff_id']);
        }
        if ($this->filter['info_source']) {
            $query->where('info_source', $this->filter['info_source']);
        }
        if ($this->filter['payment_method']) {
            $query->where('payment_method', $this->filter['payment_method']);
        }
        if ($this->filter['status']) {
            $query->where('status', $this->filter['status']);
        }
        if ($this->filter['renewal_status']) {
            $query->where('renewal_status', $this->filter['renewal_status']);
        }
        if ($this->filter['voucher_status']) {
            $query->where('voucher_status', $this->filter['voucher_status']);
        }
        if ($this->filter['is_offset']) {
            $query->where('is_offset', true);
        }
        if ($this->filter['has_room_fund']) {
            $query->where('has_room_fund', true);
        }
        if ($this->filter['is_overdue']) {
            $query->where('is_overdue', true);
        }
        if ($this->filter['loai_dich_vu']) {
            $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);
        }

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs = $query->orderBy('id', $orderDirection)->paginate(10);

        $workflowProgress = $this->getWorkflowProgress($docs);

        return view('livewire.admin.contracts.contract-consulting-manager', [
            'workflowProgress' => $workflowProgress,
            'docs' => $docs,
            'customers' => Customer::orderBy('name')->get(),
            'staffs' => User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get(),
            'departments' => Department::all(),
            'assignable_users' => User::whereHas('roles', fn ($q) => $q->whereIn('name', ['tu-van', 'ky-thuat']))->orderBy('name')->get(),
            'provinces' => $user->hasAnyRole(['tu-van', 'ky-thuat'])
                ? ContractConsulting::whereHas('assignments', fn ($q) => $q->where('user_id', $user->id))
                    ->whereNotNull('province')->where('province', '!=', '')
                    ->distinct()->orderBy('province')->pluck('province')->toArray()
                : ContractConsulting::whereNotNull('province')->where('province', '!=', '')
                    ->distinct()->orderBy('province')->pluck('province')->toArray(),
            'all_statuses' => self::ALLOWED_STATUSES,
            'renewal_statuses' => ContractConsulting::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'voucher_status_options' => ContractWaste::VOUCHER_STATUSES,
            'loai_dich_vu_options' => ContractConsulting::SERVICE_TYPES,
            'payment_methods' => ['Sau ký', 'Trước ký'],
            'info_sources' => ContractConsulting::whereNotNull('info_source')->where('info_source', '!=', '')->distinct()->pluck('info_source')->toArray(),
            'parentContracts' => ContractConsulting::with('customer')->where('is_renewal', false)->orderByDesc('id')->get(),
        ])->layout('admin.layouts.app', ['title' => 'Hồ sơ môi trường']);
    }
}
