<?php

namespace App\Livewire\Admin\Reports\Consulting;

use App\Models\ContractLegal;
use App\Models\User;
use App\Support\ContractBusinessIdentity;
use Livewire\Component;
use Livewire\WithPagination;

class ConsultingMonitoringReport extends Component
{
    use WithPagination;

    public int $year;

    public string $filter_service = '';

    public string $filter_status = '';

    public int|string $filter_staff = '';

    public array $years = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterService(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStaff(): void
    {
        $this->resetPage();
    }

    private function baseQuery()
    {
        $types = ['Quan trắc môi trường', 'Quan trắc môi trường lao động và phân loại lao động'];

        return ContractLegal::whereIn('loai_dich_vu', $types)
            ->whereYear('signed_at', $this->year)
            ->when($this->filter_service, fn ($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->when($this->filter_status, fn ($q) => $q->where('status', $this->filter_status))
            ->when($this->filter_staff, fn ($q) => $q->where(
                fn ($q2) => $q2->where('staff_id', $this->filter_staff)
                    ->orWhere('consultant_id', $this->filter_staff)
            ));
    }

    public function render()
    {
        $contracts = $this->baseQuery()
            ->with(['customer', 'staff', 'consultant'])
            ->orderByDesc('signed_at')
            ->orderByDesc('id')
            ->get();
        $items = ContractBusinessIdentity::paginate($contracts, 20);

        $statusSummary = ContractBusinessIdentity::statusSummary($contracts);
        $summary = (object) [
            'count' => $statusSummary['total'],
            'total_value' => $statusSummary['total_value'],
            'completed' => $statusSummary['completed'],
            'active' => $statusSummary['active'],
        ];

        $staffs = User::where('is_active', true)->orderBy('name')->get();
        $monitoringTypes = ['Quan trắc môi trường', 'Quan trắc môi trường lao động và phân loại lao động'];

        return view('livewire.admin.reports.consulting.consulting-monitoring-report',
            compact('items', 'summary', 'staffs', 'monitoringTypes'))
            ->layout('admin.layouts.app');
    }
}
