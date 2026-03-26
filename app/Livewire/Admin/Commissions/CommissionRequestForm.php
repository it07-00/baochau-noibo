<?php

namespace App\Livewire\Admin\Commissions;

use App\Models\CommissionRequest;
use App\Models\ContractWaste;
use Livewire\Component;
use App\Livewire\Concerns\CleanMoneyInput;

class CommissionRequestForm extends Component
{
    use CleanMoneyInput;
    public $requestId;
    public $contract_waste_id;
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
            $this->contract_waste_id = $request->contract_waste_id;
            $this->receiver_name = $request->receiver_name;
            $this->receiver_phone = $request->receiver_phone;
            $this->bank_account = $request->bank_account;
            $this->amount = $request->amount;
            $this->referrer_info = $request->referrer_info;
            $this->notes = $request->notes;
        }
    }

    public function save($exit = false)
    {
        $this->cleanMoneyProperties(['amount']);

        $this->validate([
            'contract_waste_id' => 'required|exists:contract_wastes,id',
            'receiver_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $data = [
            'contract_waste_id' => $this->contract_waste_id,
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
        $contracts = ContractWaste::with('customer')->orderBy('shd_ad', 'desc')->get();

        return view('livewire.admin.commissions.commission-request-form', [
            'contracts' => $contracts
        ])->layout('admin.layouts.app', ['title' => $this->requestId ? 'Chỉnh sửa Yêu cầu chi hoa hồng' : 'Thêm mới Yêu cầu chi hoa hồng']);
    }
}
