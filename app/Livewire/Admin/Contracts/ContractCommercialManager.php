<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractCommercial;
use App\Models\Customer;
use App\Models\User;
use App\Models\Department;
use App\Models\Quotation;
use Livewire\Component;
use Livewire\WithPagination;

class ContractCommercialManager extends Component
{
    use WithPagination;

    public $search = '';
    public bool $showModal = false;
    public bool $isEditing = false;
    public $showDetail = false;
    public $selectedDoc = null;
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

    public function mount(): void
    {
        if ($this->quotation_id) {
            $quotation = Quotation::find($this->quotation_id);
            if ($quotation) {
                $customer = Customer::firstOrCreate(
                    ['name' => $quotation->company_name ?? ''],
                    ['name' => $quotation->company_name ?? '']
                );
                $this->formData['customer_id']    = $customer->id;
                $this->formData['value']          = $quotation->value ?? 0;
                $this->formData['commission']     = $quotation->commission ?? 0;
                $this->formData['revenue']        = $quotation->revenue ?? 0;
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
        $this->selectedDoc = ContractCommercial::findOrFail($id);
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
        $this->validate([
            'formData.customer_id' => 'required',
            'formData.staff_id'    => 'required',
            'formData.value'       => 'required|numeric',
        ]);

        $data = collect($this->formData)->map(fn($v) => $v === '' ? null : $v)->toArray();

        if ($this->isEditing && $this->selectedDoc) {
            $this->selectedDoc->update($data);
        } else {
            ContractCommercial::create($data);
        }

        $this->dispatch('closeFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Lưu hợp đồng thành công!']);
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        ContractCommercial::findOrFail($id)->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa hợp đồng!']);
    }

    public function viewDetail(int $id): void
    {
        $this->selectedDoc = ContractCommercial::with(['customer', 'staff', 'department'])->find($id);
        if ($this->selectedDoc) {
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
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

    public function render()
    {
        $query = ContractCommercial::with(['customer', 'staff', 'department'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('shd_ad', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($csq) {
                            $csq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            });

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

        return view('livewire.admin.contracts.contract-commercial-manager', [
            'docs'               => $docs,
            'customers'          => Customer::orderBy('name')->get(),
            'staffs'             => User::orderBy('name')->get(),
            'departments'        => Department::all(),
            'provinces'          => ContractCommercial::whereNotNull('province')->where('province', '!=', '')->distinct()->pluck('province')->toArray(),
            'all_statuses'       => ContractCommercial::whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status')->toArray(),
            'renewal_statuses'   => ContractCommercial::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'loai_dich_vu_options' => ContractCommercial::SERVICE_TYPES,
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Hợp đồng thương mại']);
    }
}
