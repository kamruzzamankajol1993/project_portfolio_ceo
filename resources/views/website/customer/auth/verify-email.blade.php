@extends('website.master.master')
@section('title', 'Verify email | ' . $siteSetting->site_name)
@section('body')
<main class="auth-shell">
    <section class="auth-art-panel"><div class="auth-art-content"><span class="auth-eyebrow">EMAIL VERIFICATION</span><h1>One quick step<br><em>to verify your account.</em></h1><p>Confirm your email address so our team can review your customer account.</p><div class="auth-art-points"><span><i class="bi bi-envelope-check"></i> Verified account email</span><span><i class="bi bi-person-check"></i> Administrator approval</span></div></div><div class="auth-art-orbit"><i class="bi bi-envelope-check"></i></div></section>
    <section class="auth-form-panel"><div class="auth-form-card"><a class="text-link mb-3 d-inline-block" href="{{ route('customer.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a><span class="auth-eyebrow">EMAIL VERIFICATION</span><h2>Check your inbox</h2><p class="auth-subtitle">We will send a verification link to your registered email address.</p>
        @if(session('status'))<p class="form-feedback" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<p class="form-feedback text-danger" role="alert">{{ $errors->first() }}</p>@endif
        <form class="account-form" action="{{ route('customer.verification.resend') }}" method="post" data-server-form>@csrf<div class="form-field"><label for="verificationEmail">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input class="form-control" id="verificationEmail" name="email" type="email" value="{{ old('email',$email) }}" placeholder="you@example.com" autocomplete="email" required></div>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div><button class="btn btn-primary auth-submit" type="submit">Resend Verification Link <i class="bi bi-arrow-right"></i></button></form>
        <div class="auth-bottom">Already verified? <a href="{{ route('customer.login') }}">Sign in</a></div><div class="auth-trust"><i class="bi bi-info-circle"></i> After email verification, your account will wait for administrator approval.</div>
    </div></section>
</main>
@endsection
