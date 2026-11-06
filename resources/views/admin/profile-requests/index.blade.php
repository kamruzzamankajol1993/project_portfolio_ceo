@extends('admin.master.master')

@section('title', 'Profile Requests')

@section('body')
<div class="module-heading">
    <div>
        <div class="eyebrow">PROFILE ACCESS</div>
        <h1><span class="heading-icon"><i class="bi bi-clipboard-check-fill"></i></span>Profile Requests</h1>
        <p>Approve or decline customer requests to create personal and company profiles.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="module-panel h-100"><div class="text-muted small">Pending requests</div><div class="fs-3 fw-bold text-primary">{{ $pendingCount }}</div></div></div>
    <div class="col-md-4"><div class="module-panel h-100"><div class="text-muted small">Approved requests</div><div class="fs-3 fw-bold text-success">{{ $approvedCount }}</div></div></div>
    <div class="col-md-4"><div class="module-panel h-100"><div class="text-muted small">Rejected requests</div><div class="fs-3 fw-bold">{{ $rejectedCount }}</div></div></div>
</div>

<section class="module-panel module-list-panel">
    <div class="module-toolbar">
        <div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Profile creation access requests</div>
        <form class="module-search" action="{{ route('admin.profile-requests.index') }}" method="get" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search customer name or email" aria-label="Search profile requests">
        </form>
    </div>

    <form class="d-flex flex-wrap align-items-center gap-2 mb-3" action="{{ route('admin.profile-requests.index') }}" method="get">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <label class="small fw-semibold text-secondary" for="profileRequestStatus">Request status</label>
        <select class="form-select form-select-sm" style="max-width:180px" id="profileRequestStatus" name="status">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
        <button class="module-secondary" type="submit">Filter</button>
        <a class="small text-decoration-none" href="{{ route('admin.profile-requests.index') }}">Clear</a>
    </form>

    @include('admin.profile-requests._table')
</section>
@endsection
