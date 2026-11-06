@extends('website.master.master')

@section('title')
   Verify Email - {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="auth-stage auth-stage-forgot">
    <div class="auth-card auth-card-forgot">
      <aside class="auth-aside forgot-aside">
        <span class="auth-aside-kicker"><i class="bi bi-book-half"></i> ENTREPRENEURWIKI</span>
        <div class="forgot-icon-art"><span><i class="bi bi-envelope-check"></i></span><i class="bi bi-shield-check"></i></div>
        <h1>One quick step to verify your account.</h1>
        <p>Confirm your email address so our team can review your customer account.</p>
        <div class="auth-benefit"><i class="bi bi-person-check"></i><span><strong>Administrator approval</strong><small>Dashboard access begins after your account is approved.</small></span></div>
      </aside>
      <section class="auth-panel forgot-panel">
        <a class="back-to-login" href="{{ route('customer.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a>
        <div class="forgot-heading-icon"><i class="bi bi-envelope-check"></i></div>
        <div class="auth-heading"><span class="auth-eyebrow">EMAIL VERIFICATION</span><h2>Check your inbox</h2><p>We will send a verification link to your registered email address.</p></div>
        @if(session('status'))<p class="form-feedback" role="status">{{ session('status') }}</p>@endif
        <form class="account-form" action="{{ route('customer.verification.resend') }}" method="post" data-server-form>
          @csrf
          <div class="form-field"><label for="verificationEmail">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input id="verificationEmail" name="email" type="email" value="{{ old('email', $email) }}" placeholder="you@example.com" autocomplete="email" required></div>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
          <button class="btn btn-primary auth-submit" type="submit">Resend Verification Link <i class="bi bi-arrow-right"></i></button>
        </form>
        <div class="auth-bottom">Already verified? <a href="{{ route('customer.login') }}">Sign in</a></div>
        <div class="auth-trust"><i class="bi bi-info-circle"></i> After email verification, your account will wait for administrator approval.</div>
      </section>
    </div>
  </main>
@endsection

@section('scripts')
<script src="{{ asset('/') }}public/website/account-pages.js"></script>
@endsection
