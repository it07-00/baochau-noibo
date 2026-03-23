<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractCommercial;
use App\Models\Customer;
use App\Models\User;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class ContractCommercialManager extends Component
{
    use WithPagination;

    public $search = '';
    
    // Filters
    public $filter = [
        'signed_from' => '',
        'signed_to' => '',
        'submitted_from' => '',
        'submitted_to' => '',
        'province' => '',
        'department_id' => '',
        'info_source' => '',
        'payment_method' => '',
        'status' => '',
        'renewal_status' => '',
        'is_offset' => false,
        'has_room_fund' => false,
        'is_overdue' => false,
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
            'submitted_from' => '',
            'submitted_to' => '',
            'province' => '',
            'department_id' => '',
            'info_source' => '',
            'payment_method' => '',
            'status' => '',
            'renewal_status' => '',
            'is_offset' => false,
            'has_room_fund' => false,
            'is_overdue' => false,
        ];
        $this->resetPage();
    }

    public function viewDetail($id)
    {
        $this->selectedDoc = ContractCommercial::with(['customer', 'staff', 'department'])->find($id);
        if ($this->selectedDoc) {
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    public function render()
    {
        $query = ContractCommercial::with(['customer', 'staff', 'department'])
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('shd_ad', 'like', '%'.$this->search.'%')
                      ->orWhereHas('customer', function($csq) {
                          $csq->where('name', 'like', '%'.$this->search.'%');
                      });
                });
            });

        // Apply filters
        if ($this->filter['signed_from']) $query->whereDate('signed_at', '>=', $this->filter['signed_from']);
        if ($this->filter['signed_to']) $query->whereDate('signed_at', '<=', $this->filter['signed_to']);
        if ($this->filter['submitted_from']) $query->whereDate('submitted_at', '>=', $this->filter['submitted_from']);
        if ($this->filter['submitted_to']) $query->whereDate('submitted_at', '<=', $this->filter['submitted_to']);

        if ($this->filter['province']) $query->where('province', $this->filter['province']);
        if ($this->filter['department_id']) $query->where('department_id', $this->filter['department_id']);
        if ($this->filter['info_source']) $query->where('info_source', $this->filter['info_source']);
        if ($this->filter['payment_method']) $query->where('payment_method', $this->filter['payment_method']);
        if ($this->filter['status']) $query->where('status', $this->filter['status']);
        if ($this->filter['renewal_status']) $query->where('renewal_status', $this->filter['renewal_status']);
        
        if ($this->filter['is_offset']) $query->where('is_offset', true);
        if ($this->filter['has_room_fund']) $query->where('has_room_fund', true);
        if ($this->filter['is_overdue']) $query->where('is_overdue', true);

        $docs = $query->latest()->paginate(10);
        
        return view('livewire.admin.contracts.contract-commercial-manager', [
            'docs' => $docs,
            'departments' => Department::all(),
            'provinces' => ContractCommercial::whereNotNull('province')->where('province', '!=', '')->distinct()->pluck('province')->toArray(),
            'all_statuses' => ContractCommercial::whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status')->toArray(),
            'renewal_statuses' => ContractCommercial::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Hợp đồng thương mại']);
    }
}
