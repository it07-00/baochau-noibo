<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Kiểm tra user có quyền truy cập không.
     * Sử dụng: middleware('permission:contracts-waste.view')
     *       hoặc middleware('permission:contracts-waste.view|contracts-waste.create')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Bạn chưa đăng nhập.');
        }

        // Super Admin (role: it) → toàn quyền
        if ($user->hasRole('it')) {
            return $next($request);
        }

        // Hỗ trợ nhiều permission cách nhau bởi |
        $permissions = explode('|', $permission);

        foreach ($permissions as $perm) {
            if ($user->hasPermissionTo(trim($perm))) {
                return $next($request);
            }
        }

        abort(403, 'Bạn không có quyền truy cập chức năng này.');
    }
}
