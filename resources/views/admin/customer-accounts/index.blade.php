@extends('admin.master.master')

@section('title', 'Customer Accounts')

@section('body')
<div class="module-heading">
    <div>
        <div class="eyebrow">CUSTOMER ACCESS</div>
        <h1><span class="heading-icon"><i class="bi bi-person-check-fill"></i></span>Customer Accounts</h1>
        <p>Review verified customer accounts and approve access to the customer dashboard.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="module-panel h-100">
            <div class="text-muted small">Waiting for approval</div>
            <div class="fs-3 fw-bold text-primary">{{ $pendingApprovalCount }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="module-panel h-100">
            <div class="text-muted small">Waiting for email verification</div>
            <div class="fs-3 fw-bold">{{ $pendingVerificationCount }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="module-panel h-100">
            <div class="text-muted small">Approved accounts</div>
            <div class="fs-3 fw-bold text-success">{{ $approvedCount }}</div>
        </div>
    </div>
</div>

<section class="module-panel module-list-panel">
    <div class="module-toolbar">
        <div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Customer registration list</div>
        <form class="module-search" action="{{ route('admin.customer-accounts.index') }}" method="get" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email or phone" aria-label="Search customers">
        </form>
    </div>

    <form class="d-flex flex-wrap align-items-center gap-2 mb-3" action="{{ route('admin.customer-accounts.index') }}" method="get">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <label class="small fw-semibold text-secondary" for="customerStatusFilter">Account status</label>
        <select class="form-select form-select-sm" style="max-width:220px" id="customerStatusFilter" name="status">
            <option value="">All statuses</option>
            <option value="pending_verification" @selected(request('status') === 'pending_verification')>Pending verification</option>
            <option value="pending_approval" @selected(request('status') === 'pending_approval')>Pending approval</option>
            <option value="active" @selected(request('status') === 'active')>Approved</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
        <button class="module-secondary" type="submit">Filter</button>
        <a class="small text-decoration-none" href="{{ route('admin.customer-accounts.index') }}">Clear</a>
    </form>

    @include('admin.customer-accounts._table')
</section>
@endsection
