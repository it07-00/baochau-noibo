<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
        $departments = Department::where('is_active', true)->get();

        return view('admin.pages.users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'         => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'password'      => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role'          => ['required', 'exists:roles,name'],
            'is_active'     => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá :max ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'department_id.exists' => 'Phòng ban không hợp lệ.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.exists' => 'Vai trò không hợp lệ.',
        ], [
            'name' => 'họ và tên',
            'username' => 'tên đăng nhập',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'password' => 'mật khẩu',
            'password_confirmation' => 'xác nhận mật khẩu',
            'department_id' => 'phòng ban',
            'role' => 'vai trò',
        ]);

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
        $departments = Department::where('is_active', true)->get();

        return view('admin.pages.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'         => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'         => ['nullable', 'string', 'max:30'],
            'password'      => ['nullable', 'string', 'min:8', 'max:255'],
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role'          => ['required', 'exists:roles,name'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'] ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->gender = $validated['gender'] ?? null;
        $user->department_id = $validated['department_id'] ?? null;
        $user->is_active = $request->boolean('is_active', true);

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
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }

        $user->delete();

        return redirect()
            ->route('app.users.index')
            ->with('status', 'Xóa người dùng thành công.');
    }
}
