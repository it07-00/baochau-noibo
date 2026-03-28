<?php

namespace App\Livewire\Admin;

use App\Models\ContractCommercial;
use App\Models\ContractConsulting;
use App\Models\ContractEnergy;
use App\Models\ContractProject;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\ProgressiveSales;
use App\Models\QuotationSales;
use App\Models\RenewalSales;
use Livewire\Component;

class StatisticsBoard extends Component
{
    public int $year;
    public array $years = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function render()
    {
        // ── KPI tổng quan ──────────────────────────────
        $totalCustomers = Customer::count();

        $contractTypes = [
            'Chất thải'   => ContractWaste::class,
            'Tư vấn'      => ContractConsulting::class,
            'Dự án'       => ContractProject::class,
            'Thương mại'  => ContractCommercial::class,
            'Năng lượng'  => ContractEnergy::class,
            'Bền vững'    => ContractSustainability::class,
        ];

        $byType = [];
        $totalContracts = 0;
        $totalContractValue = 0;

        foreach ($contractTypes as $label => $model) {
            $row = $model::whereYear('created_at', $this->year)
                ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(value),0) as val')
                ->first();
            $byType[$label] = [
                'count' => (int) ($row->cnt ?? 0),
                'value' => (float) ($row->val ?? 0),
            ];
            $totalContracts      += $byType[$label]['count'];
            $totalContractValue  += $byType[$label]['value'];
        }

        // ── Tổng doanh số từ sales tracking ────────────
        $totalSales = (float) QuotationSales::whereYear('sales_month', $this->year)->sum('sales_amount')
                    + (float) RenewalSales::whereYear('sales_month', $this->year)->sum('sales_amount')
                    + (float) ProgressiveSales::whereYear('sales_month', $this->year)->sum('amount');

        // ── Theo tháng: HĐ chất thải + tư vấn signed ──
        $wasteM = ContractWaste::whereYear('signed_at', $this->year)
            ->selectRaw('MONTH(signed_at) as m, COUNT(*) as cnt, SUM(value) as val')
            ->groupByRaw('MONTH(signed_at)')->get()->keyBy('m');

        $consultM = ContractConsulting::whereYear('signed_at', $this->year)
            ->selectRaw('MONTH(signed_at) as m, COUNT(*) as cnt, SUM(value) as val')
            ->groupByRaw('MONTH(signed_at)')->get()->keyBy('m');

        $qM = QuotationSales::whereYear('sales_month', $this->year)
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        $rM = RenewalSales::whereYear('sales_month', $this->year)
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        $pM = ProgressiveSales::whereYear('sales_month', $this->year)
            ->selectRaw('MONTH(sales_month) as m, SUM(amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = [
                'contracts' => ($wasteM->get($m)?->cnt ?? 0) + ($consultM->get($m)?->cnt ?? 0),
                'value'     => (float) ($wasteM->get($m)?->val ?? 0) + (float) ($consultM->get($m)?->val ?? 0),
                'sales'     => (float) ($qM->get($m)?->val ?? 0)
                             + (float) ($rM->get($m)?->val ?? 0)
                             + (float) ($pM->get($m)?->val ?? 0),
            ];
        }

        return view('livewire.admin.statistics-board', compact(
            'totalCustomers', 'totalContracts', 'totalContractValue', 'totalSales',
            'byType', 'monthly'
        ))->layout('admin.layouts.app');
    }
}
