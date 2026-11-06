<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ProfileReviewLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{

    public function history(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $profileIds = $customer->managedProfiles()->pluck('profiles.id');

        $reviewHistory = ProfileReviewLog::query()
            ->whereHas('version', fn ($query) => $query->whereIn('profile_id', $profileIds))
            ->with(['version.profile', 'version.personalDetails', 'version.companyDetails', 'admin', 'customer'])
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('website.customer.auth.profileReviewHistory', compact('customer', 'reviewHistory'));
    }

    public function index(): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $requests = $customer->accessRequests()->latest('created_at')->limit(8)->get();
        $totalRequests = $customer->accessRequests()->count();
        $approvedRequests = $customer->accessRequests()->where('status', 'approved')->count();
        $pendingRequests = $customer->accessRequests()->where('status', 'pending')->count();

        $approvedProfileTypes = $customer->profileTypeAccess()
            ->active()
            ->pluck('profile_type')
            ->all();

        $profiles = $customer->managedProfiles()
            ->with([
                'publishedVersion.personalDetails',
                'publishedVersion.companyDetails',
                'latestVersion.personalDetails',
                'latestVersion.companyDetails',
                'latestVersion.reviewLogs.admin',
                'latestVersion.reviewLogs.customer',
            ])
            ->orderByDesc('profiles.updated_at')
            ->get();

        $managedProfileTypes = $profiles->pluck('profile_type')->unique()->values()->all();
        $profileIds = $profiles->pluck('id')->all();
        $reviewHistory = ProfileReviewLog::query()
            ->whereHas('version', fn ($query) => $query->whereIn('profile_id', $profileIds))
            ->with(['version.profile', 'version.personalDetails', 'version.companyDetails', 'admin', 'customer'])
            ->latest('created_at')
            ->limit(20)
            ->get();

        $hasPendingRequest = $customer->accessRequests()->where('status', 'pending')->exists();

        return view('website.customer.auth.customerDashboard', compact(
            'customer', 'requests', 'totalRequests', 'approvedRequests', 'pendingRequests',
            'approvedProfileTypes', 'managedProfileTypes', 'profiles', 'reviewHistory', 'hasPendingRequest'
        ));
    }
}
