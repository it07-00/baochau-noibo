<?php

namespace App\Livewire\Admin\Contracts;

use App\Livewire\Concerns\CleanMoneyInput;
use App\Models\ContractPaymentSchedule;
use Livewire\Component;

class ContractPaymentScheduleManager extends Component
{
    use CleanMoneyInput;

    public string $contractType;
    public int $contractId;
    public string $contractModelClass = '';
    public bool $showForm = false;
    public bool $isEditing = false;
    public ?int $editingId = null;
    public bool $canManage = false;

    public array $form = [
        'installment_name' => '',
        'percentage'       => 0,
        'amount'           => 0,
        'due_date'         => '',
        'paid_date'        => '',
        'paid_amount'      => 0,
        'status'           => 'pending',
        'notes'            => '',
    ];

    protected function rules(): array
    {
        return [
            'form.installment_name' => 'required|string|max:255',
            'form.amount'           => 'required|numeric|min:0|max:999999999999999',
            'form.percentage'       => 'nullable|numeric|min:0|max:100',
            'form.due_date'         => 'nullable|date',
            'form.paid_date'        => 'nullable|date',
            'form.paid_amount'      => 'nullable|numeric|min:0|max:999999999999999',
            'form.status'           => 'required|in:pending,partial,paid,overdue',
            'form.notes'            => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'form.installment_name.required' => 'Vui lòng nhập tên đợt thanh toán.',
            'form.amount.required'           => 'Vui lòng nhập số tiền.',
            'form.amount.min'                => 'Số tiền không được âm.',
            'form.status.in'                 => 'Trạng thái không hợp lệ.',
        ];
    }

    public function mount(string $contractType, int $contractId): void
    {
        $this->contractType       = $contractType;
        $this->contractId         = $contractId;
        $this->contractModelClass = ContractPaymentSchedule::MODEL_MAP[$contractType] ?? '';
        $this->calculatePermissions();
    }

    private function calculatePermissions(): void
    {
        $user = auth()->user();
        if ($user->hasAnyRole(['tu-van', 'ky-thuat'])) {
            $this->canManage = false;
            return;
        }

        if ($user->hasRole('tp-kinh-doanh')) {
            $parent = $this->contractModelClass::find($this->contractId);
            $this->canManage = $parent && $parent->staff_id === $user->id;
            return;
        }

        $this->canManage = true;
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()->can('payment-schedules.edit'), 403);
        $schedule = ContractPaymentSchedule::findOrFail($id);
        $this->editingId = $id;
        $this->isEditing = true;
        $this->form = [
            'installment_name' => $schedule->installment_name ?? '',
            'percentage'       => $schedule->percentage,
            'amount'           => $schedule->amount,
            'due_date'         => $schedule->due_date?->format('Y-m-d') ?? '',
            'paid_date'        => $schedule->paid_date?->format('Y-m-d') ?? '',
            'paid_amount'      => $schedule->paid_amount,
            'status'           => $schedule->status,
            'notes'            => $schedule->notes ?? '',
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        if (auth()->user()->hasRole('tp-kinh-doanh')) {
            $parent = $this->contractModelClass::find($this->contractId);
            abort_if(!$parent || $parent->staff_id !== auth()->id(), 403);
        }

        abort_unless(
            auth()->user()->can($this->isEditing ? 'payment-schedules.edit' : 'payment-schedules.create'),
            403
        );

        $this->cleanMoneyFields($this->form, ['amount', 'paid_amount']);
        $this->validate();

        $data = collect($this->form)->map(fn($v) => $v === '' ? null : $v)->toArray();
        $data['contract_type'] = $this->contractModelClass;
        $data['contract_id']   = $this->contractId;

        if ($this->isEditing && $this->editingId) {
            ContractPaymentSchedule::findOrFail($this->editingId)->update($data);
        } else {
            $nextNumber = ContractPaymentSchedule::where('contract_type', $this->contractModelClass)
                ->where('contract_id', $this->contractId)
                ->max('installment_number') + 1;

            $data['installment_number'] = $nextNumber;
            $data['created_by']         = auth()->id();
            ContractPaymentSchedule::create($data);
        }

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã lưu đợt thanh toán!']);
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        if (auth()->user()->hasRole('tp-kinh-doanh')) {
            $parent = $this->contractModelClass::find($this->contractId);
            abort_if(!$parent || $parent->staff_id !== auth()->id(), 403);
        }
        abort_unless(auth()->user()->can('payment-schedules.delete'), 403);

        ContractPaymentSchedule::findOrFail($id)->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa đợt thanh toán!']);
    }

    public function markPaid(int $id): void
    {
        if (auth()->user()->hasRole('tp-kinh-doanh')) {
            $parent = $this->contractModelClass::find($this->contractId);
            abort_if(!$parent || $parent->staff_id !== auth()->id(), 403);
        }
        abort_unless(auth()->user()->can('payment-schedules.edit'), 403);

        $schedule = ContractPaymentSchedule::findOrFail($id);
        $schedule->update([
            'status'      => 'paid',
            'paid_amount' => $schedule->amount,
            'paid_date'   => now()->toDateString(),
        ]);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã đánh dấu thanh toán!']);
    }

    private function resetForm(): void
    {
        $this->form = [
            'installment_name' => '',
            'percentage'       => 0,
            'amount'           => 0,
            'due_date'         => '',
            'paid_date'        => '',
            'paid_amount'      => 0,
            'status'           => 'pending',
            'notes'            => '',
        ];
        $this->isEditing = false;
        $this->editingId = null;
        $this->showForm  = false;
    }

    public function render()
    {
        $schedules = ContractPaymentSchedule::where('contract_type', $this->contractModelClass)
            ->where('contract_id', $this->contractId)
            ->orderBy('installment_number')
            ->get();

        $totalAmount = $schedules->sum('amount');
        $totalPaid   = $schedules->sum('paid_amount');

        return view('livewire.admin.contracts.contract-payment-schedule-manager', [
            'schedules'   => $schedules,
            'totalAmount' => $totalAmount,
            'totalPaid'   => $totalPaid,
            'statuses'    => ContractPaymentSchedule::STATUSES,
        ]);
    }
}
