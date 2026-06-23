<?php

namespace App\Livewire\Admin\InternalDocs;

use App\Enums\Role;
use App\Models\InternalSoftware;
use Livewire\Component;
use Livewire\WithPagination;

class SoftwareManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $name = '';
    public $description = '';
    public $url = '';
    public $version = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'url' => 'required|url|max:255',
        'description' => 'nullable|string',
        'version' => 'nullable|string|max:50',
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

    public function openCreateModal()
    {
        if (!auth()->user()->hasRole(Role::IT->value)) return;
        $this->resetValidation();
        $this->reset(['editingId', 'name', 'description', 'url', 'version', 'is_active']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        if (!auth()->user()->hasRole(Role::IT->value)) return;
        $software = InternalSoftware::findOrFail($id);
        $this->editingId = $software->id;
        $this->name = $software->name;
        $this->description = $software->description;
        $this->url = $software->url;
        $this->version = $software->version;
        $this->is_active = $software->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        if (!auth()->user()->hasRole(Role::IT->value)) return;
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'version' => $this->version,
            'is_active' => $this->is_active,
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
        if (!auth()->user()->hasRole(Role::IT->value)) return;
        InternalSoftware::findOrFail($id)->delete();
        $this->dispatch('swal:success', ['message' => 'Đã xóa phần mềm.']);
    }

    public function render()
    {
        $softwares = InternalSoftware::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when(!auth()->user()->hasRole(Role::IT->value), function ($q) {
                $q->where('is_active', true);
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.internal-docs.software-manager', [
            'softwares' => $softwares
        ])->layout('admin.layouts.app', [
            'title' => 'Phần mềm nội bộ',
        ]);
    }
}
