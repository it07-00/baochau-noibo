<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\ContractAssignment;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\User;
use App\Support\ContractBusinessIdentity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ContractProgressRankingService
{
    private const CONTRACT_MODELS = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    public function forRole(Role $role, int $year): Collection
    {
        return User::role($role->value)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => $this->forUser($user, $year))
            ->filter(fn (array $ranking): bool => $ranking['total'] > 0);
    }

    private function forUser(User $user, int $year): array
    {
        $workItems = collect();

        foreach (self::CONTRACT_MODELS as $model) {
            $assignedContractIds = ContractAssignment::query()
                ->where('user_id', $user->id)
                ->where('assignable_type', $model)
                ->pluck('assignable_id')
                ->unique();

            if ($assignedContractIds->isEmpty()) {
                continue;
            }

            $contracts = $model::query()
                ->whereIn('id', $assignedContractIds)
                ->whereYear(DB::raw('COALESCE(submitted_at, signed_at)'), $year)
                ->get(['id', 'shd_bc', 'workflow_status']);

            if ($contracts->isEmpty()) {
                continue;
            }

            $finishedContractIds = ContractWorkflowStep::query()
                ->where('contract_type', $model)
                ->where('step_name', 'finished')
                ->whereIn('contract_id', $contracts->pluck('id'))
                ->pluck('contract_id')
                ->flip();

            foreach ($contracts as $contract) {
                $workItems->push([
                    'key' => ContractBusinessIdentity::key($contract),
                    'finished' => ($contract->workflow_status ?? '') === 'finished'
                        || $finishedContractIds->has($contract->id),
                ]);
            }
        }

        $contractGroups = $workItems->groupBy('key');
        $total = $contractGroups->count();
        $finished = $contractGroups
            ->filter(fn (Collection $contracts): bool => $contracts->contains('finished', true))
            ->count();

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url ?? null,
            'total' => $total,
            'finished' => $finished,
            'pct' => $total > 0 ? round($finished / $total * 100) : 0,
        ];
    }
}
