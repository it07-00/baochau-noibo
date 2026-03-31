<?php

namespace App\Livewire\Admin;

use App\Models\ContractCommercial;
use App\Models\ContractConsulting;
use App\Models\ContractEnergy;
use App\Models\ContractProject;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\ContractAssignment;
use App\Models\ContractPaymentSchedule;
use App\Models\Customer;
use App\Models\ProgressiveSales;
use App\Models\QuotationSales;
use App\Models\RenewalSales;
use App\Models\User;
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

        // ── Theo tháng: tất cả 6 loại HĐ ký ─────────
        $monthlyModels = [
            ContractWaste::class,
            ContractConsulting::class,
            ContractProject::class,
            ContractCommercial::class,
            ContractSustainability::class,
            ContractEnergy::class,
        ];

        $contractMonthly = [];
        foreach ($monthlyModels as $model) {
            $rows = $model::whereYear('signed_at', $this->year)
                ->selectRaw('MONTH(signed_at) as m, COUNT(*) as cnt, SUM(value) as val')
                ->groupByRaw('MONTH(signed_at)')->get()->keyBy('m');
            foreach ($rows as $m => $row) {
                $contractMonthly[$m]['cnt'] = ($contractMonthly[$m]['cnt'] ?? 0) + $row->cnt;
                $contractMonthly[$m]['val'] = ($contractMonthly[$m]['val'] ?? 0) + (float) $row->val;
            }
        }

        $qM = QuotationSales::whereYear('sales_month', $this->year)
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        $rM = RenewalSales::whereYear('sales_month', $this->year)
            ->selectRaw('MONTH(sales_month) as m, SUM(sales_amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        $pM = ProgressiveSales::whereYear('sales_month', $this->year)
            ->selectRaw('MONTH(sales_month) as m, SUM(amount) as val')
            ->groupByRaw('MONTH(sales_month)')->get()->keyBy('m');

        // ── Tiến độ thu tiền ────────────────────────
        $paymentDueByMonth = ContractPaymentSchedule::whereYear('due_date', $this->year)
            ->selectRaw('MONTH(due_date) as m, SUM(amount) as total')
            ->groupByRaw('MONTH(due_date)')->get()->keyBy('m');

        $paymentPaidByMonth = ContractPaymentSchedule::whereYear('paid_date', $this->year)
            ->selectRaw('MONTH(paid_date) as m, SUM(paid_amount) as total')
            ->groupByRaw('MONTH(paid_date)')->get()->keyBy('m');

        $totalPaymentDue  = (float) ContractPaymentSchedule::whereYear('due_date', $this->year)->sum('amount');
        $totalPaymentPaid = (float) ContractPaymentSchedule::whereYear('paid_date', $this->year)->sum('paid_amount');

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly[$m] = [
                'contracts'    => $contractMonthly[$m]['cnt'] ?? 0,
                'value'        => (float) ($contractMonthly[$m]['val'] ?? 0),
                'sales'        => (float) ($qM->get($m)?->val ?? 0)
                               + (float) ($rM->get($m)?->val ?? 0)
                               + (float) ($pM->get($m)?->val ?? 0),
                'payment_due'  => (float) ($paymentDueByMonth->get($m)?->total ?? 0),
                'payment_paid' => (float) ($paymentPaidByMonth->get($m)?->total ?? 0),
            ];
        }

        // ── Bộ phận kỹ thuật ──────────────────────────
        $currentUser = auth()->user();
        $canSeeTechnical = $currentUser->hasAnyRole(['giam-doc', 'ky-thuat']);

        $technicalStats = collect();
        if ($canSeeTechnical) {
            $techUsers = User::role('ky-thuat')->get();
            $typeLabels = [
                ContractWaste::class          => 'Chất thải',
                ContractConsulting::class      => 'Tư vấn',
                ContractProject::class         => 'Dự án',
                ContractCommercial::class      => 'Thương mại',
                ContractSustainability::class  => 'Bền vững',
                ContractEnergy::class          => 'Năng lượng',
            ];

            foreach ($typeLabels as $modelClass => $label) {
                $assignments = ContractAssignment::where('assignable_type', $modelClass)
                    ->whereHas('assignable', fn ($q) => $q->whereYear('signed_at', $this->year))
                    ->with('assignable')
                    ->get();

                $count = $assignments->count();
                $value = $assignments->sum(fn ($a) => (float) ($a->assignable->value ?? 0));
                $completed = $assignments->filter(fn ($a) => ($a->assignable->status ?? '') === 'finished')->count();

                $technicalStats->push([
                    'label'     => $label,
                    'count'     => $count,
                    'value'     => $value,
                    'completed' => $completed,
                ]);
            }
        }

        return view('livewire.admin.statistics-board', compact(
            'totalCustomers', 'totalContracts', 'totalContractValue', 'totalSales',
            'totalPaymentDue', 'totalPaymentPaid',
            'byType', 'monthly', 'canSeeTechnical', 'technicalStats'
        ))->layout('admin.layouts.app');
    }
}
