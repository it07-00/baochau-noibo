<?php

namespace App\Livewire\Admin\Commissions;

use App\Models\CommissionRequest;
use App\Models\ContractWaste;
use App\Models\ContractConsulting;
use App\Models\ContractProject;
use App\Models\ContractCommercial;
use App\Models\ContractSustainability;
use App\Models\ContractEnergy;
use Livewire\Component;
use App\Livewire\Concerns\CleanMoneyInput;

class CommissionRequestForm extends Component
{
    use CleanMoneyInput;
    public $requestId;
    public $contract_type = '';
    public $contract_id = '';
    public $receiver_name;
    public $receiver_phone;
    public $bank_account;
    public $amount;
    public $referrer_info;
    public $notes;

    public function mount($id = null)
    {
        if ($id) {
            $request = CommissionRequest::findOrFail($id);
            $this->requestId = $request->id;
            $this->contract_type = $request->contract_type;
            $this->contract_id = $request->contract_id;
            $this->receiver_name = $request->receiver_name;
            $this->receiver_phone = $request->receiver_phone;
            $this->bank_account = $request->bank_account;
            $this->amount = $request->amount;
            $this->referrer_info = $request->referrer_info;
            $this->notes = $request->notes;
        }
    }

    public function updatedContractType()
    {
        $this->contract_id = '';
    }

    public function save($exit = false)
    {
        abort_unless(
            auth()->user()->can($this->requestId ? 'commissions.edit' : 'commissions.create'),
            403
        );

        $this->cleanMoneyProperties(['amount']);

        $allowedTypes = implode(',', array_keys(CommissionRequest::CONTRACT_TYPES));

        $this->validate([
            'contract_type' => 'required|string|in:' . $allowedTypes,
            'contract_id'   => 'required|integer',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'nullable|string|max:30',
            'bank_account'  => 'nullable|string|max:50',
            'amount'        => 'required|numeric|min:0',
            'referrer_info' => 'nullable|string|max:500',
            'notes'         => 'nullable|string|max:2000',
        ], [
            'contract_type.in' => 'Loại hợp đồng không hợp lệ.',
            'receiver_name.required' => 'Vui lòng nhập tên người nhận.',
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.min' => 'Số tiền không được âm.',
        ]);

        $data = [
            'contract_type' => $this->contract_type,
            'contract_id'   => $this->contract_id,
            'receiver_name' => $this->receiver_name,
            'receiver_phone' => $this->receiver_phone,
            'bank_account' => $this->bank_account,
            'amount' => $this->amount,
            'referrer_info' => $this->referrer_info,
            'notes' => $this->notes,
            'user_id' => auth()->id(),
        ];

        if ($this->requestId) {
            CommissionRequest::findOrFail($this->requestId)->update($data);
            $this->dispatch('swal:success', ['message' => 'Cập nhật yêu cầu thành công!']);
        } else {
            CommissionRequest::create($data);
            $this->dispatch('swal:success', ['message' => 'Thêm mới yêu cầu thành công!']);
        }

        if ($exit) {
            return redirect()->route('app.commissions.index');
        }

        if (!$this->requestId) {
            $this->reset();
        }
    }

    public function render()
    {
        $contracts = collect();
        if ($this->contract_type) {
            $modelClass = $this->contract_type;
            if (class_exists($modelClass)) {
                $contracts = $modelClass::with('customer')->orderBy('shd_bc', 'desc')->get();
            }
        }

        return view('livewire.admin.commissions.commission-request-form', [
            'contracts'     => $contracts,
            'contractTypes' => CommissionRequest::CONTRACT_TYPE_LABELS,
        ])->layout('admin.layouts.app', ['title' => $this->requestId ? 'Chỉnh sửa Yêu cầu chi hoa hồng' : 'Thêm mới Yêu cầu chi hoa hồng']);
    }
}
