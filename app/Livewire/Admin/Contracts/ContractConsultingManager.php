<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\ContractConsulting;
use App\Models\Customer;
use App\Models\User;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ContractConsultingManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    
    // Workflow attributes
    public $workflow_comment = '';
    public $workflow_consultant_id = '';
    public $workflow_file = null;
    public $workflow_milestone = '';
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
    public $showModal = false;
    public $isEditing = false;
    public $selectedDoc = null;

    public $formData = [
        'shd_ad' => '',
        'customer_id' => '',
        'staff_id' => '',
        'department_id' => '',
        'signed_at' => '',
        'submitted_at' => '',
        'value' => 0,
        'commission' => 0,
        'revenue' => 0,
        'province' => '',
        'info_source' => '',
        'payment_method' => '',
        'notes' => '',
    ];

    protected $queryString = ['search', 'quotation_id'];
    public $quotation_id;

    public function mount()
    {
        if ($this->quotation_id) {
            $quotation = \App\Models\Quotation::find($this->quotation_id);
            if ($quotation) {
                // Find or create customer
                $customer = \App\Models\Customer::firstOrCreate(
                    ['name' => $quotation->company_name],
                    ['address' => $quotation->address]
                );
                
                $this->formData['customer_id'] = $customer->id;
                $this->formData['value'] = $quotation->original_value;
                $this->formData['commission'] = $quotation->commission_value;
                $this->formData['revenue'] = $quotation->total_value;
                $this->formData['staff_id'] = $quotation->staff_id;
                $this->formData['notes'] = $quotation->work_description;
                $this->formData['info_source'] = 'MỚI';
                
                $this->showModal = true;
                $this->dispatch('openFormModal');
            }
        }
    }



    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function edit($id)
    {
        $doc = ContractConsulting::findOrFail($id);
        $this->selectedDoc = $doc;
        $this->formData = $doc->toArray();
        // Format dates for input
        $this->formData['signed_at'] = $doc->signed_at ? $doc->signed_at->format('Y-m-d') : '';
        $this->formData['submitted_at'] = $doc->submitted_at ? $doc->submitted_at->format('Y-m-d') : '';
        
        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openFormModal');
    }

    public function save()
    {
        $this->validate([
            'formData.shd_ad' => 'required|unique:contract_consultings,shd_ad,' . ($this->isEditing ? $this->selectedDoc->id : 'NULL'),
            'formData.customer_id' => 'required',
            'formData.staff_id' => 'required',
            'formData.value' => 'required|numeric',
        ]);

        $data = collect($this->formData)->map(function ($value) {
            return $value === '' ? null : $value;
        })->toArray();

        if ($this->isEditing) {
            $this->selectedDoc->update($data);
            $msg = 'Cập nhật thành công';
        } else {
            $data['workflow_status'] = ContractConsulting::STATUS_DRAFT;
            ContractConsulting::create($data);
            $msg = 'Tạo mới thành công';
        }

        $this->showModal = false;
        $this->dispatch('closeFormModal');
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => $msg]);
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->formData = [
            'shd_ad' => '',
            'customer_id' => '',
            'staff_id' => auth()->id(),
            'department_id' => 5, // Default to Consulting
            'signed_at' => date('Y-m-d'),
            'submitted_at' => '',
            'value' => 0,
            'commission' => 0,
            'revenue' => 0,
            'province' => '',
            'info_source' => 'MỚI',
            'payment_method' => 'Sau ký',
            'notes' => '',
        ];
        $this->selectedDoc = null;
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

    public function delete($id)
    {
        $doc = ContractConsulting::findOrFail($id);
        $doc->delete();
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Đã xóa hợp đồng']);
    }

    public function viewDetail($id)
    {
        $this->selectedDoc = ContractConsulting::with(['customer', 'staff', 'department', 'consultant', 'workflowSteps.user', 'milestoneFiles.uploader'])->find($id);
        if ($this->selectedDoc) {
            $this->showDetail = true;
            $this->dispatch('openDetailModal');
        }
    }

    public function render()
    {
        $query = ContractConsulting::with(['customer', 'staff', 'department'])
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
        
        return view('livewire.admin.contracts.contract-consulting-manager', [
            'docs' => $docs,
            'departments' => Department::all(),
            'provinces' => ContractConsulting::whereNotNull('province')->where('province', '!=', '')->distinct()->pluck('province')->toArray(),
            'all_statuses' => ContractConsulting::whereNotNull('status')->where('status', '!=', '')->distinct()->pluck('status')->toArray(),
            'renewal_statuses' => ContractConsulting::whereNotNull('renewal_status')->where('renewal_status', '!=', '')->distinct()->pluck('renewal_status')->toArray(),
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Hợp đồng tư vấn']);
    }

    // Workflow Actions
    public function submitToAccounting()
    {
        $this->validateSelectedDoc();
        $this->selectedDoc->update(['workflow_status' => ContractConsulting::STATUS_PENDING_ACCOUNTING]);
        $this->logStep('Nộp duyệt', 'submit', 'Đã nộp cho kế toán kiểm tra');
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Đã nộp duyệt']);
    }

    public function verifyAccounting($isApprove)
    {
        $this->validateSelectedDoc();
        if ($isApprove) {
            $this->selectedDoc->update(['workflow_status' => ContractConsulting::STATUS_PENDING_DIRECTOR]);
            $this->logStep('Kế toán kiểm tra', 'approve', 'Số liệu đã OK. Chuyển GĐ ký');
        } else {
            $this->selectedDoc->update(['workflow_status' => ContractConsulting::STATUS_REJECTED_ACCOUNTING]);
            $this->logStep('Kế toán kiểm tra', 'reject', $this->workflow_comment);
        }
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Thao tác thành công']);
        $this->workflow_comment = '';
    }

    public function approveDirector($isApprove)
    {
        $this->validateSelectedDoc();
        if ($isApprove) {
            $this->selectedDoc->update(['workflow_status' => ContractConsulting::STATUS_APPROVED_DIRECTOR]);
            $this->logStep('Giám đốc phê duyệt', 'approve', 'Đã ký phê duyệt');
        } else {
            $this->selectedDoc->update(['workflow_status' => ContractConsulting::STATUS_REJECTED_DIRECTOR]);
            $this->logStep('Giám đốc phê duyệt', 'reject', $this->workflow_comment);
        }
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Thao tác thành công']);
        $this->workflow_comment = '';
    }

    public function assignConsultant()
    {
        $this->validateSelectedDoc();
        $this->validate(['workflow_consultant_id' => 'required']);
        $this->selectedDoc->update([
            'workflow_status' => ContractConsulting::STATUS_CONSULTANT_ASSIGNED,
            'consultant_id' => $this->workflow_consultant_id,
            'assigned_at' => now(),
            'manager_id' => auth()->id()
        ]);
        $this->logStep('TPKD Assign', 'assign', 'Đã gán cho ' . User::find($this->workflow_consultant_id)->name);
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Đã gán nhân viên']);
    }

    public function updateMilestone($milestone)
    {
        $this->validateSelectedDoc();
        $this->validate(['workflow_file' => 'required|file|max:10240']);

        $path = $this->workflow_file->store('contracts/consulting/' . $this->selectedDoc->id, 'public');
        
        $this->selectedDoc->milestoneFiles()->create([
            'milestone' => $milestone,
            'file_path' => $path,
            'uploader_id' => auth()->id()
        ]);

        $nextStatus = match($milestone) {
            'receiving' => ContractConsulting::STATUS_CONSULTING_RECEIVING,
            'survey' => ContractConsulting::STATUS_CONSULTING_SURVEY,
            'processing' => ContractConsulting::STATUS_CONSULTING_PROCESSING,
            'waiting_client' => ContractConsulting::STATUS_WAITING_CLIENT,
            'confirmed' => ContractConsulting::STATUS_CLIENT_CONFIRMED,
            'incident' => ContractConsulting::STATUS_INCIDENT,
        };

        $this->selectedDoc->update(['workflow_status' => $nextStatus]);
        
        $stepLabel = match($milestone) {
            'receiving' => 'Tiếp nhận',
            'survey' => 'Khảo sát',
            'processing' => 'Thực hiện tư vấn',
            'waiting_client' => 'Chờ KH duyệt',
            'confirmed' => 'KH xác nhận',
            'incident' => 'Sự cố',
        };

        $this->logStep($stepLabel, 'update', 'Cập nhật tiến độ: ' . $stepLabel);
        
        $this->workflow_file = null;
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Đã cập nhật']);
    }

    public function finalReview($isApprove)
    {
        $this->validateSelectedDoc();
        if ($isApprove) {
            $this->selectedDoc->update([
                'workflow_status' => ContractConsulting::STATUS_FINISHED,
                'completed_at' => now()
            ]);
            $this->logStep('Duyệt hoàn thành', 'approve', 'TPKD đã duyệt hoàn thành');
        } else {
            $this->selectedDoc->update(['workflow_status' => ContractConsulting::STATUS_REJECTED_FINAL_REVIEW]);
            $this->logStep('Duyệt hoàn thành', 'reject', $this->workflow_comment);
        }
        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => 'Thao tác thành công']);
        $this->workflow_comment = '';
    }

    // Helper methods
    private function validateSelectedDoc()
    {
        if (!$this->selectedDoc) {
            $this->selectedDoc = ContractConsulting::find($this->selectedDoc->id); // Refresh if needed
        }
    }

    private function logStep($stepName, $action, $comment = '')
    {
        $this->selectedDoc->workflowSteps()->create([
            'user_id' => auth()->id(),
            'step_name' => $stepName,
            'action' => $action,
            'comment' => $comment,
        ]);
    }
}
