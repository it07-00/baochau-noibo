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
     * - Khi tạo mới: shd_cxl/shd_bc luôn null (kế toán cập nhật sau)
     * - Khi sửa: chỉ kế toán mới được thay đổi shd_cxl/shd_bc
     *
     * @return array{0: ContractWaste, 1: string}
     */
    public function execute(array $data, User $actor, ?ContractWaste $existing = null): array
    {
        $isAccountant = $actor->hasRole(Role::KE_TOAN->value);

        if ($existing) {
            if (!$isAccountant) {
                $data['shd_cxl'] = $existing->shd_cxl;
                $data['shd_bc']  = $existing->shd_bc;
            }

            $existing->update($data);
            return [$existing, 'Cập nhật thành công'];
        }

        $data['shd_cxl'] = null;
        $data['shd_bc']  = null;

        $contract = ContractWaste::create($data);
        return [$contract, 'Tạo mới thành công'];
    }
}
