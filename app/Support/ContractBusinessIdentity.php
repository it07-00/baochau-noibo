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
}
