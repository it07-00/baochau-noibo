<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(Permission::USERS_CREATE->value);
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:users,username'],
            'email'         => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'password'      => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
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
            'password.required'   => 'Vui lòng nhập mật khẩu.',
            'password.min'        => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.max'        => 'Mật khẩu không được vượt quá :max ký tự.',
            'password.confirmed'  => 'Xác nhận mật khẩu không khớp.',
            'department_id.exists'=> 'Phòng ban không hợp lệ.',
            'role.required'       => 'Vui lòng chọn vai trò.',
            'role.exists'         => 'Vai trò không hợp lệ.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                 => 'họ và tên',
            'username'             => 'tên đăng nhập',
            'email'                => 'email',
            'phone'                => 'số điện thoại',
            'password'             => 'mật khẩu',
            'password_confirmation'=> 'xác nhận mật khẩu',
            'department_id'        => 'phòng ban',
            'role'                 => 'vai trò',
        ];
    }
}
