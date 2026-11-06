@extends('website.master.master')

@section('title')
   Profile Settings - {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="editor-wrap customer-settings-wrap">
    <div class="editor-heading">
        <div>
            <div class="breadcrumb-line"><a href="{{ route('customer.dashboard') }}">Dashboard</a><i class="bi bi-chevron-right"></i><strong>Profile settings</strong></div>
            <span class="auth-eyebrow">ACCOUNT MANAGEMENT</span>
            <h1>Profile Settings</h1>
            <p>Update your own customer account information and sign-in credentials.</p>
        </div>
        <span class="profile-status"><i class="bi bi-person-gear"></i> Personal account</span>
    </div>

    @if(session('success'))
        <div class="customer-notice customer-notice-success" role="status"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="customer-notice customer-notice-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="editor-layout customer-settings-layout">
        <aside class="editor-sidebar">
            <div class="editor-user-card"><div class="editor-user-avatar"><i class="bi bi-person-fill"></i></div><div><strong>{{ $customer->name }}</strong><small>{{ $customer->customer_code }}</small></div></div>
            <span class="editor-nav-label">MY ACCOUNT</span>
            <nav class="editor-nav" aria-label="Customer account navigation">
                <a href="{{ route('customer.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
                <a class="active" href="{{ route('customer.profile') }}"><i class="bi bi-person-gear"></i> Profile Settings</a>
            </nav>
            <div class="editor-sidebar-note"><i class="bi bi-shield-check"></i><strong>Account details only.</strong><p>These settings update your customer login account, not your personal or company public profile.</p></div>
            <a class="editor-back-link" href="{{ route('customer.dashboard') }}"><i class="bi bi-arrow-left"></i> Back to dashboard</a>
        </aside>

        <section class="editor-main">
            <form class="profile-editor-form" action="{{ route('customer.profile.update') }}" method="post" data-server-form>
                @csrf
                @method('PUT')
                <section class="editor-card">
                    <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-person-lines-fill"></i></span><div><h2>Account details</h2><p>Only your customer account information is updated here.</p></div></div><span class="section-step">01</span></div>
                    <div class="editor-form-grid">
                        <div class="form-field"><label for="accountName">Full name <span class="required-star">*</span></label><input id="accountName" name="name" type="text" value="{{ old('name', $customer->name) }}" maxlength="180" required>@error('name')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field"><label for="accountPhone">Phone number</label><input id="accountPhone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" maxlength="30">@error('phone')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field field-span-2"><label for="accountEmail">Email address <span class="required-star">*</span></label><input id="accountEmail" name="email" type="email" value="{{ old('email', $customer->email) }}" maxlength="191" required>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror<small class="field-hint">Changing your email requires verification and administrator approval again.</small></div>
                    </div>
                </section>

                <section class="editor-card">
                    <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-lock"></i></span><div><h2>Change password</h2><p>Leave the new password fields blank if you do not want to change it.</p></div></div><span class="section-step">02</span></div>
                    <div class="editor-form-grid">
                        <div class="form-field field-span-2"><label for="currentPassword">Current password <span class="optional-label">Required for email or password changes</span></label><input id="currentPassword" name="current_password" type="password" autocomplete="current-password">@error('current_password')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field"><label for="newPassword">New password</label><input id="newPassword" name="password" type="password" autocomplete="new-password" minlength="8">@error('password')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field"><label for="confirmNewPassword">Confirm new password</label><input id="confirmNewPassword" name="password_confirmation" type="password" autocomplete="new-password" minlength="8"></div>
                    </div>
                </section>

                <div class="editor-form-actions">
                    <div class="save-feedback"><i class="bi bi-shield-check"></i> Your account information is private.</div>
                    <div class="action-buttons">
                        <a class="btn btn-light cancel-button" href="{{ route('customer.dashboard') }}">Cancel</a>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save Account Details</button>
                    </div>
                </div>
            </form>
        </section>

        <aside class="editor-aside">
            <div class="editor-info-card"><span class="info-card-icon"><i class="bi bi-envelope-check"></i></span><h3>Email status</h3><p>{{ $customer->hasVerifiedEmail() ? 'Your email address is verified.' : 'Your email address is not verified.' }}</p></div>
            <div class="editor-info-card pale-info"><span class="info-card-icon"><i class="bi bi-info-circle"></i></span><h3>Separate profile information</h3><p>Personal profile and company profile content are managed separately after the relevant profile request has been approved.</p></div>
        </aside>
    </div>
</main>
@endsection

@section('scripts')
<script src="{{ asset('/') }}public/website/account-pages.js"></script>
@endsection
