<?php

namespace App\Livewire\Admin\Hr;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class HrProfileManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = '';
    public string $workTypeFilter = '';
    public string $departmentFilter = '';
    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingWorkTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::with(['department', 'roles'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('employee_code', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('employment_status', $this->statusFilter))
            ->when($this->workTypeFilter, fn($q) => $q->where('work_type', $this->workTypeFilter))
            ->when($this->departmentFilter, fn($q) => $q->where('department_id', $this->departmentFilter));

        $stats = [
            'total'      => User::count(),
            'active'     => User::where('employment_status', 'chinh_thuc')->where('is_active', true)->count(),
            'probation'  => User::where('employment_status', 'thu_viec')->where('is_active', true)->count(),
            'intern'     => User::where('employment_status', 'thuc_tap')->where('is_active', true)->count(),
            'resigned'   => User::where('employment_status', 'nghi_viec')->count(),
        ];

        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.hr.hr-profile-manager', [
            'users'       => $query->orderBy('name')->paginate($this->perPage),
            'stats'       => $stats,
            'departments' => $departments,
        ])->layout('admin.layouts.app');
    }

    public function updateQuickField(int $userId, string $field, string $value): void
    {
        $allowed = [
            'employment_status' => array_keys(User::EMPLOYMENT_STATUSES),
            'work_type'         => array_keys(User::WORK_TYPES),
        ];

        if (!isset($allowed[$field]) || !in_array($value, $allowed[$field])) {
            return;
        }

        User::where('id', $userId)->update([$field => $value]);
    }
}
