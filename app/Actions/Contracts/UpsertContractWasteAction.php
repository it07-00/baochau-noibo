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
     * - Khi tạo mới: shd_cxl/shd_bc/ncc_payment luôn null/0 (kế toán cập nhật sau)
     * - Khi sửa: chỉ kế toán mới được thay đổi shd_cxl/shd_bc/ncc_payment
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
            }

            $existing->update($data);
            return [$existing, 'Cập nhật thành công'];
        }

        $data['shd_cxl']     = null;
        $data['shd_bc']      = null;
        $data['ncc_payment'] = 0;

        $contract = ContractWaste::create($data);
        return [$contract, 'Tạo mới thành công'];
    }
}
