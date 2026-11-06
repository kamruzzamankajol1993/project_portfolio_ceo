<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerProfileAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerProfileAccessRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'requested_profile_type' => ['required', Rule::in(['personal', 'company', 'both'])],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($customer->accessRequests()->where('status', 'pending')->exists()) {
            return back()->withErrors([
                'requested_profile_type' => 'You already have a profile request waiting for review.',
            ]);
        }

        $requestedTypes = $data['requested_profile_type'] === 'both'
            ? ['personal', 'company']
            : [$data['requested_profile_type']];

        $alreadyGranted = CustomerProfileAccess::query()
            ->active()
            ->where('customer_id', $customer->id)
            ->whereIn('profile_type', $requestedTypes)
            ->exists();

        if ($alreadyGranted) {
            return back()->withErrors([
                'requested_profile_type' => 'You already have access to at least one selected profile type. Please request only a profile type you do not have access to.',
            ]);
        }

        $customer->accessRequests()->create([
            'requested_profile_type' => $data['requested_profile_type'],
            'status' => 'pending',
            'message' => $data['message'] ?? null,
        ]);

        return redirect()->route('customer.dashboard')->with(
            'status',
            'Your profile creation request has been submitted for administrator review.'
        );
    }
}
