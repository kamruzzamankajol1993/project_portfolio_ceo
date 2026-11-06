<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the admin authentication flow separate from Laravel's default /home flow.
 */
class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('web')->user();

        if ($admin) {
            return $admin->can('access.admin')
                ? redirect()->route('admin.dashboard')
                : redirect()->route('site.home');
        }

        return $next($request);
    }
}
