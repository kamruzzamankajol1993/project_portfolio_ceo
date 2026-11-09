@extends('admin.master.master')

@section('title', 'Edit Customer Account')

@section('body')
<div class="module-heading">
    <div>
        <div class="eyebrow">CUSTOMER ACCESS</div>
        <h1><span class="heading-icon"><i class="bi bi-person-gear"></i></span>Edit Customer Account</h1>
        <p>Update the customer's contact information and account details.</p>
    </div>
    <a href="{{ route('admin.customer-accounts.index', $returnFilters) }}" class="module-secondary"><i class="bi bi-arrow-left"></i> Back to accounts</a>
</div>

<section class="module-panel module-form module-editor">
    <form method="post" action="{{ route('admin.customer-accounts.update', $customer) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_search" value="{{ $returnFilters['search'] ?? '' }}">
        <input type="hidden" name="return_status" value="{{ $returnFilters['status'] ?? '' }}">
        <input type="hidden" name="return_page" value="{{ $returnFilters['page'] ?? '' }}">

        <div class="customer-account-meta mb-3">
            <span><strong>Customer ID:</strong> {{ $customer->customer_code }}</span>
            <span><strong>Registered:</strong> {{ $customer->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
            <span><strong>Email status:</strong> {{ $customer->hasVerifiedEmail() ? 'Verified' : 'Not verified' }}</span>
        </div>

        <div class="form-grid">
            <label>Full name *
                <input class="form-control" name="name" value="{{ old('name', $customer->name) }}" maxlength="180" required>
            </label>
            <label>Email address *
                <input class="form-control" type="email" name="email" value="{{ old('email', $customer->email) }}" maxlength="191" required>
            </label>
            <label>Phone number
                <input class="form-control" type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" maxlength="30">
            </label>
            <label>Account status
                <input class="form-control" value="{{ str($customer->status)->replace('_', ' ')->title() }}" readonly disabled>
            </label>
        </div>

        <div class="alert alert-info small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            If you change the email address, its verification will be reset and the customer must verify the new address before signing in again.
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.customer-accounts.index', $returnFilters) }}" class="module-secondary me-2">Cancel</a>
            <button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save changes</button>
        </div>
    </form>
</section>
@endsection
