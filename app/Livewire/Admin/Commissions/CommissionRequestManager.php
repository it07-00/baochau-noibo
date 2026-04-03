<?php

namespace App\Livewire\Admin\Commissions;

use App\Models\CommissionRequest;
use Livewire\Component;
use Livewire\WithPagination;

class CommissionRequestManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $contractTypeFilter = '';
    public $perPage = 10;

    protected $listeners = ['deleteConfirmed' => 'delete'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingContractTypeFilter()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $request = CommissionRequest::findOrFail($id);
        $request->delete();
        $this->dispatch('swal:success', ['message' => 'Xóa yêu cầu thành công!']);
    }

    public function render()
    {
        $query = CommissionRequest::with(['contract.customer', 'contract.staff', 'user']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('receiver_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('contract', function($qc) {
                      $qc->where('shd_bc', 'like', '%' . $this->search . '%')
                         ->orWhereHas('customer', function($qcust) {
                             $qcust->where('name', 'like', '%' . $this->search . '%');
                         });
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->contractTypeFilter) {
            $query->where('contract_type', $this->contractTypeFilter);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.admin.commissions.commission-request-manager', [
            'requests'      => $requests,
            'contractTypes' => CommissionRequest::CONTRACT_TYPE_LABELS,
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Yêu cầu chi hoa hồng']);
    }
}
