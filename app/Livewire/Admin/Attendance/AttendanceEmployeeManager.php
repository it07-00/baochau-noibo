<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\AttendanceEmployee;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceEmployeeManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterDepartment = '';

    // Form fields
    public ?int $editingId = null;
    public string $editName = '';
    public string $editDepartment = '';
    public int $editDeviceUid = 0;

    public bool $showModal = false;
    public bool $isCreating = false;

    public bool $confirmingDelete = false;
    public ?int $deletingId = null;

    protected $queryString = ['search', 'filterDepartment'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'editName', 'editDepartment', 'editDeviceUid']);
        $this->isCreating = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $emp = AttendanceEmployee::findOrFail($id);
        $this->editingId = $emp->id;
        $this->editName = $emp->name;
        $this->editDepartment = $emp->department ?? '';
        $this->editDeviceUid = $emp->device_uid;
        $this->isCreating = false;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'editName' => 'required|string|max:255',
            'editDepartment' => 'nullable|string|max:255',
        ];

        if ($this->isCreating) {
            $rules['editDeviceUid'] = 'required|integer|min:1|unique:attendance_employees,device_uid';
        }

        $this->validate($rules, [
            'editName.required' => 'Tên nhân viên không được để trống.',
            'editDeviceUid.required' => 'Mã máy chấm công không được để trống.',
            'editDeviceUid.unique' => 'Mã máy chấm công đã tồn tại.',
        ]);

        $department = $this->editDepartment ?: null;

        if ($this->isCreating) {
            AttendanceEmployee::create([
                'device_uid' => $this->editDeviceUid,
                'name' => $this->editName,
                'department' => $department,
            ]);
            $this->dispatch('swal:toast', type: 'success', message: 'Đã thêm nhân viên thành công.');
        } else {
            $emp = AttendanceEmployee::findOrFail($this->editingId);
            $emp->update([
                'name' => $this->editName,
                'department' => $department,
            ]);
            $this->dispatch('swal:toast', type: 'success', message: 'Đã cập nhật thành công.');
        }

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            AttendanceEmployee::findOrFail($this->deletingId)->delete();
            $this->dispatch('swal:toast', type: 'success', message: 'Đã xóa nhân viên.');
        }
        $this->confirmingDelete = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $query = AttendanceEmployee::query()->orderBy('device_uid');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('device_uid', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterDepartment) {
            $query->where('department', $this->filterDepartment);
        }

        $employees = $query->paginate(25);

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        return view('livewire.admin.attendance.attendance-employee-manager', [
            'employees' => $employees,
            'departments' => $departments,
        ])->layout('admin.layouts.app');
    }
}
