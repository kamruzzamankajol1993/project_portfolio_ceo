<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($term): void {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('customer_code', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();
        $pendingApprovalCount = Customer::where('status', 'pending_approval')->count();
        $pendingVerificationCount = Customer::where('status', 'pending_verification')->count();
        $approvedCount = Customer::where('status', 'active')->count();

        return view('admin.customer-accounts.index', compact(
            'customers',
            'pendingApprovalCount',
            'pendingVerificationCount',
            'approvedCount'
        ));
    }

    public function approve(Customer $customer): RedirectResponse
    {
        abort_unless($customer->hasVerifiedEmail(), 422, 'The customer must verify their email before approval.');
        abort_unless($customer->status === 'pending_approval', 422, 'This customer account is not waiting for approval.');

        $customer->forceFill(['status' => 'active'])->save();

        return back()->with('success', 'Customer account approved. The customer can now access the dashboard.');
    }

    public function reject(Customer $customer): RedirectResponse
    {
        abort_unless($customer->status === 'pending_approval', 422, 'Only verified accounts waiting for approval can be declined.');

        $customer->forceFill(['status' => 'rejected'])->save();

        return back()->with('success', 'Customer account request declined.');
    }
}
