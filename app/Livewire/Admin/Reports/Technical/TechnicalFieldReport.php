<?php

namespace App\Livewire\Admin\Reports\Technical;

use App\Models\ContractConsulting;
use App\Models\User;
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

    public function updatedYear(): void          { $this->resetPage(); }
    public function updatedFilterService(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterStaff(): void   { $this->resetPage(); }

    private function baseQuery()
    {
        $types = ['Quan trắc môi trường', 'Quan trắc môi trường lao động và phân loại lao động'];
        return ContractConsulting::whereIn('loai_dich_vu', $types)
            ->whereYear('signed_at', $this->year)
            ->when($this->filter_service, fn($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->when($this->filter_staff, fn($q) => $q->where('consultant_id', $this->filter_staff));
    }

    public function render()
    {
        $items = $this->baseQuery()
            ->with(['customer', 'consultant', 'staff'])
            ->orderByDesc('signed_at')
            ->paginate(20);

        $summary = $this->baseQuery()
            ->selectRaw('COUNT(*) as total, SUM(value) as total_value,
                SUM(CASE WHEN status = "HOÀN THÀNH" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "ĐANG THỰC HIỆN" THEN 1 ELSE 0 END) as active')
            ->first();

        $staffs = User::orderBy('name')->get();
        $monitoringTypes = ['Quan trắc môi trường', 'Quan trắc môi trường lao động và phân loại lao động'];

        return view('livewire.admin.reports.technical.technical-field-report',
            compact('items', 'summary', 'staffs', 'monitoringTypes'))
            ->layout('admin.layouts.app');
    }
}
