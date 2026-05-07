<?php

namespace App\Livewire\Admin\Reports\Sales;

use App\Enums\Role;
use App\Models\SalesRenewal;
use App\Models\SalesProgressive;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SalesTrackingReport extends Component
{
    use WithPagination;

    public int $year;
    public string $filter_month  = '';
    public string $filter_staff  = '';
    public string $filter_status = '';
    public string $active_tab    = 'renewal'; // renewal | progressive

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');

        if (auth()->user()->hasRole(Role::KINH_DOANH->value)) {
            $this->filter_staff = (string) auth()->id();
        }
    }

    public function updatedActiveTab(): void   { $this->resetPage(); }
    public function updatedYear(): void        { $this->resetPage(); }
    public function updatedFilterMonth(): void { $this->resetPage(); }
    public function updatedFilterStaff(): void { $this->resetPage(); }

    public function render()
    {
        $isKinhDoanh  = auth()->user()->hasRole(Role::KINH_DOANH->value);
        $staffFilter  = $this->filter_staff ?: ($isKinhDoanh ? (string) auth()->id() : '');

        $items = match ($this->active_tab) {
            'renewal' => SalesRenewal::with('creator')
                ->whereYear('sales_month', $this->year)
                ->when($this->filter_month,  fn($q) => $q->whereMonth('sales_month', $this->filter_month))
                ->when($staffFilter,         fn($q) => $q->where('user_id', $staffFilter))
                ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
                ->orderByDesc('sales_month')
                ->paginate(20),

            'progressive' => SalesProgressive::with('creator')
                ->whereYear('sales_month', $this->year)
                ->when($this->filter_month,  fn($q) => $q->whereMonth('sales_month', $this->filter_month))
                ->when($staffFilter,         fn($q) => $q->where('user_id', $staffFilter))
                ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
                ->orderByDesc('sales_month')
                ->paginate(20),

            default => collect()->paginate(20),
        };

        // Tab totals (without pagination)
        $rTotal = SalesRenewal::whereYear('sales_month', $this->year)
            ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
            ->sum('sales_amount');
        $pTotal = SalesProgressive::whereYear('sales_month', $this->year)
            ->when($staffFilter, fn($q) => $q->where('user_id', $staffFilter))
            ->sum('amount');

        return view('livewire.admin.reports.sales.sales-tracking-report', [
            'items'   => $items,
            'rTotal'  => (float) $rTotal,
            'pTotal'  => (float) $pTotal,
            'staffs'  => User::role(['kinh-doanh', 'tp-kinh-doanh'])->orderBy('name')->get(),
            'years'   => range((int) now()->format('Y'), (int) now()->format('Y') - 4),
        ])->layout('admin.layouts.app', ['title' => 'Bảng theo dõi doanh số']);
    }
}
