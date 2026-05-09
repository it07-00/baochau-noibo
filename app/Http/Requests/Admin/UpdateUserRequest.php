<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(Permission::USERS_EDIT->value);
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email'         => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'         => ['nullable', 'string', 'max:30'],
            'password'      => ['nullable', 'string', 'min:8', 'max:255'],
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role'          => ['required', 'exists:roles,name'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Vui lòng nhập họ và tên.',
            'username.required'   => 'Vui lòng nhập tên đăng nhập.',
            'username.unique'     => 'Tên đăng nhập đã tồn tại.',
            'email.email'         => 'Email không đúng định dạng.',
            'email.unique'        => 'Email đã tồn tại.',
            'password.min'        => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.max'        => 'Mật khẩu không được vượt quá :max ký tự.',
            'department_id.exists'=> 'Phòng ban không hợp lệ.',
            'role.required'       => 'Vui lòng chọn vai trò.',
            'role.exists'         => 'Vai trò không hợp lệ.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'         => 'họ và tên',
            'username'     => 'tên đăng nhập',
            'email'        => 'email',
            'phone'        => 'số điện thoại',
            'password'     => 'mật khẩu',
            'department_id'=> 'phòng ban',
            'role'         => 'vai trò',
        ];
    }
}
