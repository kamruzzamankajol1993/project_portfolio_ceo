@extends('website.master.master')

@section('title')
   Forgot Password - {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="auth-stage auth-stage-forgot">
    <div class="auth-card auth-card-forgot">
      <aside class="auth-aside forgot-aside">
        <span class="auth-aside-kicker"><i class="bi bi-book-half"></i> ENTREPRENEURWIKI</span>
        <div class="forgot-icon-art"><span><i class="bi bi-key"></i></span><i class="bi bi-shield-check"></i></div>
        <h1>A fresh start is one step away.</h1>
        <p>Use the email address connected to your account to begin resetting your password.</p>
        <div class="auth-benefit"><i class="bi bi-lock"></i><span><strong>Account protection</strong><small>Only the account owner should reset the password.</small></span></div>
      </aside>
      <section class="auth-panel forgot-panel">
        <a class="back-to-login" href="{{ route('customer.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a>
        <div class="forgot-heading-icon"><i class="bi bi-envelope-lock"></i></div>
        <div class="auth-heading"><span class="auth-eyebrow">ACCOUNT RECOVERY</span><h2>Forgot your password?</h2><p>No worries. Enter your registered email address to receive a secure reset link.</p></div>
        @if(session('status'))<p class="form-feedback" role="status">{{ session('status') }}</p>@endif
        <form class="account-form" id="forgotForm" action="{{ route('customer.password.email') }}" method="post" data-server-form>
          @csrf
          <div class="form-field"><label for="forgotEmail">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input id="forgotEmail" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required></div>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
          <button class="btn btn-primary auth-submit" type="submit">Continue <i class="bi bi-arrow-right"></i></button>
        </form>
        <div class="auth-bottom">Remembered your password? <a href="{{ route('customer.login') }}">Sign in</a></div>
        <div class="auth-trust"><i class="bi bi-info-circle"></i> The password reset link is sent to your registered email address.</div>
      </section>
    </div>
  </main>
@endsection

@section('scripts')
<script src="{{ asset('/') }}public/website/account-pages.js"></script>
@endsection
