<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\Role;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Kiểm tra user có vai trò phù hợp không.
     * Sử dụng: middleware('role:it,quan-ly')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Bạn chưa đăng nhập.');
        }

        // Super Admin → toàn quyền
        if ($user->hasRole(Role::IT->value)) {
            return $next($request);
        }

        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}
