<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'department'])->latest()->paginate(15);
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        return view('admin.pages.users.index', compact('users', 'totalUsers', 'activeUsers'));
    }

    public function create()
    {
        $roles = Role::all();
        $departments = $this->getActiveDepartments();

        return view('admin.pages.users.create', compact('roles', 'departments'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'          => $validated['name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'] ?? null,
            'phone'         => $validated['phone'] ?? null,
            'gender'        => $validated['gender'] ?? null,
            'password'      => Hash::make($validated['password']),
            'department_id' => $validated['department_id'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('app.users.index')
            ->with('status', 'Tạo người dùng thành công.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = $this->getActiveDepartments();

        return view('admin.pages.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->name          = $validated['name'];
        $user->username      = $validated['username'];
        $user->email         = $validated['email'] ?? null;
        $user->phone         = $validated['phone'] ?? null;
        $user->gender        = $validated['gender'] ?? null;
        $user->department_id = $validated['department_id'] ?? null;
        $user->is_active     = $request->boolean('is_active', true);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('app.users.index')
            ->with('status', 'Cập nhật người dùng thành công.');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->can(Permission::USERS_DELETE->value), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }

        $user->delete();

        return redirect()
            ->route('app.users.index')
            ->with('status', 'Xóa người dùng thành công.');
    }

    private function getActiveDepartments(): \Illuminate\Database\Eloquent\Collection
    {
        return Department::where('is_active', true)->get();
    }
}
