<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Compatibility controller for legacy links. This project uses its own admin
 * and customer dashboards, so the stock Laravel home screen is never rendered.
 */
class HomeController extends Controller
{
    public function index(): RedirectResponse
    {
        $admin = Auth::guard('web')->user();

        if ($admin) {
            return $admin->can('access.admin')
                ? redirect()->route('admin.dashboard')
                : redirect()->route('site.home');
        }

        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return redirect()->route('site.home');
    }
}
