<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
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

    public function lockAccount(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('swal:toast', ['type' => 'error', 'message' => 'Không thể tự khóa tài khoản của chính mình.']);
            return;
        }

        $user->is_active = false;
        $user->save();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã khóa tài khoản ' . $user->name]);
    }

    public function unlockAccount(User $user)
    {
        $user->is_active = true;
        $user->save();

        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã mở khóa tài khoản ' . $user->name]);
    }

    public function resetPassword(User $user)
    {
        $user->password = Hash::make(config('app.default_password'));
        $user->save();

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Đã reset mật khẩu của ' . $user->name . ' về mặc định.',
        ]);
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Không thể tự xóa tài khoản của chính mình.');
            return;
        }

        $user->delete();

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'Đã xóa người dùng thành công.',
        ]);
    }

    public function render()
    {
        $users = User::with(['roles', 'department'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        return view('livewire.admin.users.user-manager', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
        ])->layout('admin.layouts.app');
    }
}
