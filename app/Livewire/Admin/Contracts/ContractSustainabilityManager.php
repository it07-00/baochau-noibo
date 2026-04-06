<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractSustainability;
use App\Models\Customer;
use App\Models\User;
use App\Models\Department;
use App\Models\Quotation;
use App\Models\ContractAssignment;
use App\Models\ContractProgressNote;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Livewire\Concerns\ContractValidation;
use App\Notifications\ContractAssignedNotification;
use App\Notifications\ContractProgressNoteNotification;

class ContractSustainabilityManager extends Component
{
    use WithPagination, CleanMoneyInput, ContractValidation;

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
        'shd_bc'         => '',
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
        'is_renewal'     => false,
        'parent_contract_id' => '',
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
        'loai_dich_vu'   => '',
        'status'         => '',
        'renewal_status' => '',
        'is_offset'      => false,
        'has_room_fund'  => false,
        'is_overdue'     => false,
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
        $this->selectedDoc = ContractSustainability::findOrFail($id);
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
        abort_unless(
            auth()->user()->can($this->isEditing ? 'contracts-sustainability.edit' : 'contracts-sustainability.create'),
            403
        );

        $this->cleanMoneyFields($this->formData, ['value', 'commission', 'revenue']);

        $this->validate($this->baseContractRules(), $this->contractValidationMessages());

        $data = collect($this->formData)->map(fn($v) => $v === '' ? null : $v)->toArray();

        if ($this->isEditing && $this->selectedDoc) {
            $this->selectedDoc->update($data);
        } else {
            ContractSustainability::create($data);
        }

        $this->dispatch('closeFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Lưu hợp đồng thành công!']);
        $this->resetForm();
    }

    public function updateStatus(int $id, string $status): void
    {
        abort_unless(auth()->user()->can('contracts-sustainability.edit'), 403);

        if (!in_array($status, ['ĐANG THỰC HIỆN', 'HOÀN THÀNH', 'ĐÃ HỦY'])) {
            return;
        }

        ContractSustainability::findOrFail($id)->update(['status' => $status]);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật tình trạng!']);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('contracts-sustainability.delete'), 403);

        ContractSustainability::findOrFail($id)->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hợp đồng!']);
    }

    public function viewDetail(int $id): void
    {
        $this->selectedDoc = ContractSustainability::with(['customer', 'staff', 'department', 'assignments.user', 'assignments.assigner'])->find($id);
        if ($this->selectedDoc) {
            $this->progressNotes = ContractProgressNote::where('contract_type', 'sustainability')
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
        $this->assignUserIds = ContractAssignment::where('assignable_type', ContractSustainability::class)
            ->where('assignable_id', $id)
            ->pluck('user_id')
            ->toArray();
        $this->dispatch('openAssignModal');
    }

    public function saveAssign(): void
    {
        ContractAssignment::where('assignable_type', ContractSustainability::class)
            ->where('assignable_id', $this->assignContractId)
            ->delete();
        foreach ($this->assignUserIds as $userId) {
            ContractAssignment::create([
                'assignable_type' => ContractSustainability::class,
                'assignable_id'   => $this->assignContractId,
                'user_id'         => (int) $userId,
                'assigned_by'     => auth()->id(),
            ]);
        }
        // Gửi thông báo đến users được giao
        $contract = ContractSustainability::with('customer')->find($this->assignContractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$this->assignContractId);
        foreach ($this->assignUserIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                $user->notify(new ContractAssignedNotification('sustainability', $this->assignContractId, $contractLabel, auth()->user()->name));
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
            'contract_type' => 'sustainability',
            'contract_id'   => $contractId,
            'user_id'       => auth()->id(),
            'note'          => $noteText,
        ]);
        $this->progressNote = '';
        $this->progressNotes = ContractProgressNote::where('contract_type', 'sustainability')
            ->where('contract_id', $contractId)
            ->with('user')
            ->latest()
            ->get();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã thêm ghi chú!']);

        $contract = ContractSustainability::with('customer')->find($contractId);
        $contractLabel = $contract?->shd_bc ?: ($contract?->customer?->name ?: 'HĐ #'.$contractId);
        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', ['giam-doc', 'quan-ly', 'it']))->get();
        if ($contract?->staff_id && $contract->staff_id !== auth()->id()) {
            $staff = User::find($contract->staff_id);
            if ($staff) $recipients->push($staff);
        }
        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->id !== auth()->id()) {
                $recipient->notify(new ContractProgressNoteNotification('sustainability', $contractId, $contractLabel, \Illuminate\Support\Str::limit($noteText, 50), auth()->user()->name));
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
            'loai_dich_vu'   => '',
            'status'         => '',
            'renewal_status' => '',
            'is_offset'      => false,
            'has_room_fund'  => false,
            'is_overdue'     => false,
        ];
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->formData = [
            'shd_bc'         => '',
            'customer_id'    => '',
            'staff_id'       => auth()->id(),
            'department_id'  => 3, // Phòng Kinh doanh
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
            'is_renewal'     => false,
            'parent_contract_id' => '',
        ];
        $this->selectedDoc = null;
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = ContractSustainability::with(['customer', 'staff', 'department'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%' . $this->search . '%')
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
        if ($this->filter['loai_dich_vu'])   $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);
        if ($this->filter['status'])         $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status']) $query->where('renewal_status', $this->filter['renewal_status']);
        if ($this->filter['is_offset'])      $query->where('is_offset', true);
        if ($this->filter['has_room_fund'])  $query->where('has_room_fund', true);
        if ($this->filter['is_overdue'])     $query->where('is_overdue', true);

        $docs           = $query->latest()->get();
        $title          = 'HĐ Phát triển bền vững';
        $showFinancials = !auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']);

        return response()->streamDownload(function () use ($docs, $title, $showFinancials) {
            echo view('admin.contracts.export-excel', compact('docs', 'title', 'showFinancials'));
        }, 'HopDong_PhatTrienBenVung_' . now()->format('d_m_Y') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function render()
    {
        $query = ContractSustainability::with(['customer', 'staff', 'department', 'assignments.user'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_bc', 'like', '%' . $this->search . '%')
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
        if ($this->filter['loai_dich_vu'])   $query->where('loai_dich_vu', $this->filter['loai_dich_vu']);
        if ($this->filter['status'])         $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status']) $query->where('renewal_status', $this->filter['renewal_status']);
        if ($this->filter['is_offset'])      $query->where('is_offset', true);
        if ($this->filter['has_room_fund'])  $query->where('has_room_fund', true);
        if ($this->filter['is_overdue'])     $query->where('is_overdue', true);

        $docs = $query->latest()->paginate(10);

        return view('livewire.admin.contracts.contract-sustainability-manager', [
            'docs'               => $docs,
            'customers'          => Customer::orderBy('name')->get(),
            'staffs'             => User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get(),
            'departments'        => Department::all(),
            'assignable_users'   => \App\Models\User::whereHas('roles', fn($q) =>
                $q->whereIn('name', ['tu-van', 'ky-thuat']))->orderBy('name')->get(),
            'provinces' => \App\Support\VietnamProvinces::list(),
            'all_statuses'       => ContractSustainability::whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status')->toArray(),
            'renewal_statuses'   => ContractSustainability::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'loai_dich_vu_options' => ContractSustainability::SERVICE_TYPES,
            'payment_methods' => ['Sau ký', 'Trước ký'],
            'info_sources' => ContractSustainability::whereNotNull('info_source')->where('info_source', '!=', '')->distinct()->pluck('info_source')->toArray(),
            'parentContracts' => ContractSustainability::with('customer')->where('is_renewal', false)->orderByDesc('id')->get(),
        ])->layout('admin.layouts.app', ['title' => 'HĐ TV & BC PTBV']);
    }
}
