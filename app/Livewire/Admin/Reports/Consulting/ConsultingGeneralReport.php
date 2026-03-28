<?php

namespace App\Livewire\Admin\Reports\Consulting;

use App\Models\ContractConsulting;
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
        $byService = ContractConsulting::whereYear('signed_at', $this->year)
            ->selectRaw('loai_dich_vu, COUNT(*) as count, SUM(value) as total_value,
                SUM(CASE WHEN status = "HOÀN THÀNH" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "ĐANG THỰC HIỆN" THEN 1 ELSE 0 END) as active')
            ->groupBy('loai_dich_vu')
            ->get();

        $monthRows = ContractConsulting::whereYear('signed_at', $this->year)
            ->when($this->filter_service, fn($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->selectRaw('MONTH(signed_at) as m, COUNT(*) as count, SUM(value) as total_value,
                SUM(CASE WHEN status = "HOÀN THÀNH" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "ĐANG THỰC HIỆN" THEN 1 ELSE 0 END) as active')
            ->groupByRaw('MONTH(signed_at)')
            ->get()->keyBy('m');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $row = $monthRows->get($m);
            $monthly[$m] = [
                'count'     => $row ? (int) $row->count : 0,
                'value'     => $row ? (float) $row->total_value : 0,
                'completed' => $row ? (int) $row->completed : 0,
                'active'    => $row ? (int) $row->active : 0,
            ];
        }

        $totals = [
            'count'     => array_sum(array_column($monthly, 'count')),
            'value'     => array_sum(array_column($monthly, 'value')),
            'completed' => array_sum(array_column($monthly, 'completed')),
            'active'    => array_sum(array_column($monthly, 'active')),
        ];

        $serviceTypes = ContractConsulting::SERVICE_TYPES;

        return view('livewire.admin.reports.consulting.consulting-general-report',
            compact('byService', 'monthly', 'totals', 'serviceTypes'))
            ->layout('admin.layouts.app');
    }
}
