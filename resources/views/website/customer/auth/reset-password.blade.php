@extends('website.master.master')

@section('title')
   Reset Password - {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="auth-stage auth-stage-forgot">
    <div class="auth-card auth-card-forgot">
      <aside class="auth-aside forgot-aside">
        <span class="auth-aside-kicker"><i class="bi bi-book-half"></i> ENTREPRENEURWIKI</span>
        <div class="forgot-icon-art"><span><i class="bi bi-key-fill"></i></span><i class="bi bi-shield-check"></i></div>
        <h1>Choose a new password.</h1>
        <p>Create a secure password to protect your {{ $siteSetting->site_name }} account.</p>
        <div class="auth-benefit"><i class="bi bi-lock"></i><span><strong>Secure reset</strong><small>Your reset link is private and expires automatically.</small></span></div>
      </aside>
      <section class="auth-panel forgot-panel">
        <a class="back-to-login" href="{{ route('customer.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a>
        <div class="forgot-heading-icon"><i class="bi bi-shield-lock"></i></div>
        <div class="auth-heading"><span class="auth-eyebrow">ACCOUNT RECOVERY</span><h2>Reset your password</h2><p>Enter your new password below.</p></div>
        <form class="account-form" action="{{ route('customer.password.update') }}" method="post" data-server-form>
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">
          <input type="hidden" name="email" value="{{ $email }}">
          <div class="form-field"><label for="resetPassword">New password</label><div class="password-input"><i class="bi bi-lock"></i><input id="resetPassword" name="password" type="password" placeholder="Create a new password" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-toggle-password="resetPassword" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button></div>@error('password')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
          <div class="form-field"><label for="resetPasswordConfirmation">Confirm new password</label><div class="password-input"><i class="bi bi-lock-fill"></i><input id="resetPasswordConfirmation" name="password_confirmation" type="password" placeholder="Repeat your new password" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-toggle-password="resetPasswordConfirmation" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button></div></div>
          @error('email')<p class="form-feedback text-danger" role="alert">{{ $message }}</p>@enderror
          <button class="btn btn-primary auth-submit" type="submit">Update Password <i class="bi bi-arrow-right"></i></button>
        </form>
        <div class="auth-trust"><i class="bi bi-shield-check"></i> After resetting your password, sign in with your new credentials.</div>
      </section>
    </div>
  </main>
@endsection

@section('scripts')
<script src="{{ asset('/') }}public/website/account-pages.js"></script>
@endsection
