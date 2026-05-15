<?php

namespace App\Actions\Contracts;

use App\Enums\Role;
use App\Models\ContractWaste;
use App\Models\User;

final class UpsertContractWasteAction
{
    /**
     * Tạo mới hoặc cập nhật ContractWaste.
     *
     * Business rules:
    * - Khi tạo mới: shd_cxl/shd_bc/ncc_payment/link sheet luôn null/0 (kế toán cập nhật sau)
    * - Khi sửa: chỉ kế toán mới được thay đổi shd_cxl/shd_bc/ncc_payment/link sheet
     *
     * @return array{0: ContractWaste, 1: string}
     */
    public function execute(array $data, User $actor, ?ContractWaste $existing = null): array
    {
        $isAccountant = $actor->hasRole(Role::KE_TOAN->value);

        if ($existing) {
            if (!$isAccountant) {
                $data['shd_cxl']     = $existing->shd_cxl;
                $data['shd_bc']      = $existing->shd_bc;
                $data['ncc_payment'] = $existing->ncc_payment;
                $data['ncc_payment_sheet_url'] = $existing->ncc_payment_sheet_url;
                $data['ncc_payment_updated_at'] = $existing->ncc_payment_updated_at;
            } else {
                $nccChanged = (int) ($data['ncc_payment'] ?? 0) !== (int) $existing->ncc_payment
                    || (string) ($data['ncc_payment_sheet_url'] ?? '') !== (string) ($existing->ncc_payment_sheet_url ?? '');

                $data['ncc_payment_updated_at'] = $nccChanged
                    ? now()
                    : $existing->ncc_payment_updated_at;
            }

            $existing->update($data);
            return [$existing, 'Cập nhật thành công'];
        }

        $data['shd_cxl']     = null;
        $data['shd_bc']      = null;
        $data['ncc_payment'] = 0;
        $data['ncc_payment_sheet_url'] = null;
        $data['ncc_payment_updated_at'] = null;

        $contract = ContractWaste::create($data);
        return [$contract, 'Tạo mới thành công'];
    }
}
