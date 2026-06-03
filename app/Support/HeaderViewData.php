<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\User;

class HeaderViewData
{
    public function displayName(?User $user): string
    {
        return $user?->name ?? 'Người dùng';
    }

    public function primaryRole(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return collect(Role::priorityList())
            ->first(fn ($r) => $user->hasRole($r))
            ?? $user->roles?->first()?->name;
    }

    public function roleLabel(?User $user): string
    {
        return Role::tryFrom($this->primaryRole($user) ?? '')?->label() ?? 'Nhân viên';
    }

    public function roleColor(?User $user): string
    {
        return Role::tryFrom($this->primaryRole($user) ?? '')?->color() ?? '#64748b';
    }

    public function todayLabel(): string
    {
        $weekdays = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];

        return $weekdays[now()->dayOfWeek] . ', ' . now()->format('d/m/Y');
    }

    public function wish(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour >= 5 && $hour < 11 => 'Chúc bạn buổi sáng làm việc hiệu quả! ☀️',
            $hour >= 11 && $hour < 13 => 'Chúc bạn buổi trưa vui vẻ! 🌤️',
            $hour >= 13 && $hour < 18 => 'Chúc bạn buổi chiều làm việc tốt lành! 🌿',
            $hour >= 18 && $hour < 22 => 'Chúc bạn buổi tối thư giãn! 🌙',
            default => 'Chúc bạn một ngày tốt lành! ✨',
        };
    }
}
