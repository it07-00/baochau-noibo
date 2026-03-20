<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')->latest()->get();

        return view('admin.pages.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.pages.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['required', 'string', 'max:255', 'unique:departments,slug'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Department::create([
            'name'      => $validated['name'],
            'slug'      => $validated['slug'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('app.departments.index')
            ->with('status', 'Tạo phòng ban thành công.');
    }

    public function edit(Department $department)
    {
        return view('admin.pages.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['required', 'string', 'max:255', 'unique:departments,slug,' . $department->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $department->update([
            'name'      => $validated['name'],
            'slug'      => $validated['slug'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('app.departments.index')
            ->with('status', 'Cập nhật phòng ban thành công.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Không thể xóa phòng ban đang có nhân viên.');
        }

        $department->delete();

        return redirect()
            ->route('app.departments.index')
            ->with('status', 'Xóa phòng ban thành công.');
    }
}
