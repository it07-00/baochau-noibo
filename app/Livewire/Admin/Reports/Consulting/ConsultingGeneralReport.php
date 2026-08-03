<?php

namespace App\Livewire\Admin\Reports\Consulting;

use App\Models\ContractLegal;
use App\Support\ContractBusinessIdentity;
use Livewire\Component;

class ConsultingGeneralReport extends Component
{
    public int $year;

    public string $filter_service = '';

    public array $years = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function render()
    {
        $yearContracts = ContractLegal::whereYear('signed_at', $this->year)
            ->get();
        $byService = $yearContracts
            ->groupBy('loai_dich_vu')
            ->map(function ($contracts, $service) {
                $summary = ContractBusinessIdentity::statusSummary($contracts);

                return (object) [
                    'loai_dich_vu' => $service,
                    'count' => $summary['total'],
                    'total_value' => $summary['total_value'],
                    'completed' => $summary['completed'],
                    'active' => $summary['active'],
                ];
            })
            ->values();

        $filteredContracts = $yearContracts
            ->when(
                $this->filter_service,
                fn ($contracts) => $contracts->where('loai_dich_vu', $this->filter_service)
            )
            ->values();
        $monthRows = $filteredContracts->groupBy(fn ($contract): int => $contract->signed_at->month);

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $summary = ContractBusinessIdentity::statusSummary($monthRows->get($m, collect()));
            $monthly[$m] = [
                'count' => $summary['total'],
                'value' => $summary['total_value'],
                'completed' => $summary['completed'],
                'active' => $summary['active'],
            ];
        }

        $totalSummary = ContractBusinessIdentity::statusSummary($filteredContracts);
        $totals = [
            'count' => $totalSummary['total'],
            'value' => $totalSummary['total_value'],
            'completed' => $totalSummary['completed'],
            'active' => $totalSummary['active'],
        ];

        $serviceTypes = ContractLegal::SERVICE_TYPES;

        return view('livewire.admin.reports.consulting.consulting-general-report',
            compact('byService', 'monthly', 'totals', 'serviceTypes'))
            ->layout('admin.layouts.app');
    }
}
