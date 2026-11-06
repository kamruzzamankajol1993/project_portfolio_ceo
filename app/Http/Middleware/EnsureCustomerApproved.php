<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return redirect()->guest(route('customer.login'));
        }

        if (!$customer->hasVerifiedEmail()) {
            Auth::guard('customer')->logout();

            return redirect()->route('customer.verification.notice', [
                'email' => $customer->email,
            ])->with('status', 'Please verify your email address before continuing.');
        }

        if (!$customer->isActive()) {
            $message = $customer->status === 'rejected'
                ? 'Your account approval request was declined. Please contact the website administrator.'
                : 'Your account is waiting for administrator approval.';

            Auth::guard('customer')->logout();

            return redirect()->route('customer.login')->with('status', $message);
        }

        return $next($request);
    }
}
