<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RoleManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public function paginationView()
    {
        return 'livewire.admin.users.pagination'; // Reuse the custom pagination
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteRole(Role $role)
    {
        if ($role->users()->count() > 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa vai trò đang có người dùng gắn bó.'
            ]);
            return;
        }

        $role->delete();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Đã xóa vai trò thành công.'
        ]);
    }

    public function render()
    {
        $roles = Role::withCount(['permissions', 'users'])
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        // Map names for display if needed
        $roles->getCollection()->transform(function($role) {
            $role->display_name = match($role->name) {
                'it' => 'IT / Quản trị',
                'giam-doc' => 'Giám đốc',
                'tp-kinh-doanh' => 'Trưởng phòng KD',
                'quan-ly' => 'Quản lý (cũ)',
                'kinh-doanh' => 'Nhân viên KD',
                'ke-toan' => 'Kế toán',
                'tu-van' => 'Tư vấn',
                'ky-thuat' => 'Kỹ thuật',
                'marketing' => 'Marketing',
                default => ucfirst($role->name)
            };
            return $role;
        });

        return view('livewire.admin.roles.role-manager', [
            'roles' => $roles,
            'totalRoles' => Role::count(),
        ])->layout('admin.layouts.app');
    }
}
