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
    public $departmentFilter = '';
    public $perPage = 10;

    protected $listeners = ['deleteConfirmed' => 'delete'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $request = CommissionRequest::findOrFail($id);
        
        // Authorization check if needed
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
                      $qc->where('shd_ad', 'like', '%' . $this->search . '%')
                         ->orWhereHas('customer', function($qcust) {
                             $qcust->where('name', 'like', '%' . $this->search . '%');
                         });
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.admin.commissions.commission-request-manager', [
            'requests' => $requests
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Yêu cầu chi hoa hồng']);
    }
}
