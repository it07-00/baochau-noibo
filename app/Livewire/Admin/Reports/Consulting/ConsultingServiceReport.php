<?php

namespace App\Livewire\Admin\Reports\Consulting;

use App\Models\ContractLegal;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\WithPagination;

class ConsultingServiceReport extends Component
{
    use WithPagination;

    public int $year;
    public string $filter_service = '';
    public string $filter_status = '';
    public int|string $filter_staff = '';
    public string $page_title = 'Báo cáo hợp đồng tư vấn';
    public string $page_context = 'all';
    public array $years = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->years = range(now()->year, now()->year - 4);

        $routeName = Route::currentRouteName();
        $this->page_context = match($routeName) {
            'app.reports.consulting.gpmt' => 'gpmt',
            'app.reports.consulting.dkmt' => 'dkmt',
            'app.reports.consulting.vhtn' => 'vhtn',
            default                       => 'all',
        };

        $this->page_title = match($this->page_context) {
            'gpmt'  => 'Báo cáo GPMT / ĐTM',
            'dkmt'  => 'Báo cáo ĐKMT',
            'vhtn'  => 'Báo cáo VHTN',
            default => 'Báo cáo hợp đồng tư vấn',
        };

        if ($this->page_context !== 'all') {
            $this->filter_service = ContractLegal::SERVICE_TYPES[0];
        }
    }

    public function updatedYear(): void          { $this->resetPage(); }
    public function updatedFilterService(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void  { $this->resetPage(); }
    public function updatedFilterStaff(): void   { $this->resetPage(); }

    private function baseQuery()
    {
        return ContractLegal::whereYear('signed_at', $this->year)
            ->when($this->filter_service, fn($q) => $q->where('loai_dich_vu', $this->filter_service))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->when($this->filter_staff, fn($q) => $q->where(
                fn($q2) => $q2->where('staff_id', $this->filter_staff)
                              ->orWhere('consultant_id', $this->filter_staff)
            ));
    }

    public function render()
    {
        $items = $this->baseQuery()
            ->with(['customer', 'staff', 'consultant'])
            ->orderByDesc('signed_at')
            ->paginate(20);

        $summary = $this->baseQuery()
            ->selectRaw('COUNT(*) as count, SUM(value) as total_value,
                SUM(CASE WHEN status = "HOÀN THÀNH" THEN 1 ELSE 0 END) as completed')
            ->first();

        $staffs = User::orderBy('name')->get();
        $serviceTypes = ContractLegal::SERVICE_TYPES;

        return view('livewire.admin.reports.consulting.consulting-service-report',
            compact('items', 'summary', 'staffs', 'serviceTypes'))
            ->layout('admin.layouts.app');
    }
}
