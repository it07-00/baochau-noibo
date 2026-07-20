<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\AttendanceEmployee;
use App\Models\AttendanceLog;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AttendanceEmployeeManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $filterDepartment = '';

    public bool $showInactive = false;

    public ?int $editingId = null;

    public string $editName = '';

    public string $editDepartment = '';

    public int $editDeviceUid = 0;

    public bool $showModal = false;

    public bool $isCreating = false;

    public bool $confirmingBlock = false;

    public ?int $blockingId = null;

    public $syncFile;

    public bool $showSyncModal = false;

    protected $queryString = ['search', 'filterDepartment', 'showInactive'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment(): void
    {
        $this->resetPage();
    }

    public function updatingShowInactive(): void
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
                'is_active' => true,
                'is_blocked' => false,
            ]);
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã thêm nhân viên thành công.']);
        } else {
            AttendanceEmployee::findOrFail($this->editingId)->update([
                'name' => $this->editName,
                'department' => $department,
            ]);
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã cập nhật thành công.']);
        }

        $this->showModal = false;
    }

    public function reactivate(int $id): void
    {
        AttendanceEmployee::findOrFail($id)->update(['is_active' => true, 'is_blocked' => false]);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã kích hoạt lại nhân viên.']);
    }

    public function confirmBlock(int $id): void
    {
        $this->blockingId = $id;
        $this->confirmingBlock = true;
    }

    public function block(): void
    {
        if ($this->blockingId) {
            $emp = AttendanceEmployee::findOrFail($this->blockingId);
            $emp->update([
                'is_active' => false,
                'is_blocked' => true,
            ]);
            AttendanceLog::where('employee_id', $emp->id)->delete();
            $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã chặn và xóa toàn bộ dữ liệu chấm công của nhân viên này.']);
        }
        $this->confirmingBlock = false;
        $this->blockingId = null;
    }

    public function unblock(int $id): void
    {
        AttendanceEmployee::findOrFail($id)->update(['is_blocked' => false, 'is_active' => true]);
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã bỏ chặn. Nhân viên hoạt động trở lại.']);
    }

    public function openSyncModal(): void
    {
        $this->reset(['syncFile']);
        $this->showSyncModal = true;
    }

    public function syncFromDevice(): void
    {
        $this->validate([
            'syncFile' => 'required|file|extensions:dat,txt,csv|max:2048',
        ], [
            'syncFile.required' => 'Vui lòng chọn file user.dat.',
        ]);

        $rawEmployees = $this->parseUserDat(file_get_contents($this->syncFile->getRealPath()));

        $blockedUids = AttendanceEmployee::where('is_blocked', true)->pluck('device_uid')->toArray();
        $activeFileUids = array_diff(array_keys($rawEmployees), $blockedUids);

        AttendanceEmployee::where('is_blocked', false)
            ->whereNotIn('device_uid', $activeFileUids)
            ->update(['is_active' => false]);

        $upserted = 0;
        foreach ($rawEmployees as $uid => $name) {
            if (in_array($uid, $blockedUids)) {
                continue;
            }
            $existing = AttendanceEmployee::where('device_uid', $uid)->first();
            AttendanceEmployee::updateOrCreate(
                ['device_uid' => $uid],
                ['name' => $existing?->name ?? $name, 'is_active' => true],
            );
            $upserted++;
        }

        $this->showSyncModal = false;
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => "Đã đồng bộ {$upserted} nhân viên từ máy chấm công."]);
    }

    private function parseUserDat(string $binary): array
    {
        $employees = [];
        $recordSize = 72;
        $offset = 0;

        while ($offset + $recordSize <= strlen($binary)) {
            $record = substr($binary, $offset, $recordSize);
            $uid = unpack('v', substr($record, 0, 2))[1];
            $nameRaw = substr($record, 11, 24);
            $name = rtrim(explode("\x00", $nameRaw)[0]);

            if ($uid > 0 && $name !== '') {
                $employees[$uid] = $name;
            }

            $offset += $recordSize;
        }

        return $employees;
    }

    public function render()
    {
        $query = AttendanceEmployee::query()->orderBy('device_uid');

        if (! $this->showInactive) {
            $query->where('is_active', true)->where('is_blocked', false);
        }

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
            'totalEmployees' => AttendanceEmployee::count(),
            'activeEmployees' => AttendanceEmployee::where('is_active', true)->where('is_blocked', false)->count(),
            'blockedEmployees' => AttendanceEmployee::where('is_blocked', true)->count(),
        ])->layout('admin.layouts.app');
    }
}
