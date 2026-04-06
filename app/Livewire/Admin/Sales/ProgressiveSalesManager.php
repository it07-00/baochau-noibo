<?php

namespace App\Livewire\Admin\Sales;

use App\Models\ContractPaymentSchedule;
use Livewire\Component;
use Livewire\WithPagination;
use App\Livewire\Concerns\CleanMoneyInput;

class ProgressiveSalesManager extends Component
{
    use WithPagination, CleanMoneyInput;

    public $search = '';
    public $filter_month = '';
    public $filter_status = '';

    // Edit form fields
    public $selectedId = null;
    public $installment_name, $percentage = 0, $amount = 0, $due_date, $paid_date, $paid_amount = 0, $status, $notes;

    protected $queryString = ['search', 'filter_month', 'filter_status'];

    public function paginationView()
    {
        return 'livewire.admin.users.pagination';
    }

    public function render()
    {
        $query = ContractPaymentSchedule::with('contract')
            ->when($this->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('installment_name', 'like', "%$search%")
                        ->orWhereHasMorph('contract', array_values(ContractPaymentSchedule::MODEL_MAP), function ($cq, $type) use ($search) {
                            $cq->where('shd_bc', 'like', "%$search%");
                            if ($type === \App\Models\ContractWaste::class) {
                                $cq->orWhere('shd_cxl', 'like', "%$search%");
                            }
                        });
                });
            })
            ->when($this->filter_month, fn($q) => $q->whereYear('due_date', substr($this->filter_month, 0, 4))
                ->whereMonth('due_date', substr($this->filter_month, 5, 2)))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->orderBy('due_date', 'desc');

        $total = (clone $query)->sum('amount');

        return view('livewire.admin.sales.progressive-sales-manager', [
            'items'    => $query->paginate(20),
            'total'    => $total,
            'statuses' => ContractPaymentSchedule::STATUSES,
        ])->layout('admin.layouts.app', ['title' => 'Doanh số theo tiến độ']);
    }

    public function edit($id)
    {
        $item = ContractPaymentSchedule::findOrFail($id);
        $this->selectedId = $id;
        $this->installment_name = $item->installment_name;
        $this->percentage       = $item->percentage;
        $this->amount           = $item->amount;
        $this->due_date         = $item->due_date?->format('Y-m-d');
        $this->paid_date        = $item->paid_date?->format('Y-m-d');
        $this->paid_amount      = $item->paid_amount;
        $this->status           = $item->status;
        $this->notes            = $item->notes;
        $this->dispatch('open-modal', 'progressive-modal');
    }

    public function save()
    {
        abort_unless(auth()->user()->can('progressive-sales.edit'), 403);

        $this->cleanMoneyProperties(['amount', 'paid_amount']);

        $this->validate([
            'installment_name' => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0|max:999999999999999',
            'paid_amount'      => 'nullable|numeric|min:0|max:999999999999999',
            'percentage'       => 'nullable|numeric|min:0|max:100',
            'due_date'         => 'nullable|date',
            'paid_date'        => 'nullable|date',
            'status'           => 'required|in:pending,partial,paid,overdue',
            'notes'            => 'nullable|string|max:1000',
        ], [
            'installment_name.required' => 'Vui lòng nhập tên đợt.',
            'amount.required'           => 'Vui lòng nhập số tiền.',
            'amount.min'                => 'Số tiền không được âm.',
            'status.in'                 => 'Trạng thái không hợp lệ.',
        ]);

        $schedule = ContractPaymentSchedule::findOrFail($this->selectedId);

        $schedule->update([
            'installment_name' => $this->installment_name,
            'percentage'       => $this->percentage,
            'amount'           => $this->amount,
            'due_date'         => $this->due_date ?: null,
            'paid_date'        => $this->paid_date ?: null,
            'paid_amount'      => $this->paid_amount,
            'status'           => $this->status,
            'notes'            => $this->notes,
        ]);

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Cập nhật thành công!']);
        $this->dispatch('close-modal', 'progressive-modal');
        $this->reset(['selectedId', 'installment_name', 'percentage', 'amount', 'due_date', 'paid_date', 'paid_amount', 'status', 'notes']);
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->can('progressive-sales.delete'), 403);

        ContractPaymentSchedule::findOrFail($id)->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Xóa thành công!']);
        $this->resetPage();
    }
}
