@extends('website.master.master')
@section('title', 'Forgot password | ' . $siteSetting->site_name)
@section('body')
<main class="auth-shell">
    <section class="auth-art-panel"><div class="auth-art-content"><span class="auth-eyebrow">ACCOUNT RECOVERY</span><h1>A fresh start<br><em>is one step away.</em></h1><p>Use the email address connected to your account to begin resetting your password.</p><div class="auth-art-points"><span><i class="bi bi-lock"></i> Account protection</span><span><i class="bi bi-envelope-check"></i> Secure reset link</span></div></div><div class="auth-art-orbit"><i class="bi bi-key"></i></div></section>
    <section class="auth-form-panel"><div class="auth-form-card"><a class="text-link mb-3 d-inline-block" href="{{ route('customer.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a><span class="auth-eyebrow">ACCOUNT RECOVERY</span><h2>Forgot your password?</h2><p class="auth-subtitle">Enter your registered email address to receive a secure reset link.</p>
        @if(session('status'))<p class="form-feedback" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<p class="form-feedback text-danger" role="alert">{{ $errors->first() }}</p>@endif
        <form class="account-form" action="{{ route('customer.password.email') }}" method="post" data-server-form>@csrf<div class="form-field"><label for="forgotEmail">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input class="form-control" id="forgotEmail" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required></div>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div><button class="btn btn-primary auth-submit" type="submit">Send Reset Link <i class="bi bi-arrow-right"></i></button></form>
        <div class="auth-bottom">Remembered your password? <a href="{{ route('customer.login') }}">Sign in</a></div><div class="auth-trust"><i class="bi bi-shield-check"></i> The reset link is sent to your registered email address.</div>
    </div></section>
</main>
@endsection
