<?php

namespace App\Livewire\Admin\Commissions;

use App\Models\CommissionRequest;
use App\Models\ContractWaste;
use App\Models\ContractLegal;
use App\Models\ContractTechnical;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractEmission;
use App\Models\User;
use App\Notifications\CommissionRequestSubmittedNotification;
use Livewire\Component;
use Illuminate\Validation\Rule;
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
            abort_if(auth()->check() && auth()->user()->hasRole('ke-toan'), 403, 'Kế toán không được sửa yêu cầu chi hoa hồng.');

            $request = CommissionRequest::findOrFail($id);
            $this->requestId = $request->id;
            $this->contract_type = $this->normalizeContractType($request->contract_type);
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
        $this->contract_type = $this->normalizeContractType($this->contract_type);
        $this->contract_id = '';
    }

    private function normalizeContractType(?string $type): string
    {
        if (!$type) {
            return '';
        }

        if (isset(CommissionRequest::CONTRACT_TYPES[$type])) {
            return CommissionRequest::CONTRACT_TYPES[$type];
        }

        if (array_key_exists($type, CommissionRequest::CONTRACT_TYPE_LABELS)) {
            return $type;
        }

        return '';
    }

    private function getSelectedContractModelClass(): ?string
    {
        $normalizedType = $this->normalizeContractType($this->contract_type);

        return $normalizedType && class_exists($normalizedType) ? $normalizedType : null;
    }

    private function notifyAccountantsForNewRequest(CommissionRequest $request): void
    {
        $creator = auth()->user();
        if (!$creator) {
            return;
        }

        $contractLabel = (string) ($request->contract?->shd_bc ?: ('#' . $request->id));

        $recipients = User::role('ke-toan')->get()->unique('id');

        foreach ($recipients as $recipient) {
            /** @var User $recipient */
            if ($recipient->id === $creator->id) {
                continue;
            }

            $recipient->notify(new CommissionRequestSubmittedNotification(
                requesterName: (string) $creator->name,
                contractLabel: $contractLabel,
                amount: (string) $request->amount,
                requestId: (int) $request->id,
            ));
        }
    }

    public function save($exit = false)
    {
        if ($this->requestId && auth()->check() && auth()->user()->hasRole('ke-toan')) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Kế toán không được sửa yêu cầu chi hoa hồng.']);
            return;
        }

        abort_unless(
            auth()->user()->can($this->requestId ? 'commissions.edit' : 'commissions.create'),
            403
        );

        $this->cleanMoneyProperties(['amount']);

        $allowedTypes = array_keys(CommissionRequest::CONTRACT_TYPE_LABELS);

        $this->validate([
            'contract_type' => ['required', 'string', Rule::in($allowedTypes)],
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

        $modelClass = $this->getSelectedContractModelClass();
        if (!$modelClass || !$modelClass::query()->whereKey($this->contract_id)->exists()) {
            $this->addError('contract_id', 'Số hợp đồng không thuộc loại hợp đồng đã chọn.');
            return;
        }

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
            $createdRequest = CommissionRequest::create($data);
            $this->notifyAccountantsForNewRequest($createdRequest);
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
        $modelClass = $this->getSelectedContractModelClass();
        if ($modelClass) {
            $contracts = $modelClass::query()
                ->with('customer')
                ->whereNotNull('shd_bc')
                ->where('shd_bc', '!=', '')
                ->orderBy('shd_bc', 'desc')
                ->get();
        }

        return view('livewire.admin.commissions.commission-request-form', [
            'contracts'     => $contracts,
            'contractTypes' => CommissionRequest::CONTRACT_TYPE_LABELS,
        ])->layout('admin.layouts.app', ['title' => $this->requestId ? 'Chỉnh sửa Yêu cầu chi hoa hồng' : 'Thêm mới Yêu cầu chi hoa hồng']);
    }
}
