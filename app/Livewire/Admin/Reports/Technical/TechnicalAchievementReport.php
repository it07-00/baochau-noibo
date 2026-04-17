<?php

namespace App\Livewire\Admin\Reports\Technical;

use App\Models\ContractAssignment;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\ContractWorkflowStep;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TechnicalAchievementReport extends Component
{
    public int $year;

    public array $years = [];

    private const CONTRACT_MODELS = [
        ContractWaste::class,
        ContractLegal::class,
        ContractTechnical::class,
        ContractResearch::class,
        ContractSustainability::class,
        ContractEmission::class,
    ];

    public function mount(): void
    {
        $this->year  = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    private function buildRankings(): Collection
    {
        $staffs = User::role('ky-thuat')->orderBy('name')->get();

        return $staffs->map(function (User $user) {
            $total    = 0;
            $finished = 0;

            foreach (self::CONTRACT_MODELS as $model) {
                // Get IDs of contracts for this year (avoid whereHas on morphTo)
                $contractIds = $model::whereYear(
                    DB::raw('COALESCE(submitted_at, signed_at)'),
                    $this->year
                )->pluck('id');

                if ($contractIds->isEmpty()) {
                    continue;
                }

                $cnt = ContractAssignment::where('user_id', $user->id)
                    ->where('assignable_type', $model)
                    ->whereIn('assignable_id', $contractIds)
                    ->count();
                $total += $cnt;

                $finishedIds = ContractWorkflowStep::where('contract_type', $model)
                    ->where('step_name', 'finished')
                    ->pluck('contract_id');

                $done = ContractAssignment::where('user_id', $user->id)
                    ->where('assignable_type', $model)
                    ->whereIn('assignable_id', $contractIds)
                    ->whereIn('assignable_id', $finishedIds)
                    ->count();
                $finished += $done;
            }

            return [
                'user_id'    => $user->id,
                'name'       => $user->name,
                'avatar_url' => $user->avatar_url ?? null,
                'total'      => $total,
                'finished'   => $finished,
                'pct'        => $total > 0 ? round($finished / $total * 100) : 0,
            ];
        })->filter(fn ($r) => $r['total'] > 0);
    }

    public function render()
    {
        $all = $this->buildRankings();

        $completionRankings = $all->sortByDesc('finished')->values();
        $rateRankings       = $all->sortByDesc('pct')->values();

        return view('livewire.admin.reports.technical.technical-achievement-report', [
            'completionRankings' => $completionRankings,
            'rateRankings'       => $rateRankings,
            'years'              => $this->years,
            'year'               => $this->year,
        ])->layout('admin.layouts.app');
    }
}
