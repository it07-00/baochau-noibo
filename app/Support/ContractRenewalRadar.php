<?php

namespace App\Support;

use App\Enums\ContractRenewalStatus;
use App\Enums\Role;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ContractRenewalRadar
{
    private const CONTRACTS = [
        [
            'model' => ContractWaste::class,
            'label' => 'Chất thải',
            'route' => 'app.contracts.waste.index',
        ],
        [
            'model' => ContractLegal::class,
            'label' => 'Pháp lý & Hồ sơ MT',
            'route' => 'app.contracts.consulting.index',
        ],
        [
            'model' => ContractTechnical::class,
            'label' => 'Ứng phó sự cố',
            'route' => 'app.contracts.project.index',
        ],
        [
            'model' => ContractResearch::class,
            'label' => 'Nghiên cứu & công nghệ',
            'route' => 'app.contracts.commercial.index',
        ],
        [
            'model' => ContractSustainability::class,
            'label' => 'Phát triển bền vững',
            'route' => 'app.contracts.sustainability.index',
        ],
        [
            'model' => ContractEmission::class,
            'label' => 'Phát thải & năng lượng',
            'route' => 'app.contracts.energy.index',
        ],
    ];

    public static function visibleFor(User $viewer, int $days = 30, int $limit = 5): Collection
    {
        if (! self::shouldShowFor($viewer)) {
            return collect();
        }

        $staffIds = self::visibleStaffIds($viewer);
        if ($staffIds->isEmpty()) {
            return collect();
        }

        $today = today()->startOfDay();
        $windowEnd = $today->copy()->addDays($days)->endOfDay();
        $closedStatuses = [
            ContractRenewalStatus::DA_TAI_KY->value,
            ContractRenewalStatus::KHONG_TAI_KY->value,
            ContractRenewalStatus::ROT_TAI_KY->value,
        ];

        return collect(self::CONTRACTS)
            ->flatMap(function (array $meta) use ($closedStatuses, $staffIds, $today, $windowEnd) {
                $modelClass = $meta['model'];

                return $modelClass::query()
                    ->with(['customer:id,name', 'staff:id,name'])
                    ->whereNotNull('signed_at')
                    ->whereDate('signed_at', '<=', $today)
                    ->whereIn('staff_id', $staffIds)
                    ->where(function ($query) {
                        $query->where('is_renewal', false)->orWhereNull('is_renewal');
                    })
                    ->where(function ($query) use ($closedStatuses) {
                        $query->whereNull('renewal_status')
                            ->orWhereNotIn('renewal_status', $closedStatuses);
                    })
                    ->get(['id', 'customer_id', 'staff_id', 'signed_at', 'renewal_status', 'shd_bc', 'shd_cxl', 'value'])
                    ->map(function ($contract) use ($meta, $today, $windowEnd) {
                        $renewalDate = self::nextAnniversary($contract->signed_at, $today);

                        if (! $renewalDate->betweenIncluded($today, $windowEnd)) {
                            return null;
                        }

                        $daysLeft = (int) $today->diffInDays($renewalDate);

                        return [
                            'id' => $contract->id,
                            'type' => $meta['label'],
                            'url' => route($meta['route']),
                            'customer' => $contract->customer?->name ?? 'Khách hàng chưa đặt tên',
                            'staff' => $contract->staff?->name ?? 'Chưa phân công',
                            'contract_number' => $contract->shd_bc ?: ($contract->shd_cxl ?: 'HĐ #'.$contract->id),
                            'signed_at' => $contract->signed_at,
                            'renewal_date' => $renewalDate,
                            'days_left' => $daysLeft,
                            'days_label' => $daysLeft === 0 ? 'Hôm nay' : 'Còn '.$daysLeft.' ngày',
                            'value' => (float) ($contract->value ?? 0),
                            'renewal_status' => $contract->renewal_status ?: ContractRenewalStatus::CHUA_DEN_HAN->value,
                        ];
                    })
                    ->filter();
            })
            ->sortBy(['days_left', 'renewal_date'])
            ->take($limit)
            ->values();
    }

    private static function shouldShowFor(User $viewer): bool
    {
        return $viewer->hasAnyRole([
            Role::KINH_DOANH->value,
            Role::TP_KINH_DOANH->value,
            Role::GIAM_DOC->value,
        ]);
    }

    private static function visibleStaffIds(User $viewer): Collection
    {
        if (
            $viewer->hasRole(Role::KINH_DOANH->value)
            && ! $viewer->hasAnyRole([Role::TP_KINH_DOANH->value, Role::GIAM_DOC->value])
        ) {
            return collect([$viewer->id]);
        }

        return User::role([Role::KINH_DOANH->value, Role::TP_KINH_DOANH->value])
            ->where('is_active', true)
            ->pluck('id');
    }

    private static function nextAnniversary(Carbon $signedAt, Carbon $today): Carbon
    {
        $renewalDate = $signedAt->copy()->startOfDay()->addYearNoOverflow();

        while ($renewalDate->lt($today)) {
            $renewalDate->addYearNoOverflow();
        }

        return $renewalDate;
    }
}
