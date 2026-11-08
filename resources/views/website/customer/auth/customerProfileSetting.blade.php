@extends('website.master.master')

@section('title', 'Profile settings | ' . $siteSetting->site_name)

@section('body')
<main class="account-wrap">
    <div class="account-heading">
        <div>
            <div class="breadcrumb-line"><a href="{{ route('customer.dashboard') }}">Dashboard</a><i class="bi bi-chevron-right"></i><strong>Profile settings</strong></div>
            <span class="auth-eyebrow">ACCOUNT MANAGEMENT</span>
            <h1>Profile Settings</h1>
            <p>Update your customer account details and sign-in credentials.</p>
        </div>
        <span class="profile-status"><i class="bi bi-person-gear"></i> Personal account</span>
    </div>

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

    <div class="account-layout account-layout-with-aside">
        <aside class="account-sidebar">
            <div class="account-user-card"><span class="profile-list-icon"><i class="bi bi-person-fill"></i></span><div><strong>{{ $customer->name }}</strong><small>{{ $customer->customer_code }}</small></div></div>
            <span class="account-nav-label">MY ACCOUNT</span>
            <nav class="account-nav">
                <a href="{{ route('customer.dashboard') }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>
                <a class="active" href="{{ route('customer.profile') }}"><i class="bi bi-person-gear"></i> Profile Settings</a>
                @php($managedPersonalProfile = $customer->managedProfiles()->where('profile_type','personal')->first())
                @php($managedCompanyProfile = $customer->managedProfiles()->where('profile_type','company')->first())
                @if($managedPersonalProfile)<a href="{{ route('customer.personal-profile-update', ['profile'=>$managedPersonalProfile->id]) }}"><i class="bi bi-person-vcard"></i> Personal Profile</a>@endif
                @if($managedCompanyProfile)<a href="{{ route('customer.company-profile-update', ['profile'=>$managedCompanyProfile->id]) }}"><i class="bi bi-buildings"></i> Company Profile</a>@endif
            </nav>
            <div class="account-sidebar-note"><i class="bi bi-shield-check"></i><strong>Account details only.</strong><p>These settings update your {{ $siteSetting->site_name }} login account, not your public personal or company profile.</p></div>
            <a class="account-signout" href="{{ route('customer.dashboard') }}"><i class="bi bi-arrow-left"></i> Back to dashboard</a>
        </aside>

        <section class="account-main">
            <form class="account-form editor-form" action="{{ route('customer.profile.update') }}" method="post" data-server-form>
                @csrf
                @method('PUT')
                <section class="editor-card">
                    <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-person-lines-fill"></i></span><div><h2>Account details</h2><p>Only your customer account information is updated here.</p></div></div><span class="section-step">01</span></div>
                    <div class="editor-form-grid">
                        <div class="form-field"><label for="accountName">Full name <b>*</b></label><input class="form-control" id="accountName" name="name" type="text" value="{{ old('name',$customer->name) }}" maxlength="180" required>@error('name')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field"><label for="accountPhone">Phone number</label><input class="form-control" id="accountPhone" name="phone" type="tel" value="{{ old('phone',$customer->phone) }}" maxlength="30">@error('phone')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field field-span-2"><label for="accountEmail">Email address <b>*</b></label><input class="form-control" id="accountEmail" name="email" type="email" value="{{ old('email',$customer->email) }}" maxlength="191" required>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror<small class="field-hint">Changing your email requires verification and administrator approval again.</small></div>
                    </div>
                </section>

                <section class="editor-card">
                    <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-lock"></i></span><div><h2>Change password</h2><p>Leave the new password fields blank if you do not want to change it.</p></div></div><span class="section-step">02</span></div>
                    <div class="editor-form-grid">
                        <div class="form-field field-span-2"><label for="currentPassword">Current password <small>Required for email or password changes</small></label><input class="form-control" id="currentPassword" name="current_password" type="password" autocomplete="current-password">@error('current_password')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field"><label for="newPassword">New password</label><input class="form-control" id="newPassword" name="password" type="password" autocomplete="new-password" minlength="8">@error('password')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                        <div class="form-field"><label for="confirmNewPassword">Confirm new password</label><input class="form-control" id="confirmNewPassword" name="password_confirmation" type="password" autocomplete="new-password" minlength="8"></div>
                    </div>
                </section>

                <div class="editor-form-actions">
                    <div class="save-feedback"><i class="bi bi-shield-check"></i> Your account information is private.</div>
                    <div class="d-flex gap-2"><a class="btn btn-light" href="{{ route('customer.dashboard') }}">Cancel</a><button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save Account Details</button></div>
                </div>
            </form>
        </section>

        <aside class="account-aside">
            <div class="editor-info-card">
                <span class="info-card-icon"><i class="bi bi-envelope-check"></i></span>
                <h3>Email status</h3>
                <p>{{ $customer->hasVerifiedEmail() ? 'Your email address is verified.' : 'Your email address is not verified.' }}</p>
                <span class="customer-status {{ $customer->hasVerifiedEmail() ? 'customer-status-approved' : 'customer-status-pending' }}">{{ $customer->hasVerifiedEmail() ? 'Verified' : 'Not verified' }}</span>
            </div>
            <div class="editor-info-card pale-info">
                <span class="info-card-icon"><i class="bi bi-info-circle"></i></span>
                <h3>Separate profile information</h3>
                <p>Personal profile and company profile content are managed separately after the relevant profile request has been approved.</p>
            </div>
        </aside>
    </div>
</main>
@endsection
