@extends('admin.master.master')
@section('title', 'Verification Center')
@section('body')
<div class="module-heading">
    <div>
        <div class="eyebrow">PROFILE MANAGEMENT</div>
        <h1><span class="heading-icon"><i class="bi bi-patch-check"></i></span>Verification Center</h1>
        <p>Review published profile verification status. Verification is separate from content approval.</p>
    </div>
    <a class="module-secondary" href="{{ route('admin.profiles.index') }}">All profiles</a>
</div>

<div class="ops-stats">
    <div class="ops-stat mint"><i class="bi bi-patch-check"></i><div><strong>{{ number_format($stats['verified']) }}</strong><span>Verified profiles</span></div></div>
    <div class="ops-stat violet"><i class="bi bi-patch-exclamation"></i><div><strong>{{ number_format($stats['unverified']) }}</strong><span>Not verified</span></div></div>
    <div class="ops-stat"><i class="bi bi-globe2"></i><div><strong>{{ number_format($stats['published']) }}</strong><span>Published profiles</span></div></div>
</div>

<section class="module-panel module-list-panel">
    <form method="get" class="module-toolbar" action="{{ route('admin.profiles.verification-center') }}">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <select class="form-select" name="type">
                <option value="">All profile types</option>
                <option value="personal" @selected(request('type')==='personal')>Individual</option>
                <option value="company" @selected(request('type')==='company')>Company</option>
            </select>
            <select class="form-select" name="verification">
                <option value="">All verification states</option>
                <option value="verified" @selected(request('verification')==='verified')>Verified</option>
                <option value="unverified" @selected(request('verification')==='unverified')>Not verified</option>
            </select>
            <input class="form-control" style="max-width:260px" type="search" name="search" value="{{ request('search') }}" placeholder="Search profile">
            <button class="module-secondary" type="submit">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table admin-table module-table">
            <thead><tr><th>Profile</th><th>Type</th><th>Owner</th><th>Publication</th><th>Verification</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($profiles as $profile)
                @php
                    $details = $profile->profile_type === 'personal' ? $profile->publishedVersion?->personalDetails : $profile->publishedVersion?->companyDetails;
                    $name = $profile->profile_type === 'personal' ? ($details?->full_name ?? $profile->slug) : ($details?->company_name ?? $profile->slug);
                @endphp
                <tr>
                    <td><strong>{{ $name }}</strong><small class="d-block text-muted">/{{ $profile->slug }}</small></td>
                    <td>{{ $profile->profile_type === 'personal' ? 'Individual' : 'Company' }}</td>
                    <td>{{ $profile->customerAccess->first()?->customer?->name ?? $profile->createdByCustomer?->name ?? 'Admin managed' }}</td>
                    <td>{{ $profile->status === 'published' ? 'Published' : ucfirst($profile->status) }}</td>
                    <td>@if($profile->is_verified)<span class="soft-tag tag-active">Verified</span>@else<span class="soft-tag tag-inactive">Not verified</span>@endif</td>
                    <td>
                        <form method="post" action="{{ route('admin.profiles.verification.update',$profile) }}" class="d-flex flex-wrap gap-2">
                            @csrf
                            <input type="hidden" name="is_verified" value="{{ $profile->is_verified ? 0 : 1 }}">
                            <button class="btn btn-sm {{ $profile->is_verified ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
                                {{ $profile->is_verified ? 'Remove verification' : 'Verify profile' }}
                            </button>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.profiles.history',$profile) }}">History</a>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">No profiles match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.shared._pagination', ['paginator' => $profiles])
</section>
@endsection
