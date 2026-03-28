<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractConsulting;
use App\Models\Customer;
use App\Models\User;
use App\Models\Department;
use App\Models\Quotation;
use App\Models\ContractAssignment;
use App\Models\ContractProgressNote;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;

class ContractConsultingManager extends Component
{
    use WithPagination, CleanMoneyInput;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
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
        'shd_ad'         => '',
        'customer_id'    => '',
        'staff_id'       => '',
        'department_id'  => '',
        'signed_at'      => '',
        'submitted_at'   => '',
        'value'          => 0,
        'commission'     => 0,
        'revenue'        => 0,
        'province'       => '',
        'info_source'    => 'MỚI',
        'payment_method' => 'Sau ký',
        'loai_dich_vu'   => '',
        'status'         => 'ĐANG THỰC HIỆN',
        'renewal_status' => '',
        'is_offset'      => false,
        'has_room_fund'  => false,
        'is_overdue'     => false,
        'notes'          => '',
    ];

    public $filter = [
        'signed_from'    => '',
        'signed_to'      => '',
        'submitted_from' => '',
        'submitted_to'   => '',
        'province'       => '',
        'department_id'  => '',
        'info_source'    => '',
        'payment_method' => '',
        'status'         => '',
        'renewal_status' => '',
        'is_offset'      => false,
        'has_room_fund'  => false,
        'is_overdue'     => false,
        'loai_dich_vu'   => '',
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
                $this->formData['customer_id']    = $customer->id;
                $this->formData['value']          = $quotation->original_value ?? 0;
                $this->formData['commission']     = $quotation->commission_value ?? 0;
                $this->formData['revenue']        = $quotation->total_value ?? 0;
                $this->formData['staff_id']       = $quotation->staff_id ?? auth()->id();
                $this->formData['notes']          = $quotation->notes ?? '';
                $this->formData['info_source']    = 'MỚI';
                $this->showModal = true;
                $this->dispatch('openFormModal');
            }
        }
    }

    public function updatedSearch(): void
    {
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
        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue']);

        $this->validate([
            'formData.customer_id' => 'required',
            'formData.staff_id'    => 'required',
            'formData.value'       => 'required|numeric',
        ]);

        $data = collect($this->formData)->map(fn($v) => $v === '' ? null : $v)->toArray();

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
        ContractConsulting::findOrFail($id)->update(['status' => $status]);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tình trạng!']);
    }

    public function delete(int $id): void
    {
        ContractConsulting::findOrFail($id)->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hợp đồng!']);
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
                'assignable_id'   => $this->assignContractId,
                'user_id'         => (int) $userId,
                'assigned_by'     => auth()->id(),
            ]);
        }
        // Gửi thông báo đến users được giao
        $contract = ContractConsulting::with('customer')->find($this->assignContractId);
        $contractLabel = $contract?->shd_ad ?: ($contract?->customer?->name ?: 'HĐ #'.$this->assignContractId);
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
            'contract_id'   => $contractId,
            'user_id'       => auth()->id(),
            'note'          => $noteText,
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
        $contractLabel = $contract?->shd_ad ?: ($contract?->customer?->name ?: 'HĐ #'.$contractId);
        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', ['giam-doc', 'quan-ly', 'it']))->get();
        if ($contract?->staff_id && $contract->staff_id !== auth()->id()) {
            $staff = User::find($contract->staff_id);
            if ($staff) $recipients->push($staff);
        }
        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->id !== auth()->id()) {
                $recipient->notify(new ContractProgressNoteNotification('consulting', $contractId, $contractLabel, \Illuminate\Support\Str::limit($noteText, 50), auth()->user()->name));
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
            'info_source'    => '',
            'payment_method' => '',
            'status'         => '',
            'renewal_status' => '',
            'is_offset'      => false,
            'has_room_fund'  => false,
            'is_overdue'     => false,
            'loai_dich_vu'   => '',
        ];
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->formData = [
            'shd_ad'         => '',
            'customer_id'    => '',
            'staff_id'       => auth()->id(),
            'department_id'  => auth()->user()->department_id ?? '',
            'signed_at'      => date('Y-m-d'),
            'submitted_at'   => '',
            'value'          => 0,
            'commission'     => 0,
            'revenue'        => 0,
            'province'       => '',
            'info_source'    => 'MỚI',
            'payment_method' => 'Sau ký',
            'loai_dich_vu'   => '',
            'status'         => 'ĐANG THỰC HIỆN',
            'renewal_status' => '',
            'is_offset'      => false,
            'has_room_fund'  => false,
            'is_overdue'     => false,
            'notes'          => '',
        ];
        $this->selectedDoc = null;
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = ContractConsulting::with(['customer', 'staff', 'department'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_ad', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when(auth()->user()->hasRole('kinh-doanh'), fn($q) => $q->where('staff_id', auth()->id()))
            ->when(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', auth()->id())));

        if ($this->filter['signed_from'])    $query->whereDate('signed_at', '>=', $this->filter['signed_from']);
        if ($this->filter['signed_to'])      $query->whereDate('signed_at', '<=', $this->filter['signed_to']);
        if ($this->filter['submitted_from']) $query->whereDate('submitted_at', '>=', $this->filter['submitted_from']);
        if ($this->filter['submitted_to'])   $query->whereDate('submitted_at', '<=', $this->filter['submitted_to']);
        if ($this->filter['province'])       $query->where('province', $this->filter['province']);
        if ($this->filter['department_id'])  $query->where('department_id', $this->filter['department_id']);
        if ($this->filter['info_source'])    $query->where('info_source', $this->filter['info_source']);
        if ($this->filter['payment_method']) $query->where('payment_method', $this->filter['payment_method']);
        if ($this->filter['status'])         $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status']) $query->where('renewal_status', $this->filter['renewal_status']);
        if ($this->filter['is_offset'])      $query->where('is_offset', true);
        if ($this->filter['has_room_fund'])  $query->where('has_room_fund', true);
        if ($this->filter['is_overdue'])     $query->where('is_overdue', true);
        if ($this->filter['loai_dich_vu'])   $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);

        $docs           = $query->latest()->get();
        $title          = 'Hợp đồng tư vấn';
        $showFinancials = !auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, 'HopDong_TuVan_' . now()->format('d_m_Y') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function render()
    {
        $query = ContractConsulting::with(['customer', 'staff', 'department', 'assignments.user'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_ad', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when(auth()->user()->hasRole('kinh-doanh'),
                fn($q) => $q->where('staff_id', auth()->id()))
            ->when(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']),
                fn($q) => $q->whereHas('assignments', fn($sq) => $sq->where('user_id', auth()->id())));

        if ($this->filter['signed_from'])    $query->whereDate('signed_at', '>=', $this->filter['signed_from']);
        if ($this->filter['signed_to'])      $query->whereDate('signed_at', '<=', $this->filter['signed_to']);
        if ($this->filter['submitted_from']) $query->whereDate('submitted_at', '>=', $this->filter['submitted_from']);
        if ($this->filter['submitted_to'])   $query->whereDate('submitted_at', '<=', $this->filter['submitted_to']);
        if ($this->filter['province'])       $query->where('province', $this->filter['province']);
        if ($this->filter['department_id'])  $query->where('department_id', $this->filter['department_id']);
        if ($this->filter['info_source'])    $query->where('info_source', $this->filter['info_source']);
        if ($this->filter['payment_method']) $query->where('payment_method', $this->filter['payment_method']);
        if ($this->filter['status'])         $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status']) $query->where('renewal_status', $this->filter['renewal_status']);
        if ($this->filter['is_offset'])      $query->where('is_offset', true);
        if ($this->filter['has_room_fund'])  $query->where('has_room_fund', true);
        if ($this->filter['is_overdue'])     $query->where('is_overdue', true);
        if ($this->filter['loai_dich_vu'])   $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);

        $docs = $query->latest()->paginate(10);

        return view('livewire.admin.contracts.contract-consulting-manager', [
            'docs'                 => $docs,
            'customers'            => Customer::orderBy('name')->get(),
            'staffs'               => User::orderBy('name')->get(),
            'departments'          => Department::all(),
            'assignable_users'     => User::whereHas('roles', fn($q) =>
                $q->whereIn('name', ['tu-van', 'kinh-doanh', 'ky-thuat']))->orderBy('name')->get(),
            'provinces' => \App\Support\VietnamProvinces::list(),
            'all_statuses'         => ContractConsulting::whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status')->toArray(),
            'renewal_statuses'     => ContractConsulting::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'loai_dich_vu_options' => ContractConsulting::SERVICE_TYPES,
            'payment_methods' => ['Sau ký', 'Trước ký'],
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Hợp đồng tư vấn']);
    }
}
