@extends('website.master.master')
@section('title', 'Reset password | ' . $siteSetting->site_name)
@section('body')
<main class="auth-shell">
    <section class="auth-art-panel"><div class="auth-art-content"><span class="auth-eyebrow">SECURE ACCOUNT RECOVERY</span><h1>Choose a new<br><em>password.</em></h1><p>Create a secure password to protect your {{ $siteSetting->site_name }} account.</p><div class="auth-art-points"><span><i class="bi bi-shield-check"></i> Secure reset process</span><span><i class="bi bi-lock"></i> Private account access</span></div></div><div class="auth-art-orbit"><i class="bi bi-shield-lock"></i></div></section>
    <section class="auth-form-panel"><div class="auth-form-card"><a class="text-link mb-3 d-inline-block" href="{{ route('customer.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a><span class="auth-eyebrow">ACCOUNT RECOVERY</span><h2>Reset your password</h2><p class="auth-subtitle">Enter your new password below.</p>
        @if($errors->any())<p class="form-feedback text-danger" role="alert">{{ $errors->first() }}</p>@endif
        <form class="account-form" action="{{ route('customer.password.update') }}" method="post" data-server-form>@csrf<input type="hidden" name="token" value="{{ $token }}"><input type="hidden" name="email" value="{{ $email }}">
            <div class="form-field"><label for="resetPassword">New password</label><div class="password-input"><i class="bi bi-lock"></i><input class="form-control" id="resetPassword" name="password" type="password" placeholder="Create a new password" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-toggle-password="resetPassword" aria-label="Show password"><i class="bi bi-eye"></i></button></div>@error('password')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
            <div class="form-field"><label for="resetPasswordConfirmation">Confirm new password</label><div class="password-input"><i class="bi bi-lock-fill"></i><input class="form-control" id="resetPasswordConfirmation" name="password_confirmation" type="password" placeholder="Repeat your new password" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-toggle-password="resetPasswordConfirmation" aria-label="Show password"><i class="bi bi-eye"></i></button></div></div>
            <button class="btn btn-primary auth-submit" type="submit">Update Password <i class="bi bi-arrow-right"></i></button>
        </form><div class="auth-trust"><i class="bi bi-shield-check"></i> After resetting, sign in with your new credentials.</div>
    </div></section>
</main>
@endsection
