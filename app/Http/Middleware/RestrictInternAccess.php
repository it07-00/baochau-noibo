<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictInternAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(Role::THUC_TAP->value)) {
            return $next($request);
        }

        if ($request->routeIs('app.daily-reports.*')) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            return redirect()->route('app.daily-reports.index');
        }

        abort(403, 'Ban khong co quyen truy cap chuc nang nay.');
    }
}
