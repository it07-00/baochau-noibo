<?php

namespace App\Actions\Quotations;

use App\Enums\Role;
use App\Models\Quotation;
use App\Models\User;

final class UpsertQuotationAction
{
    /**
     * Tạo mới hoặc cập nhật Quotation.
     * Business rule: KinhDoanh chỉ được gán chính mình khi tạo mới.
     *
     * @return array{0: Quotation, 1: string}
     */
    public function execute(array $data, User $actor, ?Quotation $existing = null): array
    {
        if ($existing) {
            $existing->update($data);
            return [$existing, 'Cập nhật thành công'];
        }

        if ($actor->hasRole(Role::KINH_DOANH->value)) {
            $data['staff_id'] = $actor->id;
        }

        $quotation = Quotation::create($data);
        return [$quotation, 'Tạo mới thành công'];
    }
}
