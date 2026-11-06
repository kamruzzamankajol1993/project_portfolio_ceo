<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Keeps the existing public profile-editor endpoints in place.
 * Account authentication and account settings are handled by dedicated controllers.
 */
class CustomerloginController extends Controller
{
    public function companyProfileUpdate()
    {
        $customer = Auth::guard('customer')->user();

        abort_unless(
            $customer->profileTypeAccess()->active()->where('profile_type', 'company')->exists(),
            403,
            'Your company profile creation request must be approved before you can continue.'
        );

        return view('website.customer.companyProfileUpdate');
    }

    public function personalProfileUpdate()
    {
        $customer = Auth::guard('customer')->user();

        abort_unless(
            $customer->profileTypeAccess()->active()->where('profile_type', 'personal')->exists(),
            403,
            'Your personal profile creation request must be approved before you can continue.'
        );

        return view('website.customer.personalProfileUpdate');
    }
}
