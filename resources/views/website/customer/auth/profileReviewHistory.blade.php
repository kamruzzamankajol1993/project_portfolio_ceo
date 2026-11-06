@extends('website.master.master')
@section('title', 'Profile Review History - ' . $siteSetting->site_name)
@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection
@section('body')
<main class="editor-wrap customer-dashboard-wrap">
    <div class="editor-heading">
        <div>
            <div class="breadcrumb-line"><a href="{{ route('site.home') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('customer.dashboard') }}">My dashboard</a><i class="bi bi-chevron-right"></i><strong>Profile history</strong></div>
            <span class="auth-eyebrow">CUSTOMER WORKSPACE</span>
            <h1>Profile Review History</h1>
            <p>Track every profile submission, admin feedback, returned correction, resubmission and publication.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('customer.dashboard') }}"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <section class="editor-card customer-latest-requests">
        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-clock-history"></i></span><div><h2>All profile activity</h2><p>Your complete profile revision and review timeline.</p></div></div><span class="section-step">{{ $reviewHistory->total() }} EVENTS</span></div>
        <div class="table-responsive">
            <table class="customer-request-table">
                <thead><tr><th>Action</th><th>Profile</th><th>Version</th><th>By</th><th>Date</th><th>Message</th></tr></thead>
                <tbody>
                @forelse($reviewHistory as $history)
                    @php
                        $profile = $history->version?->profile;
                        $details = $profile?->profile_type === 'personal' ? $history->version?->personalDetails : $history->version?->companyDetails;
                        $name = $profile?->profile_type === 'personal' ? ($details?->full_name ?? $profile?->slug) : ($details?->company_name ?? $profile?->slug);
                    @endphp
                    <tr>
                        <td>{{ str($history->action)->replace('_',' ')->title() }}</td>
                        <td>{{ $name ?: '—' }}</td>
                        <td>{{ $history->version ? 'v'.$history->version->version_number : '—' }}</td>
                        <td>{{ $history->admin?->name ?? $history->customer?->name ?? 'System' }}</td>
                        <td>{{ $history->created_at?->format('d M Y, h:i A') }}</td>
                        <td>{{ $history->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="customer-empty-state">No profile activity yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $reviewHistory->links() }}</div>
    </section>
</main>
@endsection
