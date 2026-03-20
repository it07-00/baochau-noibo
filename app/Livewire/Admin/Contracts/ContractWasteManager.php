<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Handler;
use App\Models\User;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class ContractWasteManager extends Component
{
    use WithPagination;

    public $search = '';
    
    // Filters
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
        'province_id' => '',
        'is_offset' => false,
        'is_overdue' => false,
        'department_id' => '',
        'source' => '',
        'payment_method' => '',
        'service_type' => '',
        'waste_type' => '',
        'status' => '',
        'renewal_status' => '',
        'voucher_status' => '',
    ];

    public $showDetail = false;
    public $selectedDoc = null;

    protected $queryString = ['search'];

    public function updatedSearch()
    {
        $this->resetPage();
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
            'province_id' => '',
            'is_offset' => false,
            'is_overdue' => false,
            'department_id' => '',
            'source' => '',
            'payment_method' => '',
            'service_type' => '',
            'waste_type' => '',
            'status' => '',
            'renewal_status' => '',
            'voucher_status' => '',
        ];
        $this->resetPage();
    }

    public function viewDetail($id)
    {
        $this->selectedDoc = ContractWaste::with(['customer', 'handler', 'staff', 'department'])->find($id);
        if ($this->selectedDoc) {
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->selectedDoc = null;
    }

    public function render()
    {
        $query = ContractWaste::with(['customer', 'handler', 'staff', 'department'])
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('shd_cxl', 'like', '%'.$this->search.'%')
                      ->orWhere('shd_ad', 'like', '%'.$this->search.'%')
                      ->orWhereHas('customer', function($csq) {
                          $csq->where('name', 'like', '%'.$this->search.'%');
                      });
                });
            });

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
        if ($this->filter['is_offset'] ?? null) $query->where('is_offset', true);
        if ($this->filter['is_overdue'] ?? null) $query->where('is_overdue', true);
        if ($this->filter['status'] ?? null) $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status'] ?? null) $query->where('renewal_status', $this->filter['renewal_status']);
        if ($this->filter['service_type'] ?? null) $query->where('service_type', $this->filter['service_type']);
        if ($this->filter['waste_type'] ?? null) $query->where('waste_type', $this->filter['waste_type']);
        if ($this->filter['voucher_status'] ?? null) $query->where('voucher_status', $this->filter['voucher_status']);
        if ($this->filter['source'] ?? null) $query->where('source', $this->filter['source']);
        if ($this->filter['payment_method'] ?? null) $query->where('payment_method', $this->filter['payment_method']);

        $docs = $query->latest()->paginate(10);
        
        return view('livewire.admin.contracts.contract-waste-manager', [
            'docs' => $docs,
            'handlers' => Handler::all(),
            'departments' => Department::all(),
            // Dynamic filter options
            'service_types' => ContractWaste::whereNotNull('service_type')->where('service_type', '!=', '')->distinct()->pluck('service_type')->toArray(),
            'waste_types' => ContractWaste::whereNotNull('waste_type')->where('waste_type', '!=', '')->distinct()->pluck('waste_type')->toArray(),
            'all_statuses' => ContractWaste::whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status')->toArray(),
            'renewal_statuses' => ContractWaste::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
            'voucher_statuses' => ContractWaste::whereNotNull('voucher_status')->where('voucher_status', '!=', '')->distinct()->pluck('voucher_status')->toArray(),
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Hợp đồng chất thải']);
    }
}
