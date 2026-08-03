<?php

namespace App\Livewire\Admin\Reports\Consulting;

use App\Enums\Role;
use App\Models\DailyReport;
use App\Services\ContractProgressRankingService;
use Illuminate\Support\Collection;
use Livewire\Component;

class ConsultingAchievementReport extends Component
{
    public int $year;

    public array $years = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function deptInitials(string $name): string
    {
        $parts = array_values(array_filter(explode(' ', trim($name)), fn ($word) => $word !== ''));
        if (count($parts) === 0) {
            return '?';
        }

        $last = array_slice($parts, -2);

        return strtoupper(implode('', array_map(fn ($word) => mb_substr($word, 0, 1), $last)));
    }

    public function hasDailyReportToday(): bool
    {
        return DailyReport::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->exists();
    }

    private function buildRankings(): Collection
    {
        return app(ContractProgressRankingService::class)->forRole(Role::TU_VAN, $this->year);
    }

    public function render()
    {
        $all = $this->buildRankings();

        $completionRankings = $all->sortByDesc('finished')->values();
        $rateRankings = $all->sortByDesc('pct')->values();

        return view('livewire.admin.reports.consulting.consulting-achievement-report', [
            'completionRankings' => $completionRankings,
            'rateRankings' => $rateRankings,
            'years' => $this->years,
            'year' => $this->year,
        ])->layout('admin.layouts.app');
    }
}
