@extends('admin.master.master')
@section('title', 'Revision History')
@section('body')
<div class="module-heading">
    <div>
        <div class="eyebrow">PROFILE MANAGEMENT</div>
        <h1><span class="heading-icon"><i class="bi bi-clock-history"></i></span>Revision History</h1>
        <p>Complete audit trail for customer submissions, admin approvals, returned corrections and republishing.</p>
    </div>
    <a class="module-secondary" href="{{ route('admin.profiles.submissions') }}"><i class="bi bi-inbox"></i> Pending submissions</a>
</div>

<section class="module-panel module-list-panel">
    <form method="get" class="module-toolbar" action="{{ route('admin.profiles.revision-history') }}">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <select class="form-select" name="type">
                <option value="">All profile types</option>
                <option value="personal" @selected(request('type')==='personal')>Individual</option>
                <option value="company" @selected(request('type')==='company')>Company</option>
            </select>
            <select class="form-select" name="action">
                <option value="">All actions</option>
                @foreach(['submitted','customer_resubmitted','approved','published','changes_requested','rejected','verified','verification_removed','draft_saved'] as $action)
                    <option value="{{ $action }}" @selected(request('action')===$action)>{{ str($action)->replace('_',' ')->title() }}</option>
                @endforeach
            </select>
            <input class="form-control" style="max-width:260px" type="search" name="search" value="{{ request('search') }}" placeholder="Search profile slug">
            <button class="module-secondary" type="submit">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table admin-table module-table">
            <thead><tr><th>Date</th><th>Profile</th><th>Version</th><th>Action</th><th>By</th><th>Note</th><th></th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                @php
                    $profile = $log->version?->profile;
                    $version = $log->version;
                    $details = $profile?->profile_type === 'personal' ? $version?->personalDetails : $version?->companyDetails;
                    $name = $profile?->profile_type === 'personal' ? ($details?->full_name ?? $profile?->slug) : ($details?->company_name ?? $profile?->slug);
                @endphp
                <tr>
                    <td>{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                    <td><strong>{{ $name ?: 'Deleted profile' }}</strong><small class="d-block text-muted">{{ ucfirst($profile?->profile_type ?? '') }}</small></td>
                    <td>{{ $version ? 'v'.$version->version_number : '—' }}</td>
                    <td><span class="soft-tag">{{ str($log->action)->replace('_',' ')->title() }}</span></td>
                    <td>{{ $log->admin?->name ?? $log->customer?->name ?? 'System' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($log->note ?: '—', 100) }}</td>
                    <td>@if($profile)<a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.profiles.history',$profile) }}">Profile history</a>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">No revision history found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.shared._pagination', ['paginator' => $logs])
</section>
@endsection
