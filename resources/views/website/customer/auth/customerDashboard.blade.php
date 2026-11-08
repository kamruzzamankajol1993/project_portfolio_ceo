@extends('website.master.master')

@section('title', 'My dashboard | ' . $siteSetting->site_name)

@section('body')
@php
    $managedPersonalProfile = $profiles->firstWhere('profile_type', 'personal');
    $managedCompanyProfile = $profiles->firstWhere('profile_type', 'company');

    $recentEvents = collect();
    foreach ($reviewHistory as $history) {
        $historyProfile = $history->version?->profile;
        $historyDetails = $historyProfile?->profile_type === 'personal'
            ? $history->version?->personalDetails
            : $history->version?->companyDetails;
        $historyName = $historyProfile?->profile_type === 'personal'
            ? ($historyDetails?->full_name ?: $historyProfile?->slug)
            : ($historyDetails?->company_name ?: $historyProfile?->slug);
        $recentEvents->push([
            'date' => $history->created_at,
            'title' => str($history->action)->replace('_', ' ')->title()->toString(),
            'detail' => $historyName ?: 'Profile update',
            'state' => in_array($history->action, ['approved', 'published'], true) ? 'approved' : (in_array($history->action, ['submitted', 'returned_for_changes'], true) ? 'pending' : 'neutral'),
        ]);
    }
    foreach ($requests as $profileRequest) {
        $recentEvents->push([
            'date' => $profileRequest->created_at,
            'title' => str($profileRequest->requested_profile_type)->title() . ' profile access request ' . $profileRequest->status,
            'detail' => $profileRequest->review_note ?: 'Profile creation access request',
            'state' => $profileRequest->status === 'approved' ? 'approved' : ($profileRequest->status === 'pending' ? 'pending' : 'neutral'),
        ]);
    }
    $recentEvents = $recentEvents->sortByDesc(fn ($event) => $event['date']?->timestamp ?? 0)->take(5)->values();
@endphp

<main class="account-wrap">
    <div class="account-heading">
        <div>
            <div class="breadcrumb-line"><a href="{{ route('site.home') }}">Home</a><i class="bi bi-chevron-right"></i><strong>My dashboard</strong></div>
            <span class="auth-eyebrow">CUSTOMER WORKSPACE</span>
            <h1>Welcome back, {{ $customer->name }}</h1>
            <p>Manage your account and track your personal or company profile requests.</p>
        </div>
        <span class="profile-status"><i class="bi bi-patch-check-fill"></i> Account approved</span>
    </div>

    @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

    <div class="account-layout">
        <aside class="account-sidebar">
            <div class="account-user-card">
                <span class="profile-list-icon"><i class="bi bi-person-fill"></i></span>
                <div><strong>{{ $customer->name }}</strong><small>{{ $customer->customer_code }}</small></div>
            </div>
            <span class="account-nav-label">MY ACCOUNT</span>
            <nav class="account-nav">
                <a class="active" href="{{ route('customer.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
                <a href="{{ route('customer.profile') }}"><i class="bi bi-person-gear"></i> Profile Settings</a>
                @if($managedPersonalProfile || in_array('personal', $approvedProfileTypes, true))
                    <a href="{{ route('customer.personal-profile-update', $managedPersonalProfile ? ['profile' => $managedPersonalProfile->id] : []) }}"><i class="bi bi-person-vcard"></i> Personal Profile</a>
                @endif
                @if($managedCompanyProfile || in_array('company', $approvedProfileTypes, true))
                    <a href="{{ route('customer.company-profile-update', $managedCompanyProfile ? ['profile' => $managedCompanyProfile->id] : []) }}"><i class="bi bi-buildings"></i> Company Profile</a>
                @endif
                <a href="{{ route('customer.profile-history') }}"><i class="bi bi-clock-history"></i> Review History</a>
            </nav>
            <div class="account-sidebar-note"><i class="bi bi-shield-check"></i><strong>Your account is approved.</strong><p>Profile publishing access is reviewed separately by the {{ $siteSetting->site_name }} team.</p></div>
            <a class="account-signout" href="{{ route('customer.profile') }}"><i class="bi bi-person-gear"></i> Account settings</a>
            <form action="{{ route('customer.logout') }}" method="post" data-server-form>@csrf<button class="account-signout w-100" type="submit"><i class="bi bi-box-arrow-left"></i> Sign out</button></form>
        </aside>

        <section class="account-main">
            <div class="customer-stat-grid">
                <article class="customer-stat-card"><span class="customer-stat-icon"><i class="bi bi-file-earmark-text"></i></span><div><small>Total Requests</small><strong>{{ $totalRequests }}</strong><span>Across all profiles</span></div></article>
                <article class="customer-stat-card"><span class="customer-stat-icon stat-green"><i class="bi bi-check2-circle"></i></span><div><small>Approved</small><strong>{{ $approvedRequests }}</strong><span>Approved requests</span></div></article>
                <article class="customer-stat-card"><span class="customer-stat-icon stat-orange"><i class="bi bi-hourglass-split"></i></span><div><small>Pending Review</small><strong>{{ $pendingRequests }}</strong><span>Awaiting administrator</span></div></article>
            </div>

            <section class="editor-card">
                <div class="editor-card-heading">
                    <div><span class="editor-section-icon"><i class="bi bi-person-vcard"></i></span><div><h2>My profiles</h2><p>Manage profile drafts, submissions, published pages and requested corrections.</p></div></div>
                    <span class="section-step">{{ $profiles->count() }} PROFILE(S)</span>
                </div>
                @forelse($profiles as $managedProfile)
                    @php
                        $isPersonalProfile = $managedProfile->profile_type === 'personal';
                        $latestVersion = $managedProfile->latestVersion;
                        $displayVersion = $managedProfile->publishedVersion ?: $latestVersion;
                        $displayDetails = $isPersonalProfile ? $displayVersion?->personalDetails : $displayVersion?->companyDetails;
                        $managedProfileName = $isPersonalProfile ? ($displayDetails?->full_name ?? $managedProfile->slug) : ($displayDetails?->company_name ?? $managedProfile->slug);
                        $reviewState = $latestVersion?->review_status ?: 'draft';
                        $reviewLabel = $reviewState === 'pending' ? 'Waiting for admin review' : ($reviewState === 'changes_requested' ? 'Correction requested' : ($managedProfile->status === 'published' && $reviewState === 'approved' ? 'Published' : str($reviewState)->replace('_',' ')->title()));
                        $reviewClass = $reviewState === 'pending' ? 'customer-status-pending' : ($reviewState === 'changes_requested' || $reviewState === 'rejected' ? 'customer-status-rejected' : ($managedProfile->status === 'published' && $reviewState === 'approved' ? 'customer-status-approved' : 'customer-status-pending'));
                        $editRoute = $isPersonalProfile ? 'customer.personal-profile-update' : 'customer.company-profile-update';
                    @endphp
                    <div class="profile-list-row">
                        <span class="profile-list-icon {{ $isPersonalProfile ? '' : 'company' }}"><i class="bi bi-{{ $isPersonalProfile ? 'person-badge' : 'buildings' }}"></i></span>
                        <div class="profile-list-copy">
                            <strong>{{ $managedProfileName }}</strong>
                            <small>{{ ucfirst($managedProfile->profile_type) }} profile · Version {{ $latestVersion?->version_number ?? '—' }} · Updated {{ optional($managedProfile->updated_at)->format('d M Y') }}</small>
                            <span class="customer-status {{ $reviewClass }}">{{ $reviewLabel }}</span>
                            @if($latestVersion?->review_note)<small class="d-block mt-2"><strong>Admin note:</strong> {{ $latestVersion->review_note }}</small>@endif
                        </div>
                        <div class="profile-list-actions">
                            @if($managedProfile->status === 'published')<a class="btn btn-light btn-sm" href="{{ $managedProfile->publicUrl() }}" target="_blank" rel="noopener">View</a>@endif
                            <a class="btn btn-outline-primary btn-sm" href="{{ route($editRoute, ['profile' => $managedProfile->id]) }}">{{ $reviewState === 'pending' ? 'View status' : 'Manage' }}</a>
                        </div>
                    </div>
                @empty
                    <div class="account-sidebar-note"><strong>You have not created a profile yet.</strong><p>Once your profile creation access is approved, you can create a personal or company profile here.</p></div>
                @endforelse
                <div class="profile-list-footer">
                    <small>Every submitted version is reviewed before it replaces the public profile.</small>
                    <div class="d-flex flex-wrap gap-2">
                        @if(in_array('personal', $approvedProfileTypes, true))<a class="btn btn-outline-primary btn-sm" href="{{ route('customer.personal-profile-update') }}"><i class="bi bi-plus-lg"></i> New personal profile</a>@endif
                        @if(in_array('company', $approvedProfileTypes, true))<a class="btn btn-outline-primary btn-sm" href="{{ route('customer.company-profile-update') }}"><i class="bi bi-plus-lg"></i> New company profile</a>@endif
                    </div>
                </div>
            </section>

            <section class="editor-card">
                <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-clock-history"></i></span><div><h2>Recent activity</h2><p>Latest updates from your profile submissions and review requests.</p></div></div><a href="{{ route('customer.profile-history') }}" class="text-link">View history <i class="bi bi-arrow-right"></i></a></div>
                <div class="activity-list">
                    @forelse($recentEvents as $event)
                        <div><span class="activity-dot {{ $event['state'] }}"></span><p><strong>{{ $event['title'] }}</strong><small>{{ $event['detail'] }}</small></p><span class="activity-time">{{ $event['date']?->diffForHumans() }}</span></div>
                    @empty
                        <div><span class="activity-dot neutral"></span><p><strong>No recent activity yet</strong><small>Your profile submissions and review updates will appear here.</small></p></div>
                    @endforelse
                </div>
            </section>

            <div class="review-info-banner"><i class="bi bi-info-circle"></i><div><strong>How profile review works</strong><p>Submit your changes → {{ $siteSetting->site_name }} editorial review → Approval and publication. Your currently published profile stays live while a new version is being reviewed.</p></div></div>

            <section class="editor-card">
                <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-plus-circle"></i></span><div><h2>Request profile creation access</h2><p>Choose which type of public profile you want to create.</p></div></div><span class="section-step">REQUEST</span></div>
                @if(count($approvedProfileTypes) >= 2)
                    <div class="review-info-banner"><i class="bi bi-check-circle"></i><div><strong>Both profile types are enabled.</strong><p>You can create a personal profile or a company profile using the buttons in the My profiles section.</p></div></div>
                @elseif($hasPendingRequest)
                    <div class="review-info-banner"><i class="bi bi-hourglass-split"></i><div><strong>Your request is under review.</strong><p>You can submit another profile request after the administrator reviews the current one.</p></div></div>
                @else
                    <form action="{{ route('customer.profile-requests.store') }}" method="post" data-server-form class="account-form">
                        @csrf
                        <div class="editor-form-grid">
                            <div class="form-field">
                                <label for="requestedProfileType">Profile type <b>*</b></label>
                                <select id="requestedProfileType" name="requested_profile_type" class="form-select" required>
                                    <option value="">Select profile type</option>
                                    <option value="personal" @selected(old('requested_profile_type') === 'personal') @disabled(in_array('personal', $approvedProfileTypes, true))>Personal profile</option>
                                    <option value="company" @selected(old('requested_profile_type') === 'company') @disabled(in_array('company', $approvedProfileTypes, true))>Company profile</option>
                                    <option value="both" @selected(old('requested_profile_type') === 'both') @disabled(count($approvedProfileTypes) > 0)>Both personal and company profiles</option>
                                </select>
                                @error('requested_profile_type')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-field"><label for="profileRequestMessage">Additional information <small>Optional</small></label><textarea class="form-control" id="profileRequestMessage" name="message" rows="2" placeholder="Tell us briefly why you need this profile...">{{ old('message') }}</textarea>@error('message')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        </div>
                        <div class="editor-form-actions"><small>Our team will review your request before enabling profile creation.</small><button class="btn btn-primary" type="submit">Submit Request <i class="bi bi-arrow-right"></i></button></div>
                    </form>
                @endif
                @if(count($approvedProfileTypes) > 0)
                    <div class="customer-access-list mt-3"><span>Profile creation access:</span>@foreach($approvedProfileTypes as $profileType)<span class="customer-access-tag"><i class="bi bi-check-circle-fill"></i>{{ ucfirst($profileType) }}</span>@endforeach</div>
                @endif
            </section>
        </section>
    </div>
</main>
@endsection
