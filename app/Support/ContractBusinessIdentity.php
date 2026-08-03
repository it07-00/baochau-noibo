<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

final class ContractBusinessIdentity
{
    public static function key(Model|array $contract): string
    {
        $baoChauNumber = trim((string) data_get($contract, 'shd_bc'));

        if ($baoChauNumber !== '') {
            return 'shd_bc:'.mb_strtolower($baoChauNumber, 'UTF-8');
        }

        $type = $contract instanceof Model
            ? $contract::class
            : (string) data_get($contract, 'source_key', 'contract');

        return 'record:'.$type.':'.data_get($contract, 'id');
    }

    public static function unique(Collection $contracts): Collection
    {
        return $contracts
            ->unique(fn (Model|array $contract): string => self::key($contract))
            ->values();
    }

    public static function groups(Collection $contracts): Collection
    {
        return $contracts->groupBy(
            fn (Model|array $contract): string => self::key($contract)
        );
    }

    public static function statusSummary(Collection $contracts): array
    {
        $groups = self::groups($contracts);
        $completed = $groups->filter(
            fn (Collection $rows): bool => $rows->contains(
                fn (Model|array $contract): bool => self::isCompleted($contract)
            )
        );
        $active = $groups
            ->reject(fn (Collection $rows, string $key): bool => $completed->has($key))
            ->filter(
                fn (Collection $rows): bool => $rows->contains(
                    fn (Model|array $contract): bool => self::isActive($contract)
                )
            );

        return [
            'total' => $groups->count(),
            'total_value' => (float) $contracts->sum('value'),
            'completed' => $completed->count(),
            'active' => $active->count(),
        ];
    }

    public static function paginate(Collection $contracts, int $perPage = 10): LengthAwarePaginator
    {
        $contracts = self::unique($contracts);
        $page = Paginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $contracts->forPage($page, $perPage)->values(),
            $contracts->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    private static function isCompleted(Model|array $contract): bool
    {
        if (data_get($contract, 'workflow_status') === 'finished') {
            return true;
        }

        return in_array(mb_strtolower(trim((string) data_get($contract, 'status')), 'UTF-8'), [
            mb_strtolower('HOÀN THÀNH', 'UTF-8'),
            mb_strtolower('Đã hoàn thành', 'UTF-8'),
            'finished',
        ], true);
    }

    private static function isActive(Model|array $contract): bool
    {
        return in_array(mb_strtolower(trim((string) data_get($contract, 'status')), 'UTF-8'), [
            mb_strtolower('ĐANG THỰC HIỆN', 'UTF-8'),
            mb_strtolower('PTH đang kiểm tra', 'UTF-8'),
        ], true);
    }
}
