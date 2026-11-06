@extends('website.master.master')

@section('title')
   Sign In - {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="auth-stage">
    <div class="auth-card auth-card-login">
      <aside class="auth-aside">
        <span class="auth-aside-kicker"><i class="bi bi-book-half"></i> ENTREPRENEURWIKI</span>
        <div class="auth-illustration" aria-hidden="true"><div class="illustration-orbit orbit-one"></div><div class="illustration-orbit orbit-two"></div><div class="illustration-book"><i class="bi bi-journal-richtext"></i></div><span class="orbit-dot dot-one"></span><span class="orbit-dot dot-two"></span><span class="orbit-dot dot-three"></span></div>
        <h1>Welcome back.</h1>
        <p>Sign in to manage your information, update your profile and keep your {{ $siteSetting->site_name }} presence accurate.</p>
        <div class="auth-benefit"><i class="bi bi-shield-check"></i><span><strong>Your profile, your control</strong><small>Review and update your submitted information.</small></span></div>
        <div class="auth-benefit"><i class="bi bi-patch-check"></i><span><strong>Editorial review</strong><small>Submit updates for verification before publishing.</small></span></div>
      </aside>
      <section class="auth-panel">
        <div class="auth-heading"><span class="auth-eyebrow">MEMBER ACCESS</span><h2>Sign in to your account</h2><p>Enter your account details to continue.</p></div>
        @if(session('status'))<p class="form-feedback" role="status">{{ session('status') }}</p>@endif
        <form class="account-form" id="loginForm" action="{{ route('customer.login.submit') }}" method="post" data-server-form>
          @csrf
          <div class="form-field"><label for="loginEmail">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input id="loginEmail" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required></div>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
          <div class="form-field"><div class="field-label-row"><label for="loginPassword">Password</label><a href="{{ route('customer.forgot-password') }}">Forgot password?</a></div><div class="password-input"><i class="bi bi-lock"></i><input id="loginPassword" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required><button type="button" class="password-toggle" data-toggle-password="loginPassword" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button></div></div>
          <div class="form-options"><label class="check-label"><input type="checkbox" name="remember" value="1"><span>Remember me</span></label><span class="secure-note"><i class="bi bi-shield-lock"></i> Secure sign in</span></div>
          <button class="btn btn-primary auth-submit" type="submit">Sign In <i class="bi bi-arrow-right"></i></button>
        </form>
        <div class="auth-bottom">Don't have an account? <a href="{{ route('customer.register') }}">Create an account</a></div>
        <div class="auth-trust"><i class="bi bi-info-circle"></i> Dashboard access is enabled after email verification and administrator approval.</div>
      </section>
    </div>
  </main>
@endsection

@section('scripts')
<script src="{{ asset('/') }}public/website/account-pages.js"></script>
@endsection
