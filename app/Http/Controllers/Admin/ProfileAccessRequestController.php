<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfileAccess;
use App\Models\ProfileAccessRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileAccessRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProfileAccessRequest::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->whereHas('customer', function ($builder) use ($term): void {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $requests = $query->latest('id')->paginate(15)->withQueryString();
        $pendingCount = ProfileAccessRequest::where('status', 'pending')->count();
        $approvedCount = ProfileAccessRequest::where('status', 'approved')->count();
        $rejectedCount = ProfileAccessRequest::where('status', 'rejected')->count();

        return view('admin.profile-requests.index', compact(
            'requests',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function approve(ProfileAccessRequest $profileRequest): RedirectResponse
    {
        DB::transaction(function () use ($profileRequest): void {
            $profileRequest = ProfileAccessRequest::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($profileRequest->id);

            abort_unless($profileRequest->status === 'pending', 422, 'This profile request has already been reviewed.');
            abort_unless($profileRequest->customer?->isActive(), 422, 'The customer account must be active before profile access can be granted.');

            $profileTypes = $profileRequest->requested_profile_type === 'both'
                ? ['personal', 'company']
                : [$profileRequest->requested_profile_type];

            foreach ($profileTypes as $profileType) {
                CustomerProfileAccess::updateOrCreate(
                    [
                        'customer_id' => $profileRequest->customer_id,
                        'profile_type' => $profileType,
                    ],
                    [
                        'can_create' => true,
                        'granted_by_admin_id' => auth()->id(),
                        'granted_at' => now(),
                        'revoked_at' => null,
                    ]
                );
            }

            $profileRequest->forceFill([
                'status' => 'approved',
                'reviewed_by_admin_id' => auth()->id(),
                'reviewed_at' => now(),
                'review_note' => null,
            ])->save();
        });

        return back()->with('success', 'Profile request approved. The customer can now start the selected profile type.');
    }

    public function reject(Request $request, ProfileAccessRequest $profileRequest): RedirectResponse
    {
        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($profileRequest->status === 'pending', 422, 'This profile request has already been reviewed.');

        $profileRequest->forceFill([
            'status' => 'rejected',
            'reviewed_by_admin_id' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $data['review_note'] ?? null,
        ])->save();

        return back()->with('success', 'Profile request declined.');
    }
}
