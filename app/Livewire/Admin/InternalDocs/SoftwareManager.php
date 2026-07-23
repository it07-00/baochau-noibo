<?php

namespace App\Livewire\Admin\InternalDocs;

use App\Enums\Role;
use App\Models\Department;
use App\Models\InternalSoftware;
use Livewire\Component;
use Livewire\WithPagination;

class SoftwareManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $departmentFilter = '';

    // Modal state
    public $showModal = false;

    public $editingId = null;

    // Form fields
    public $name = '';

    public $description = '';

    public $url = '';

    public $version = '';

    public $is_active = true;

    public $departmentId = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'url' => 'required|url|max:255',
        'description' => 'nullable|string',
        'version' => 'nullable|string|max:50',
        'departmentId' => 'nullable|exists:departments,id',
    ];

    public function mount()
    {
        if (auth()->user()->hasRole(Role::THUC_TAP->value)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        if (! auth()->user()->can(Permission::INTERNAL_SOFTWARE_MANAGE->value)) {
            return;
        }
        $this->resetValidation();
        $this->reset(['editingId', 'name', 'description', 'url', 'version', 'is_active', 'departmentId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        if (! auth()->user()->can(Permission::INTERNAL_SOFTWARE_MANAGE->value)) {
            return;
        }
        $software = InternalSoftware::findOrFail($id);
        $this->editingId = $software->id;
        $this->name = $software->name;
        $this->description = $software->description;
        $this->url = $software->url;
        $this->version = $software->version;
        $this->is_active = $software->is_active;
        $this->departmentId = $software->department_id ? (string) $software->department_id : '';
        $this->showModal = true;
    }

    public function save()
    {
        if (! auth()->user()->can(Permission::INTERNAL_SOFTWARE_MANAGE->value)) {
            return;
        }
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'version' => $this->version,
            'is_active' => $this->is_active,
            'department_id' => $this->departmentId ?: null,
        ];

        if ($this->editingId) {
            InternalSoftware::findOrFail($this->editingId)->update($data);
            $this->dispatch('swal:success', ['message' => 'Đã cập nhật phần mềm.']);
        } else {
            InternalSoftware::create($data);
            $this->dispatch('swal:success', ['message' => 'Đã thêm phần mềm mới.']);
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        if (! auth()->user()->can(Permission::INTERNAL_SOFTWARE_MANAGE->value)) {
            return;
        }
        InternalSoftware::findOrFail($id)->delete();
        $this->dispatch('swal:success', ['message' => 'Đã xóa phần mềm.']);
    }

    public function render()
    {
        $canManage = auth()->user()->can(Permission::INTERNAL_SOFTWARE_MANAGE->value);

        $softwares = InternalSoftware::query()
            ->with('department')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->when($this->departmentFilter !== '', function ($q) {
                if ($this->departmentFilter === 'company') {
                    $q->whereNull('department_id');
                } else {
                    $q->where('department_id', $this->departmentFilter);
                }
            })
            ->when(! $canManage, function ($q) {
                $q->where('is_active', true);
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.internal-docs.software-manager', [
            'softwares' => $softwares,
            'canManage' => $canManage,
            'departments' => Department::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ])->layout('admin.layouts.app', [
            'title' => 'Phần mềm nội bộ',
        ]);
    }
}
