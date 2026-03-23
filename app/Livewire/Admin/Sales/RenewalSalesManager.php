<?php

namespace App\Livewire\Admin\Sales;

use App\Models\RenewalSales;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class RenewalSalesManager extends Component
{
    use WithPagination, WithFileUploads;

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
        $this->sales_amount = ($this->sales_value * $this->sales_percentage) / 100;
    }

    public function render()
    {
        $query = RenewalSales::query()
            ->when($this->search, fn($q) => $q->where('contract_number', 'like', '%' . $this->search . '%'))
            ->when($this->filter_month, fn($q) => $q->whereDate('sales_month', $this->filter_month . '-01'))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->orderBy('created_at', 'desc');

        return view('livewire.admin.sales.renewal-sales-manager', [
            'items' => $query->paginate(15),
            'statuses' => RenewalSales::distinct()->pluck('status')->filter()->values(),
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
        $this->validate([
            'contract_number' => 'required',
            'sales_month' => 'required',
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
            $data['file_path'] = $this->file->store('sales/renewal', 'public');
        }

        if ($this->selectedId) {
            RenewalSales::find($this->selectedId)->update($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Cập nhật thành công!']]);
        } else {
            RenewalSales::create($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Lưu thành công!']]);
        }

        $this->dispatch('close-modal', 'renewal-modal');
        $this->resetPage();
    }

    public function edit($id)
    {
        $item = RenewalSales::findOrFail($id);
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
        RenewalSales::find($id)->delete();
        $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Xóa thành công!']]);
    }
}
