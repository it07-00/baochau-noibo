<?php

namespace App\Livewire\Admin\Departments;

use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public function paginationView()
    {
        return 'livewire.admin.users.pagination';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteDepartment(Department $department)
    {
        if ($department->users()->count() > 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa phòng ban đang có nhân viên.'
            ]);
            return;
        }

        $department->delete();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Đã xóa phòng ban thành công.'
        ]);
    }

    public function toggleActive(Department $department)
    {
        $department->is_active = !$department->is_active;
        $department->save();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Cập nhật trạng thái ' . $department->name . ' thành công.'
        ]);
    }

    public function render()
    {
        $departments = Department::withCount('users')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        return view('livewire.admin.departments.department-manager', [
            'departments' => $departments,
            'totalDepartments' => Department::count(),
        ])->layout('admin.layouts.app');
    }
}
