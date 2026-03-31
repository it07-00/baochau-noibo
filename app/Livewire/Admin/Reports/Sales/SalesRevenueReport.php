<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Models\ContractCommercial;
use App\Models\ContractConsulting;
use App\Models\ContractEnergy;
use App\Models\ContractPaymentSchedule;
use App\Models\ContractProject;
use App\Models\ContractSustainability;
use App\Models\ContractWaste;
use App\Models\Department;
use App\Models\User;
use Livewire\Component;

class SalesRevenueReport extends Component
{
    public int $year;
    public string $filter_staff = '';
    public string $filter_department = '';
    public string $filter_contract_type = '';
    public string $filter_renewal = '';

    protected array $contractTypeMap = [
        'waste'          => ContractWaste::class,
        'consulting'     => ContractConsulting::class,
        'project'        => ContractProject::class,
        'commercial'     => ContractCommercial::class,
        'sustainability' => ContractSustainability::class,
        'energy'         => ContractEnergy::class,
    ];

    protected array $contractTypeLabels = [
        ContractWaste::class          => 'Chất thải',
        ContractConsulting::class      => 'Tư vấn',
        ContractProject::class         => 'Dự án',
        ContractCommercial::class      => 'Thương mại',
        ContractSustainability::class  => 'Bền vững',
        ContractEnergy::class          => 'Năng lượng',
    ];

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
    }

    public function render()
    {
        $query = ContractPaymentSchedule::whereIn('status', ['paid', 'partial'])
            ->whereYear('paid_date', $this->year);

        // Filter by contract type
        if ($this->filter_contract_type) {
            $modelClass = $this->contractTypeMap[$this->filter_contract_type] ?? null;
            if ($modelClass) {
                $query->where('contract_type', $modelClass);
            }
        }

        // Filter by staff (via contract's staff_id / user_id)
        if ($this->filter_staff) {
            $query->where(function ($q) {
                foreach ($this->contractTypeMap as $modelClass) {
                    $q->orWhere(function ($sub) use ($modelClass) {
                        $sub->where('contract_type', $modelClass)
                            ->whereIn('contract_id', $modelClass::where('staff_id', $this->filter_staff)->pluck('id'));
                    });
                }
            });
        }

        // Filter by department
        if ($this->filter_department) {
            $query->where(function ($q) {
                foreach ($this->contractTypeMap as $modelClass) {
                    $q->orWhere(function ($sub) use ($modelClass) {
                        $sub->where('contract_type', $modelClass)
                            ->whereIn('contract_id', $modelClass::where('department_id', $this->filter_department)->pluck('id'));
                    });
                }
            });
        }

        // Filter by renewal status
        if ($this->filter_renewal !== '') {
            $isRenewal = (bool) $this->filter_renewal;
            $query->where(function ($q) use ($isRenewal) {
                foreach ($this->contractTypeMap as $modelClass) {
                    $q->orWhere(function ($sub) use ($modelClass, $isRenewal) {
                        $sub->where('contract_type', $modelClass)
                            ->whereIn('contract_id', $modelClass::where('is_renewal', $isRenewal)->pluck('id'));
                    });
                }
            });
        }

        // Monthly data grouped by contract type + is_renewal
        $payments = (clone $query)->with('contract')->get();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'new' => 0,
                'renewal' => 0,
                'total' => 0,
                'by_type' => [],
            ];
            foreach ($this->contractTypeLabels as $class => $label) {
                $months[$m]['by_type'][$label] = ['new' => 0, 'renewal' => 0, 'total' => 0];
            }
        }

        foreach ($payments as $payment) {
            $m = (int) $payment->paid_date->format('m');
            $amount = (float) $payment->paid_amount;
            $contract = $payment->contract;
            $isRenewal = $contract ? (bool) $contract->is_renewal : false;
            $typeLabel = $this->contractTypeLabels[$payment->contract_type] ?? 'Khác';

            $key = $isRenewal ? 'renewal' : 'new';
            $months[$m][$key] += $amount;
            $months[$m]['total'] += $amount;

            if (isset($months[$m]['by_type'][$typeLabel])) {
                $months[$m]['by_type'][$typeLabel][$key] += $amount;
                $months[$m]['by_type'][$typeLabel]['total'] += $amount;
            }
        }

        // Totals
        $totals = ['new' => 0, 'renewal' => 0, 'total' => 0, 'by_type' => []];
        foreach ($this->contractTypeLabels as $class => $label) {
            $totals['by_type'][$label] = ['new' => 0, 'renewal' => 0, 'total' => 0];
        }
        foreach ($months as $data) {
            $totals['new'] += $data['new'];
            $totals['renewal'] += $data['renewal'];
            $totals['total'] += $data['total'];
            foreach ($data['by_type'] as $label => $vals) {
                $totals['by_type'][$label]['new'] += $vals['new'];
                $totals['by_type'][$label]['renewal'] += $vals['renewal'];
                $totals['by_type'][$label]['total'] += $vals['total'];
            }
        }

        // Staff revenue ranking
        $staffRevenue = collect();
        if (!$this->filter_staff) {
            $allPayments = (clone $query)->get();
            $contractIds = $allPayments->groupBy('contract_type');
            $staffTotals = [];

            foreach ($contractIds as $type => $items) {
                $ids = $items->pluck('contract_id')->unique();
                $modelClass = $type;
                if (!class_exists($modelClass)) continue;

                $contracts = $modelClass::whereIn('id', $ids)->with('staff')->get()->keyBy('id');
                foreach ($items as $item) {
                    $contract = $contracts->get($item->contract_id);
                    if (!$contract || !$contract->staff) continue;
                    $staffName = $contract->staff->name;
                    $staffTotals[$staffName] = ($staffTotals[$staffName] ?? 0) + (float) $item->paid_amount;
                }
            }

            $staffRevenue = collect($staffTotals)->sortDesc()->take(15)
                ->map(fn($total, $name) => ['name' => $name, 'total' => $total]);
        }

        return view('livewire.admin.reports.sales.sales-revenue-report', [
            'months'          => $months,
            'totals'          => $totals,
            'typeLabels'      => array_values($this->contractTypeLabels),
            'staffs'          => User::role('kinh-doanh')->orderBy('name')->get(),
            'departments'     => Department::orderBy('name')->get(),
            'years'           => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
            'contractTypes'   => $this->contractTypeMap,
            'staffRevenue'    => $staffRevenue,
        ])->layout('admin.layouts.app', ['title' => 'Doanh số thực thu']);
    }
}
