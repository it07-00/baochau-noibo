<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Handler;
use App\Models\User;
use App\Models\Department;
use App\Models\ContractAssignment;
use App\Models\ContractProgressNote;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Livewire\Concerns\ContractValidation;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;

class ContractWasteManager extends Component
{
    use WithPagination, WithFileUploads, CleanMoneyInput, ContractValidation;

    private const ALLOWED_STATUSES = [
        'Đã trình ký nhà thầu phụ',
        'Nhà thầu phụ đã gửi về',
        'Đã gửi khách hàng',
        'Đã hoàn thành KH ký trước',
        'Đã hoàn thành',
        'Hợp đồng hủy',
    ];

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $sortDirection = 'desc';
    public array $selectedDocIds = [];

    public $showDetail = false;
    public $showModal = false;
    public $isEditing = false;
    public $selectedDoc = null;
    public bool $showAssignModal = false;
    public ?int $assignContractId = null;
    public array $assignUserIds = [];
    public string $progressNote = '';
    public $progressNotes = [];
    public ?int $workflowContractId = null;

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
    ];

    protected $queryString = ['search', 'quotation_id'];
    public $quotation_id;

    public function paginationView()
    {
        return 'livewire.admin.users.pagination';
    }

    public function mount()
    {
        if ($this->quotation_id) {
            abort_unless(auth()->user()->can('contracts-waste.create'), 403);

            $quotation = \App\Models\Quotation::find($this->quotation_id);
            if ($quotation) {
                // Find or create customer
                $customer = \App\Models\Customer::firstOrCreate(
                    ['name' => $quotation->company_name],
                    ['address' => $quotation->address]
                );

                $this->formData['customer_id'] = $customer->id;
                $this->formData['content'] = $quotation->work_description;
                $this->formData['value'] = $quotation->total_value;
                $this->formData['commission'] = $quotation->commission_value;
                $this->formData['revenue'] = $quotation->total_value;
                $this->formData['staff_id'] = $quotation->staff_id;
                $this->formData['billing_address'] = $quotation->address;
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
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function edit($id)
    {
        $doc = ContractWaste::findOrFail($id);
        $this->selectedDoc = $doc;
        $this->formData = $doc->toArray();
        // Format dates for input
        $this->formData['signed_at'] = $doc->signed_at ? $doc->signed_at->format('Y-m-d') : '';
        $this->formData['effective_at'] = $doc->effective_at ? $doc->effective_at->format('Y-m-d') : '';
        $this->formData['end_at'] = $doc->end_at ? $doc->end_at->format('Y-m-d') : '';
        $this->formData['submitted_at'] = $doc->submitted_at ? $doc->submitted_at->format('Y-m-d') : '';

        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function save()
    {
        $user = auth()->user();

        if (!$user->can($this->isEditing ? 'contracts-waste.edit' : 'contracts-waste.create')) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn không có quyền lưu hợp đồng này.']);
            return;
        }

        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && !$user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);
        if ($this->isEditing && $isRestrictedTpKd && $this->selectedDoc->staff_id !== $user->id) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Bạn chỉ được cập nhật hợp đồng do bạn phụ trách.']);
            return;
        }

        if (!$user->hasAnyRole(['tp-kinh-doanh', 'giam-doc'])) {
            $this->formData['staff_id'] = ($this->isEditing && $this->selectedDoc)
                ? ($this->selectedDoc->staff_id ?: $user->id)
                : $user->id;
        }

        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue']);
        $this->ensureDepartmentId();

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

        $isAccountant = $user->hasRole('ke-toan');

        if (!$this->isEditing) {
            // Số HĐ chỉ do kế toán cập nhật sau khi tạo.
            $data['shd_cxl'] = null;
            $data['shd_bc'] = null;
        } elseif (!$isAccountant && $this->selectedDoc) {
            $data['shd_cxl'] = $this->selectedDoc->shd_cxl;
            $data['shd_bc'] = $this->selectedDoc->shd_bc;
        }

        if ($this->isEditing) {
            $this->selectedDoc->update($data);
            $msg = 'Cập nhật thành công';
        } else {
            ContractWaste::create($data);
            $msg = 'Tạo mới thành công';
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
        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && !$user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);

        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        } else {
            abort_if($user->hasAnyRole(['tu-van', 'ky-thuat']), 403);
        }
        abort_unless($user->can('contracts-waste.edit'), 403);

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
        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && !$user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);

        if ($isRestrictedTpKd) {
            abort_if($doc->staff_id !== $user->id, 403);
        }
        abort_unless($user->can('contracts-waste.delete'), 403);

        $doc->delete();
        $this->dispatch('swal:toast', ['message' => 'Đã xóa hợp đồng', 'type' => 'success']);
    }

    public function bulkDeleteSelected()
    {
        $user = auth()->user();
        abort_unless($user->can('contracts-waste.delete'), 403);

        $selectedIds = collect($this->selectedDocIds)
            ->map(static fn($id) => (int) $id)
            ->filter(static fn($id) => $id > 0)
            ->unique()
            ->values();

        if ($selectedIds->isEmpty()) {
            $this->dispatch('swal:toast', ['type' => 'warning', 'message' => 'Vui lòng chọn ít nhất 1 hợp đồng để xóa.']);
            return;
        }

        $isRestrictedTpKd = $user->hasRole('tp-kinh-doanh') && !$user->hasAnyRole(['admin', 'giam-doc', 'quan-ly']);
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
        $this->selectedDoc = null;
    }

    public function openAssign(int $id): void
    {
        $this->assignContractId = $id;
        $this->assignUserIds = ContractAssignment::where('assignable_type', ContractWaste::class)
            ->where('assignable_id', $id)
            ->pluck('user_id')
            ->toArray();
        $this->dispatch('openAssignModal');
    }

    public function saveAssign(): void
    {
        ContractAssignment::where('assignable_type', ContractWaste::class)
            ->where('assignable_id', $this->assignContractId)
            ->delete();
        foreach ($this->assignUserIds as $userId) {
            ContractAssignment::create([
                'assignable_type' => ContractWaste::class,
                'assignable_id'   => $this->assignContractId,
                'user_id'         => (int) $userId,
                'assigned_by'     => auth()->id(),
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
        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', ['giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']))->get();

        $assignmentUserIds = ContractAssignment::where('assignable_type', ContractWaste::class)
            ->where('assignable_id', $contractId)
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
        ];
        $this->selectedDocIds = [];
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function viewDetail($id)
    {
        $this->selectedDoc = ContractWaste::with(['customer', 'handler', 'staff', 'department', 'assignments.user', 'assignments.assigner'])->find($id);
        if ($this->selectedDoc) {
            $this->progressNotes = ContractProgressNote::where('contract_type', 'waste')
                ->where('contract_id', $id)
                ->with('user')
                ->latest()
                ->get();
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->selectedDoc = null;
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole('kinh-doanh')
            && !$user->hasAnyRole(['admin', 'giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']);

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
            ->when($user->hasAnyRole(['tu-van', 'ky-thuat']),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', $user->id)));

        if ($this->filter['signed_from'] ?? null)    $query->whereDate('signed_at', '>=', $this->filter['signed_from']);
        if ($this->filter['signed_to'] ?? null)      $query->whereDate('signed_at', '<=', $this->filter['signed_to']);
        if ($this->filter['end_from'] ?? null)       $query->whereDate('end_at', '>=', $this->filter['end_from']);
        if ($this->filter['end_to'] ?? null)         $query->whereDate('end_at', '<=', $this->filter['end_to']);
        if ($this->filter['submitted_from'] ?? null) $query->whereDate('submitted_at', '>=', $this->filter['submitted_from']);
        if ($this->filter['submitted_to'] ?? null)   $query->whereDate('submitted_at', '<=', $this->filter['submitted_to']);
        if ($this->filter['handler_id'] ?? null)     $query->where('handler_id', $this->filter['handler_id']);
        if ($this->filter['department_id'] ?? null)  $query->where('department_id', $this->filter['department_id']);
        if ($this->filter['province'] ?? null)       $query->where('province', $this->filter['province']);
        if ($this->filter['staff_id'] ?? null)       $query->where('staff_id', $this->filter['staff_id']);
        if ($this->filter['is_offset'] ?? null)      $query->where('is_offset', true);
        if ($this->filter['is_overdue'] ?? null)     $query->where('is_overdue', true);
        if ($this->filter['status'] ?? null)         $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status'] ?? null) $query->where('renewal_status', $this->filter['renewal_status']);
        if ($this->filter['service_type'] ?? null)   $query->where('service_type', $this->filter['service_type']);
        if ($this->filter['waste_type'] ?? null)     $query->where('waste_type', $this->filter['waste_type']);
        if ($this->filter['loai_dich_vu'] ?? null)   $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);
        if ($this->filter['voucher_status'] ?? null) $query->where('voucher_status', $this->filter['voucher_status']);
        if ($this->filter['source'] ?? null)         $query->where('source', $this->filter['source']);
        if ($this->filter['payment_method'] ?? null) $query->where('payment_method', $this->filter['payment_method']);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs           = $query->orderBy('id', $orderDirection)->get();
        $title          = 'Hợp đồng chất thải';
        $showFinancials = !auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, 'HopDong_ChatThai_' . now()->format('d_m_Y') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $isRestrictedSales = $user->hasRole('kinh-doanh')
            && !$user->hasAnyRole(['admin', 'giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']);

        $query = ContractWaste::with(['customer', 'handler', 'staff', 'department', 'assignments.user'])
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
            ->when($user->hasAnyRole(['tu-van', 'ky-thuat']),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', $user->id)));

        // Apply filters
        if ($this->filter['signed_from'] ?? null) $query->whereDate('signed_at', '>=', $this->filter['signed_from']);
        if ($this->filter['signed_to'] ?? null) $query->whereDate('signed_at', '<=', $this->filter['signed_to']);
        if ($this->filter['end_from'] ?? null) $query->whereDate('end_at', '>=', $this->filter['end_from']);
        if ($this->filter['end_to'] ?? null) $query->whereDate('end_at', '<=', $this->filter['end_to']);
        if ($this->filter['returned_from'] ?? null) $query->whereDate('submitted_at', '>=', $this->filter['returned_from']); // Assuming return date mapped to submitted_at for now
        if ($this->filter['returned_to'] ?? null) $query->whereDate('submitted_at', '<=', $this->filter['returned_to']);
        if ($this->filter['submitted_from'] ?? null) $query->whereDate('submitted_at', '>=', $this->filter['submitted_from']);
        if ($this->filter['submitted_to'] ?? null) $query->whereDate('submitted_at', '<=', $this->filter['submitted_to']);

        if ($this->filter['handler_id'] ?? null) $query->where('handler_id', $this->filter['handler_id']);
        if ($this->filter['department_id'] ?? null) $query->where('department_id', $this->filter['department_id']);
        if ($this->filter['province'] ?? null) $query->where('province', $this->filter['province']);
        if ($this->filter['staff_id'] ?? null) $query->where('staff_id', $this->filter['staff_id']);
        if ($this->filter['is_offset'] ?? null) $query->where('is_offset', true);
        if ($this->filter['is_overdue'] ?? null) $query->where('is_overdue', true);
        if ($this->filter['status'] ?? null) $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status'] ?? null) $query->where('renewal_status', $this->filter['renewal_status']);
        if ($this->filter['service_type'] ?? null) $query->where('service_type', $this->filter['service_type']);
        if ($this->filter['waste_type'] ?? null) $query->where('waste_type', $this->filter['waste_type']);
        if ($this->filter['loai_dich_vu'] ?? null) $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);
        if ($this->filter['voucher_status'] ?? null) $query->where('voucher_status', $this->filter['voucher_status']);
        if ($this->filter['source'] ?? null) $query->where('source', $this->filter['source']);
        if ($this->filter['payment_method'] ?? null) $query->where('payment_method', $this->filter['payment_method']);

        $orderDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $docs = $query->orderBy('id', $orderDirection)->paginate(10);
        $voucherStatuses = collect(ContractWaste::VOUCHER_STATUSES)
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

        return view('livewire.admin.contracts.contract-waste-manager', [
            'docs' => $docs,
            'handlers' => Handler::orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'staffs' => User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get(),
            'departments' => Department::all(),
            'assignable_users' => \App\Models\User::whereHas('roles', fn($q) =>
                $q->whereIn('name', ['tu-van']))->orderBy('name')->get(),
            // Dynamic filter options
            'service_types' => ContractWaste::whereNotNull('service_type')->where('service_type', '!=', '')->distinct()->pluck('service_type')->toArray(),
            'waste_types' => ContractWaste::whereNotNull('waste_type')->where('waste_type', '!=', '')->distinct()->pluck('waste_type')->toArray(),
            'loai_dich_vu_options' => ContractWaste::SERVICE_TYPES,
            'all_statuses' => self::ALLOWED_STATUSES,
            'renewal_statuses' => ContractWaste::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'voucher_statuses' => $voucherStatuses,
            'voucher_status_options' => ContractWaste::VOUCHER_STATUSES,
            'payment_methods' => ['Sau ký', 'Trước ký'],
            'provinces' => ContractWaste::whereNotNull('province')->where('province', '!=', '')
                ->distinct()->orderBy('province')->pluck('province')->toArray(),
            'source_options' => ContractWaste::whereNotNull('source')->where('source', '!=', '')->distinct()->pluck('source')->toArray(),
            'parentContracts' => ContractWaste::with('customer')->where('is_renewal', false)->orderByDesc('id')->get(),
        ])->layout('admin.layouts.app', ['title' => 'Chất thải & Tiếng ồn']);
    }
}
