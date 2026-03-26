<?php

namespace App\Livewire\Admin\Sales;

use App\Models\ProgressiveSales;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\CleanMoneyInput;

class ProgressiveSalesManager extends Component
{
    use WithPagination, CleanMoneyInput;

    public $search = '';
    public $filter_month = '';
    
    // Form fields
    public $isEditing = false;
    public $selectedId = null;
    public $contract_number, $sales_month, $milestone_name, $percentage = 0, $amount = 0, $status, $notes;

    protected $queryString = ['search', 'filter_month'];

    public function render()
    {
        $query = ProgressiveSales::query()
            ->when($this->search, fn($q) => $q->where('contract_number', 'like', '%' . $this->search . '%'))
            ->when($this->filter_month, fn($q) => $q->whereDate('sales_month', $this->filter_month . '-01'))
            ->orderBy('created_at', 'desc');

        return view('livewire.admin.sales.progressive-sales-manager', [
            'items' => $query->paginate(15),
        ])->layout('admin.layouts.app');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'progressive-modal');
    }

    public function resetForm()
    {
        $this->reset(['selectedId', 'contract_number', 'sales_month', 'milestone_name', 'percentage', 'amount', 'status', 'notes', 'isEditing']);
        $this->sales_month = now()->format('Y-m');
    }

    public function save()
    {
        $this->cleanMoneyProperties(['amount']);

        $this->validate([
            'contract_number' => 'required',
            'sales_month' => 'required',
            'milestone_name' => 'required',
        ]);

        $data = [
            'contract_number' => $this->contract_number,
            'sales_month' => $this->sales_month . '-01',
            'milestone_name' => $this->milestone_name,
            'percentage' => $this->percentage,
            'amount' => $this->amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'user_id' => auth()->id(),
        ];

        if ($this->selectedId) {
            ProgressiveSales::find($this->selectedId)->update($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Cập nhật thành công!']]);
        } else {
            ProgressiveSales::create($data);
            $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Lưu thành công!']]);
        }

        $this->dispatch('close-modal', 'progressive-modal');
        $this->resetPage();
    }

    public function edit($id)
    {
        $item = ProgressiveSales::findOrFail($id);
        $this->selectedId = $id;
        $this->contract_number = $item->contract_number;
        $this->sales_month = $item->sales_month->format('Y-m');
        $this->milestone_name = $item->milestone_name;
        $this->percentage = $item->percentage;
        $this->amount = $item->amount;
        $this->status = $item->status;
        $this->notes = $item->notes;
        
        $this->isEditing = true;
        $this->dispatch('open-modal', 'progressive-modal');
    }

    public function delete($id)
    {
        ProgressiveSales::find($id)->delete();
        $this->dispatch('swal:toast', [['type' => 'success', 'message' => 'Xóa thành công!']]);
    }
}
