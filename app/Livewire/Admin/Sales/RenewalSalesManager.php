<?php

namespace App\Livewire\Admin\Sales;

use App\Models\SalesRenewal;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\Concerns\CleanMoneyInput;

class RenewalSalesManager extends Component
{
    use WithPagination, WithFileUploads, CleanMoneyInput;

    public $search = '';
    public $filter_month = '';
    public $filter_status = '';

    // Form fields
    public $isEditing = false;
    public $selectedId = null;
    public $contract_number, $sales_month, $sales_value = 0, $commission = 0, $sales_percentage = 0, $sales_amount = 0, $status, $notes, $file;

    protected $queryString = ['search', 'filter_month', 'filter_status'];

    public function calculateSales()
    {
        $this->cleanMoneyProperties(['sales_value', 'commission', 'sales_amount']);
        $this->sales_amount = ($this->sales_value * $this->sales_percentage) / 100;
    }

    public function render()
    {
        $query = SalesRenewal::query()
            ->when($this->search, fn($q) => $q->where('contract_number', 'like', '%' . $this->search . '%'))
            ->when($this->filter_month, fn($q) => $q->whereDate('sales_month', $this->filter_month . '-01'))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->orderBy('created_at', 'desc');

        return view('livewire.admin.sales.renewal-sales-manager', [
            'items' => $query->paginate(15),
            'statuses' => SalesRenewal::distinct()->pluck('status')->filter()->values(),
        ])->layout('admin.layouts.app');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'renewal-modal');
    }

    public function resetForm()
    {
        $this->reset(['selectedId', 'contract_number', 'sales_month', 'sales_value', 'commission', 'sales_percentage', 'sales_amount', 'status', 'notes', 'file', 'isEditing']);
        $this->sales_month = now()->format('Y-m');
    }

    public function save()
    {
        abort_unless(
            auth()->user()->can($this->selectedId ? 'renewal-sales.edit' : 'renewal-sales.create'),
            403
        );

        $this->cleanMoneyProperties(['sales_value', 'commission', 'sales_amount']);

        $this->validate([
            'contract_number' => 'required|string|max:255',
            'sales_month' => 'required|date_format:Y-m',
            'sales_value' => 'nullable|numeric|min:0|max:999999999999999',
            'commission' => 'nullable|numeric|min:0|max:999999999999999',
            'sales_percentage' => 'nullable|numeric|min:0|max:100',
            'sales_amount' => 'nullable|numeric|min:0|max:999999999999999',
            'status' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ], [
            'contract_number.required' => 'Vui lòng nhập số hợp đồng.',
            'sales_month.required'     => 'Vui lòng chọn tháng doanh số.',
            'sales_month.date_format'  => 'Tháng doanh số không đúng định dạng (YYYY-MM).',
            'sales_value.min'          => 'Giá trị doanh số không được âm.',
            'commission.min'           => 'Hoa hồng không được âm.',
            'file.mimes'               => 'File phải có định dạng: pdf, doc, docx, xls, xlsx, jpg, jpeg, png.',
            'file.max'                 => 'File không được vượt quá 10MB.',
        ]);

        $data = [
            'contract_number' => $this->contract_number,
            'sales_month' => $this->sales_month . '-01',
            'sales_value' => $this->sales_value,
            'commission' => $this->commission,
            'sales_percentage' => $this->sales_percentage,
            'sales_amount' => $this->sales_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'user_id' => auth()->id(),
        ];

        if ($this->file) {
            $uploadDisk = config('filesystems.upload_disk', 'public');
            $data['file_path'] = $this->file->store('sales/renewal', $uploadDisk);
        }

        if ($this->selectedId) {
            SalesRenewal::findOrFail($this->selectedId)->update($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Cập nhật thành công!']]);
        } else {
            SalesRenewal::create($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Lưu thành công!']]);
        }

        $this->dispatch('close-modal', 'renewal-modal');
        $this->resetPage();
    }

    public function edit($id)
    {
        $item = SalesRenewal::findOrFail($id);
        $this->selectedId = $id;
        $this->contract_number = $item->contract_number;
        $this->sales_month = $item->sales_month->format('Y-m');
        $this->sales_value = $item->sales_value;
        $this->commission = $item->commission;
        $this->sales_percentage = $item->sales_percentage;
        $this->sales_amount = $item->sales_amount;
        $this->status = $item->status;
        $this->notes = $item->notes;

        $this->isEditing = true;
        $this->dispatch('open-modal', 'renewal-modal');
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->can('renewal-sales.delete'), 403);

        SalesRenewal::findOrFail($id)->delete();
        $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Xóa thành công!']]);
    }
}
