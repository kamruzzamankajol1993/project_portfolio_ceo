<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfCustomerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if ($customer && $customer->isActive()) {
            return redirect()->route('customer.dashboard');
        }

        if ($customer) {
            Auth::guard('customer')->logout();
        }

        return $next($request);
    }
}
