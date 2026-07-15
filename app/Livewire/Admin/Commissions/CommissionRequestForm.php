<?php

namespace App\Livewire\Admin\Commissions;

use App\Enums\CommissionRequestStatus;
use App\Enums\ContractType;
use App\Enums\Permission;
use App\Enums\Role;
use App\Livewire\Concerns\CleanMoneyInput;
use App\Models\CommissionRequest;
use App\Services\CommissionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CommissionRequestForm extends Component
{
    use CleanMoneyInput;

    public $requestId;

    public $contract_type = '';

    public $contract_id = '';

    public $manual_contract_number = '';

    public bool $manualContractEntry = false;

    public $receiver_name;

    public $receiver_phone;

    public $bank_account;

    public $bank_code = '';

    public $bank_number = '';

    public $amount;

    public $referrer_info;

    public $notes;

    public $selectedSavedAccountId = '';

    public array $banks = [];

    public function mount($id = null)
    {
        $this->banks = $this->loadBanks();

        if ($id) {
            abort_if(auth()->check() && auth()->user()->hasRole(Role::KE_TOAN->value), 403, 'Kế toán không được sửa yêu cầu chi hoa hồng.');

            $canEditAnyRequest = auth()->check()
                && auth()->user()->can(Permission::COMMISSIONS_EDIT->value);

            $request = CommissionRequest::query()
                ->when(! $canEditAnyRequest, fn ($query) => $query->where('user_id', auth()->id()))
                ->findOrFail($id);
            abort_if(
                in_array($request->status, [
                    CommissionRequestStatus::DA_DUYET->value,
                    CommissionRequestStatus::DA_CHI->value,
                ], true),
                403,
                'Không thể chỉnh sửa yêu cầu đã được duyệt hoặc đã chi.'
            );

            $this->requestId = $request->id;
            $this->contract_type = $this->normalizeContractType($request->contract_type);
            $this->contract_id = $request->contract_id;
            $this->manual_contract_number = $request->manual_contract_number ?: '';
            $this->manualContractEntry = filled($request->manual_contract_number) && ! $request->contract_id;
            $this->receiver_name = $this->cleanReceiverName($request->receiver_name);
            $this->receiver_phone = $request->receiver_phone;
            $this->bank_account = $request->bank_account;
            $this->bank_code = $request->bank_code ?: '';
            $this->bank_number = $request->bank_number ?: '';
            $this->amount = $request->amount;
            $this->referrer_info = $request->referrer_info;
            $this->notes = $request->status === CommissionRequestStatus::TU_CHOI->value ? '' : $request->notes;
        }
    }

    public function updatedReceiverName($value)
    {
        $this->receiver_name = $this->cleanReceiverName($value);
    }

    private function cleanReceiverName($value): string
    {
        if (! $value) {
            return '';
        }

        return strtoupper(Str::ascii((string) $value));
    }

    public function updatedSelectedSavedAccountId($value)
    {
        if (! $value) {
            return;
        }

        $account = CommissionRequest::where('user_id', auth()->id())->find($value);
        if ($account) {
            $this->receiver_name = $this->cleanReceiverName($account->receiver_name);
            $this->receiver_phone = $account->receiver_phone;
            $this->bank_account = $account->bank_account;
            $this->bank_code = $account->bank_code ?: '';
            $this->bank_number = $account->bank_number ?: '';
        }
    }

    public function getVietQrUrl(): string
    {
        if (! $this->hasValidVietQrAccount()) {
            return '';
        }

        $cleanAmount = 0;
        if ($this->amount) {
            $cleanAmount = (int) preg_replace('/\D+/', '', (string) $this->amount);
        }

        $contractShd = trim((string) $this->manual_contract_number) ?: 'Hoa hong';
        if (! $this->manualContractEntry && $this->contract_id && $this->contract_type) {
            $contractClass = $this->contract_type;
            if (class_exists($contractClass)) {
                $contract = $contractClass::find($this->contract_id);
                if ($contract && isset($contract->shd_bc)) {
                    $contractShd = $contract->shd_bc;
                }
            }
        }

        $receiverName = rawurlencode(strtoupper(Str::ascii($this->receiver_name ?: '')));
        $description = rawurlencode("Chi hoa hong HD {$contractShd}");

        return "https://img.vietqr.io/image/{$this->bank_code}-{$this->bank_number}-compact2.png?amount={$cleanAmount}&addInfo={$description}&accountName={$receiverName}";
    }

    public function hasValidVietQrAccount(): bool
    {
        $bankNumber = preg_replace('/\D+/', '', (string) $this->bank_number);

        return filled($this->bank_code)
            && $bankNumber !== '';
    }

    public function updatedContractType()
    {
        $this->contract_type = $this->normalizeContractType($this->contract_type);
        $this->contract_id = '';
    }

    public function updatedManualContractEntry(bool $value): void
    {
        if ($value) {
            $this->contract_id = '';
        } else {
            $this->manual_contract_number = '';
        }
    }

    private function normalizeContractType(?string $type): string
    {
        if (! $type) {
            return '';
        }

        $ct = ContractType::tryFrom($type);
        if ($ct) {
            return $ct->modelClass();
        }

        if (ContractType::fromModelClass($type)) {
            return $type;
        }

        return '';
    }

    private function getSelectedContractModelClass(): ?string
    {
        $normalizedType = $this->normalizeContractType($this->contract_type);

        return $normalizedType && class_exists($normalizedType) ? $normalizedType : null;
    }

    public function save($exit = false)
    {
        if ($this->requestId && auth()->check() && auth()->user()->hasRole(Role::KE_TOAN->value)) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Kế toán không được sửa yêu cầu chi hoa hồng.']);

            return;
        }

        if ($this->requestId) {
            $existing = CommissionRequest::findOrFail($this->requestId);
            if (in_array($existing->status, [
                CommissionRequestStatus::DA_DUYET->value,
                CommissionRequestStatus::DA_CHI->value,
            ], true)) {
                $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Không thể chỉnh sửa yêu cầu đã được duyệt hoặc đã chi.']);

                return;
            }
        }

        if ($this->requestId) {
            $isOwner = $existing->user_id === auth()->id();
            $hasEditPermission = auth()->user()->can(Permission::COMMISSIONS_EDIT->value);
            abort_unless($isOwner || $hasEditPermission, 403);
        } else {
            abort_unless(auth()->user()->can('commissions.create'), 403);
        }

        $this->cleanMoneyProperties(['amount']);

        $allowedTypes = ContractType::modelClasses();

        $this->validate([
            'contract_type' => ['required', 'string', Rule::in($allowedTypes)],
            'contract_id' => [Rule::requiredIf(! $this->manualContractEntry), 'nullable', 'integer'],
            'manual_contract_number' => [Rule::requiredIf($this->manualContractEntry), 'nullable', 'string', 'max:100'],
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'nullable|string|max:30',
            'bank_account' => 'nullable|string|max:100',
            'bank_code' => 'nullable|string|max:20',
            'bank_number' => ['nullable', 'string', 'max:50', 'regex:/^\d+$/'],
            'amount' => 'required|numeric|min:0',
            'referrer_info' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ], [
            'contract_type.required' => 'Vui lòng chọn loại hợp đồng.',
            'contract_type.in' => 'Loại hợp đồng không hợp lệ.',
            'contract_id.required' => 'Vui lòng chọn số hợp đồng.',
            'manual_contract_number.required' => 'Vui lòng nhập số hợp đồng.',
            'bank_number.regex' => 'Số tài khoản chỉ được gồm các chữ số.',
            'receiver_name.required' => 'Vui lòng nhập tên người nhận.',
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.min' => 'Số tiền không được âm.',
        ]);

        $modelClass = $this->getSelectedContractModelClass();
        if (! $this->manualContractEntry && (! $modelClass || ! $modelClass::query()->whereKey($this->contract_id)->exists())) {
            $this->addError('contract_id', 'Số hợp đồng không thuộc loại hợp đồng đã chọn.');

            return;
        }

        $data = [
            'contract_type' => $this->contract_type,
            'contract_id' => $this->manualContractEntry ? null : $this->contract_id,
            'manual_contract_number' => $this->manualContractEntry
                ? trim((string) $this->manual_contract_number)
                : null,
            'receiver_name' => $this->cleanReceiverName($this->receiver_name),
            'receiver_phone' => $this->receiver_phone,
            'bank_account' => $this->bank_account,
            'bank_code' => $this->bank_code ?: null,
            'bank_number' => $this->bank_number ? preg_replace('/\D+/', '', (string) $this->bank_number) : null,
            'amount' => $this->amount,
            'referrer_info' => $this->referrer_info,
            'notes' => $this->notes,
            'user_id' => auth()->id(),
        ];

        if ($this->requestId) {
            $existing = CommissionRequest::findOrFail($this->requestId);
            if ($existing->status === CommissionRequestStatus::TU_CHOI->value) {
                $data['status'] = CommissionRequestStatus::DU_CHI->value;
                $data['processed_at'] = null;
            }
            app(CommissionService::class)->updateRequest($existing, $data, auth()->user());
            $this->dispatch('swal:success', ['message' => 'Cập nhật yêu cầu thành công!']);
        } else {
            $data['status'] = CommissionRequestStatus::DU_CHI->value;
            app(CommissionService::class)->createRequest($data, auth()->user());
            $this->dispatch('swal:success', ['message' => 'Thêm mới yêu cầu thành công!']);
        }

        if ($exit) {
            return redirect()->route('app.commissions.index');
        }

        if (! $this->requestId) {
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

        $savedAccounts = CommissionRequest::where('user_id', auth()->id())
            ->whereNotNull('receiver_name')
            ->where(function ($q) {
                $q->whereNotNull('bank_number')
                    ->orWhereNotNull('bank_account');
            })
            ->latest()
            ->get()
            ->map(function ($item) {
                $item->receiver_name = $this->cleanReceiverName($item->receiver_name);

                return $item;
            })
            ->unique(function ($item) {
                return $item->receiver_name.'_'.($item->bank_number ?: $item->bank_account);
            })
            ->values();

        return view('livewire.admin.commissions.commission-request-form', [
            'contracts' => $contracts,
            'contractTypes' => ContractType::labelMap(),
            'savedAccounts' => $savedAccounts,
        ])->layout('admin.layouts.app', ['title' => $this->requestId ? 'Chỉnh sửa Yêu cầu chi hoa hồng' : 'Thêm mới Yêu cầu chi hoa hồng']);
    }

    private function loadBanks(): array
    {
        return Cache::remember('vietqr_banks_list', 86400, function () {
            try {
                $response = Http::timeout(5)->get('https://api.vietqr.io/v2/banks');
                if ($response->successful() && isset($response->json()['data'])) {
                    $banksData = $response->json()['data'];
                    $formattedBanks = [];
                    foreach ($banksData as $bank) {
                        $code = $bank['code'] ?? '';
                        $shortName = $bank['shortName'] ?? ($bank['short_name'] ?? '');
                        if ($code && $shortName) {
                            $formattedBanks[$code] = $shortName.' ('.$code.')';
                        }
                    }
                    if (! empty($formattedBanks)) {
                        asort($formattedBanks);

                        return $formattedBanks;
                    }
                }
            } catch (\Throwable $e) {
                // Silent fallback
            }

            return $this->getStaticBanksList();
        });
    }

    private function getStaticBanksList(): array
    {
        $static = [
            'ICB' => 'VietinBank (ICB)',
            'VCB' => 'Vietcombank (VCB)',
            'BIDV' => 'BIDV',
            'VBA' => 'Agribank (VBA)',
            'OCB' => 'OCB',
            'MB' => 'MBBank (MB)',
            'TCB' => 'Techcombank (TCB)',
            'ACB' => 'ACB',
            'VPB' => 'VPBank (VPB)',
            'TPB' => 'TPBank (TPB)',
            'STB' => 'Sacombank (STB)',
            'HDB' => 'HDBank (HDB)',
            'VCCB' => 'VietCapitalBank (VCCB)',
            'SCB' => 'SCB',
            'VIB' => 'VIB',
            'SHB' => 'SHB',
            'EIB' => 'Eximbank (EIB)',
            'MSB' => 'MSB',
            'CAKE' => 'CAKE',
            'Ubank' => 'Ubank',
            'VTLMONEY' => 'ViettelMoney (VTLMONEY)',
            'TIMO' => 'Timo (TIMO)',
            'VNPTMONEY' => 'VNPTMoney (VNPTMONEY)',
            'SGICB' => 'SaigonBank (SGICB)',
            'BAB' => 'BacABank (BAB)',
            'momo' => 'MoMo (momo)',
            'PVDB' => 'PVcomBank Pay (PVDB)',
            'PVCB' => 'PVcomBank (PVCB)',
            'MBV' => 'MBV',
            'NCB' => 'NCB',
            'SHBVN' => 'ShinhanBank (SHBVN)',
            'ABB' => 'ABBANK (ABB)',
            'VAB' => 'VietABank (VAB)',
            'NAB' => 'NamABank (NAB)',
            'PGB' => 'PGBank (PGB)',
            'VIETBANK' => 'VietBank (VIETBANK)',
            'BVB' => 'BaoVietBank (BVB)',
            'SEAB' => 'SeABank (SEAB)',
            'COOPBANK' => 'COOPBANK',
            'LPB' => 'LPBank (LPB)',
            'KLB' => 'KienLongBank (KLB)',
            'KBank' => 'KBank',
            'MAFC' => 'MAFC',
            'HLBVN' => 'HongLeong (HLBVN)',
            'KEBHANAHN' => 'KEBHANAHN',
            'KEBHANAHCM' => 'KEBHanaHCM',
            'CITIBANK' => 'Citibank (CITIBANK)',
            'CBB' => 'CBBank (CBB)',
            'CIMB' => 'CIMB',
            'DBS' => 'DBSBank (DBS)',
            'Vikki' => 'Vikki',
            'VBSP' => 'VBSP',
            'GPB' => 'GPBank (GPB)',
            'KBHCM' => 'KookminHCM (KBHCM)',
            'KBHN' => 'KookminHN (KBHN)',
            'WVN' => 'Woori (WVN)',
            'VRB' => 'VRB',
            'HSBC' => 'HSBC',
            'IBK - HN' => 'IBKHN (IBK - HN)',
            'IBK - HCM' => 'IBKHCM (IBK - HCM)',
            'IVB' => 'IndovinaBank (IVB)',
            'UOB' => 'UnitedOverseas (UOB)',
            'NHB HN' => 'Nonghyup (NHB HN)',
            'SCVN' => 'StandardChartered (SCVN)',
            'PBVN' => 'PublicBank (PBVN)',
        ];
        asort($static);

        return $static;
    }
}
