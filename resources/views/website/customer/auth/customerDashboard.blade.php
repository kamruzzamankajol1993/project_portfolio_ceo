@extends('website.master.master')

@section('title')
   Customer Dashboard - {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="editor-wrap customer-dashboard-wrap">
    <div class="editor-heading">
        <div>
            <div class="breadcrumb-line"><a href="{{ route('site.home') }}">Home</a><i class="bi bi-chevron-right"></i><strong>My dashboard</strong></div>
            <span class="auth-eyebrow">CUSTOMER WORKSPACE</span>
            <h1>Welcome, {{ $customer->name }}</h1>
            <p>Manage your account and track your personal or company profile creation requests.</p>
        </div>
        <span class="profile-status"><i class="bi bi-patch-check-fill"></i> Account approved</span>
    </div>

    @if(session('status'))
        <div class="customer-notice customer-notice-success" role="status"><i class="bi bi-check-circle-fill"></i>{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="customer-notice customer-notice-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="editor-layout customer-dashboard-layout">
        <aside class="editor-sidebar">
            <div class="editor-user-card">
                <div class="editor-user-avatar"><i class="bi bi-person-fill"></i></div>
                <div><strong>{{ $customer->name }}</strong><small>{{ $customer->customer_code }}</small></div>
            </div>
            <span class="editor-nav-label">MY ACCOUNT</span>
            @php($managedPersonalProfile = $profiles->firstWhere('profile_type','personal'))
            @php($managedCompanyProfile = $profiles->firstWhere('profile_type','company'))
            <nav class="editor-nav" aria-label="Customer account navigation">
                <a class="active" href="{{ route('customer.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
                <a href="{{ route('customer.profile') }}"><i class="bi bi-person-gear"></i> Profile Settings</a>
                @if($managedPersonalProfile || in_array('personal', $approvedProfileTypes, true))
                    <a href="{{ route('customer.personal-profile-update', $managedPersonalProfile ? ['profile'=>$managedPersonalProfile->id] : []) }}"><i class="bi bi-person-vcard"></i> Personal Profile</a>
                @endif
                @if($managedCompanyProfile || in_array('company', $approvedProfileTypes, true))
                    <a href="{{ route('customer.company-profile-update', $managedCompanyProfile ? ['profile'=>$managedCompanyProfile->id] : []) }}"><i class="bi bi-buildings"></i> Company Profile</a>
                @endif
            </nav>
            <div class="editor-sidebar-note"><i class="bi bi-shield-check"></i><strong>Your account is approved.</strong><p>Profile creation access is managed separately and requires its own administrator approval.</p></div>
            <form action="{{ route('customer.logout') }}" method="post" data-server-form>
                @csrf
                <button class="editor-back-link customer-logout-button" type="submit"><i class="bi bi-box-arrow-left"></i> Sign out</button>
            </form>
        </aside>

        <section class="editor-main customer-dashboard-main">
            <div class="customer-stat-grid" aria-label="Profile request summary">
                <article class="customer-stat-card"><span class="customer-stat-icon"><i class="bi bi-file-earmark-text"></i></span><div><small>Total Requests</small><strong>{{ $totalRequests }}</strong></div></article>
                <article class="customer-stat-card"><span class="customer-stat-icon customer-stat-icon-green"><i class="bi bi-check2-circle"></i></span><div><small>Approved</small><strong>{{ $approvedRequests }}</strong></div></article>
                <article class="customer-stat-card"><span class="customer-stat-icon customer-stat-icon-orange"><i class="bi bi-hourglass-split"></i></span><div><small>Pending</small><strong>{{ $pendingRequests }}</strong></div></article>
            </div>

            <section class="editor-card customer-request-card">
                <div class="editor-card-heading">
                    <div><span class="editor-section-icon"><i class="bi bi-person-vcard"></i></span><div><h2>My profiles</h2><p>Manage profile drafts, submissions, published pages and returned corrections.</p></div></div>
                    <span class="section-step">{{ $profiles->count() }} PROFILE(S)</span>
                </div>
                @forelse($profiles as $managedProfile)
                    @php
                        $isPersonalProfile = $managedProfile->profile_type === 'personal';
                        $latestVersion = $managedProfile->latestVersion;
                        $displayDetails = $managedProfile->publishedVersion ?: $latestVersion;
                        $displayProfileDetails = $isPersonalProfile ? $displayDetails?->personalDetails : $displayDetails?->companyDetails;
                        $managedProfileName = $isPersonalProfile ? ($displayProfileDetails?->full_name ?? $managedProfile->slug) : ($displayProfileDetails?->company_name ?? $managedProfile->slug);
                        $reviewState = $latestVersion?->review_status ?: 'draft';
                        $reviewLabel = $reviewState === 'pending' ? 'Waiting for admin review' : ($reviewState === 'changes_requested' ? 'Correction requested' : ($managedProfile->status === 'published' && $reviewState === 'approved' ? 'Published' : str($reviewState)->replace('_',' ')->title()));
                        $reviewClass = $reviewState === 'pending' ? 'customer-status-pending' : ($reviewState === 'changes_requested' || $reviewState === 'rejected' ? 'customer-status-rejected' : ($managedProfile->status === 'published' && $reviewState === 'approved' ? 'customer-status-approved' : 'customer-status-pending'));
                        $editRoute = $isPersonalProfile ? 'customer.personal-profile-update' : 'customer.company-profile-update';
                    @endphp
                    <div class="customer-inline-state mb-2">
                        <i class="bi bi-{{ $isPersonalProfile ? 'person-vcard' : 'buildings' }}"></i>
                        <div class="flex-grow-1"><strong>{{ $managedProfileName }}</strong><p class="mb-1">{{ ucfirst($managedProfile->profile_type) }} profile · Version {{ $latestVersion?->version_number ?? '—' }}</p><span class="customer-status {{ $reviewClass }}">{{ $reviewLabel }}</span>@if($latestVersion?->review_note)<p class="mt-2 mb-0"><strong>Admin note:</strong> {{ $latestVersion->review_note }}</p>@endif</div>
                        <div class="d-flex flex-wrap gap-2">@if($managedProfile->status==='published')<a class="btn btn-sm btn-light" href="{{ $managedProfile->publicUrl() }}" target="_blank" rel="noopener">View</a>@endif<a class="btn btn-sm btn-outline-primary" href="{{ route($editRoute,['profile'=>$managedProfile->id]) }}">{{ $reviewState === 'pending' ? 'View status' : 'Manage' }}</a></div>
                    </div>
                @empty
                    <div class="customer-inline-state"><i class="bi bi-info-circle"></i><div><strong>You have not created a profile yet.</strong><p>Once your profile creation access is approved, you can create a personal or company profile here.</p></div></div>
                @endforelse
                <div class="customer-request-actions">
                    <small>Every submitted version is reviewed before it replaces the public profile.</small>
                    <div class="d-flex flex-wrap gap-2">
                        @if(in_array('personal',$approvedProfileTypes,true))<a class="btn btn-outline-primary" href="{{ route('customer.personal-profile-update') }}"><i class="bi bi-plus-lg"></i> New personal profile</a>@endif
                        @if(in_array('company',$approvedProfileTypes,true))<a class="btn btn-outline-primary" href="{{ route('customer.company-profile-update') }}"><i class="bi bi-plus-lg"></i> New company profile</a>@endif
                    </div>
                </div>
            </section>

            <section class="editor-card customer-request-card">
                <div class="editor-card-heading">
                    <div><span class="editor-section-icon"><i class="bi bi-plus-circle"></i></span><div><h2>Request a new profile</h2><p>Choose which type of public profile you want to create.</p></div></div>
                    <span class="section-step">REQUEST</span>
                </div>

                @if($hasPendingRequest)
                    <div class="customer-inline-state"><i class="bi bi-hourglass-split"></i><div><strong>Your request is under review.</strong><p>You can submit another profile request after the administrator reviews the current one.</p></div></div>
                @else
                    <form class="customer-request-form" action="{{ route('customer.profile-requests.store') }}" method="post" data-server-form>
                        @csrf
                        <div class="editor-form-grid">
                            <div class="form-field">
                                <label for="requestedProfileType">Profile type <span class="required-star">*</span></label>
                                <select id="requestedProfileType" name="requested_profile_type" required>
                                    <option value="">Select profile type</option>
                                    <option value="personal" @selected(old('requested_profile_type') === 'personal') @disabled(in_array('personal', $approvedProfileTypes, true))>Personal profile</option>
                                    <option value="company" @selected(old('requested_profile_type') === 'company') @disabled(in_array('company', $approvedProfileTypes, true))>Company profile</option>
                                    <option value="both" @selected(old('requested_profile_type') === 'both') @disabled(count($approvedProfileTypes) > 0)>Both personal and company profiles</option>
                                </select>
                                @error('requested_profile_type')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-field">
                                <label for="profileRequestMessage">Additional information <span class="optional-label">Optional</span></label>
                                <textarea id="profileRequestMessage" name="message" rows="2" placeholder="Tell us briefly why you need this profile...">{{ old('message') }}</textarea>
                                @error('message')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="customer-request-actions"><small>Our team will review your request before enabling profile creation.</small><button class="btn btn-primary" type="submit">Submit Request <i class="bi bi-arrow-right"></i></button></div>
                    </form>
                @endif

                @if(count($approvedProfileTypes) > 0)
                    <div class="customer-access-list">
                        <span>Profile creation access:</span>
                        @foreach($approvedProfileTypes as $profileType)
                            <span class="customer-access-tag"><i class="bi bi-check-circle-fill"></i>{{ ucfirst($profileType) }}</span>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="editor-card customer-latest-requests">
                <div class="editor-card-heading">
                    <div><span class="editor-section-icon"><i class="bi bi-clock-history"></i></span><div><h2>Latest profile creation requests</h2><p>Your most recent requests and their review status.</p></div></div>
                    <span class="section-step">HISTORY</span>
                </div>
                <div class="table-responsive">
                    <table class="customer-request-table">
                        <thead><tr><th>Profile type</th><th>Request date</th><th>Status</th><th>Admin note</th></tr></thead>
                        <tbody>
                            @forelse($requests as $profileRequest)
                                <tr>
                                    <td>{{ str($profileRequest->requested_profile_type)->title() }}</td>
                                    <td>{{ $profileRequest->created_at?->format('d M Y') }}</td>
                                    <td>
                                        @php($requestStatusClass = $profileRequest->status === 'approved' ? 'customer-status-approved' : ($profileRequest->status === 'rejected' ? 'customer-status-rejected' : 'customer-status-pending'))
                                        <span class="customer-status {{ $requestStatusClass }}">{{ ucfirst($profileRequest->status) }}</span>
                                    </td>
                                    <td>{{ $profileRequest->review_note ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="customer-empty-state">You have not submitted any profile creation requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="editor-card customer-latest-requests">
                <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-arrow-left-right"></i></span><div><h2>Profile review history</h2><p>Admin feedback, returned corrections, customer resubmissions and approvals.</p></div></div><a class="btn btn-sm btn-outline-primary" href="{{ route('customer.profile-history') }}">View full history</a></div>
                <div class="table-responsive"><table class="customer-request-table"><thead><tr><th>Action</th><th>Profile</th><th>By</th><th>Date</th><th>Message</th></tr></thead><tbody>
                    @forelse($reviewHistory as $history)
                        @php($historyProfile = $history->version?->profile)
                        @php($historyDetails = $historyProfile?->profile_type==='personal' ? $history->version?->personalDetails : $history->version?->companyDetails)
                        @php($historyName = $historyProfile?->profile_type==='personal' ? ($historyDetails?->full_name ?? $historyProfile?->slug) : ($historyDetails?->company_name ?? $historyProfile?->slug))
                        <tr><td>{{ str($history->action)->replace('_',' ')->title() }}</td><td>{{ $historyName ?: '—' }}</td><td>{{ $history->admin?->name ?? $history->customer?->name ?? 'System' }}</td><td>{{ $history->created_at?->format('d M Y, h:i A') }}</td><td>{{ $history->note ?: '—' }}</td></tr>
                    @empty<tr><td colspan="5" class="customer-empty-state">No profile review activity yet.</td></tr>@endforelse
                </tbody></table></div>
            </section>
        </section>

        <aside class="editor-aside">
            <div class="editor-info-card">
                <span class="info-card-icon"><i class="bi bi-person-check"></i></span>
                <h3>Account information</h3>
                <p><strong>{{ $customer->name }}</strong><br>{{ $customer->email }}<br>{{ $customer->phone ?: 'No phone number added' }}</p>
                <a class="customer-side-link" href="{{ route('customer.profile') }}">Update account details <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="editor-info-card pale-info">
                <span class="info-card-icon"><i class="bi bi-shield-lock"></i></span>
                <h3>Three approval stages</h3>
                <p>Your account is approved first, profile creation access is granted separately, and every submitted profile version is reviewed before publication.</p>
                <div class="review-flow"><b>Account</b><i class="bi bi-arrow-right"></i><b>Access</b><i class="bi bi-arrow-right"></i><b>Publish</b></div>
            </div>
        </aside>
    </div>
</main>
@endsection

@section('scripts')
<script src="{{ asset('/') }}public/website/account-pages.js"></script>
@endsection
