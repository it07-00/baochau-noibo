<?php

namespace App\Livewire\Admin\Reports\Technical;

use App\Models\ContractLegal;
use App\Models\User;
use App\Support\ContractBusinessIdentity;
use Livewire\Component;
use Livewire\WithPagination;

class TechnicalFieldReport extends Component
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
            ->when($this->filter_staff, fn ($q) => $q->where('consultant_id', $this->filter_staff));
    }

    public function render()
    {
        $contracts = $this->baseQuery()
            ->with(['customer', 'consultant', 'staff'])
            ->orderByDesc('signed_at')
            ->orderByDesc('id')
            ->get();
        $items = ContractBusinessIdentity::paginate($contracts, 20);

        $statusSummary = ContractBusinessIdentity::statusSummary($contracts);
        $summary = (object) $statusSummary;

        $staffs = User::where('is_active', true)->orderBy('name')->get();
        $monitoringTypes = ['Quan trắc môi trường', 'Quan trắc môi trường lao động và phân loại lao động'];

        return view('livewire.admin.reports.technical.technical-field-report',
            compact('items', 'summary', 'staffs', 'monitoringTypes'))
            ->layout('admin.layouts.app');
    }
}
