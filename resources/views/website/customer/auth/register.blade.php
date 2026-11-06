@extends('website.master.master')

@section('title')
   Sign Up - {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="auth-stage auth-stage-register">
    <div class="auth-card auth-card-register">
      <aside class="auth-aside">
        <span class="auth-aside-kicker"><i class="bi bi-book-half"></i> ENTREPRENEURWIKI</span>
        <div class="auth-illustration" aria-hidden="true"><div class="illustration-orbit orbit-one"></div><div class="illustration-orbit orbit-two"></div><div class="illustration-book"><i class="bi bi-person-vcard"></i></div><span class="orbit-dot dot-one"></span><span class="orbit-dot dot-two"></span><span class="orbit-dot dot-three"></span></div>
        <h1>Make your story part of the record.</h1>
        <p>Create an account to share your professional journey or your company's story with a wider audience.</p>
        <div class="auth-steps"><div><span>01</span><p><strong>Create your account</strong><small>Enter your basic details.</small></p></div><div><span>02</span><p><strong>Verify your email</strong><small>Confirm that your account belongs to you.</small></p></div><div><span>03</span><p><strong>Request a profile</strong><small>Submit personal or company information for review.</small></p></div></div>
      </aside>
      <section class="auth-panel">
        <div class="auth-heading"><span class="auth-eyebrow">JOIN OUR COMMUNITY</span><h2>Create your account</h2><p>Start with your basic information. You can request a profile after email verification and account approval.</p></div>
        <form class="account-form" id="registerForm" action="{{ route('customer.register.submit') }}" method="post" data-server-form>
          @csrf
          <div class="form-field"><label for="registerName">Full name</label><div class="input-with-icon"><i class="bi bi-person"></i><input id="registerName" name="name" type="text" value="{{ old('name') }}" placeholder="Enter your full name" autocomplete="name" maxlength="180" required></div>@error('name')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
          <div class="form-grid-two">
            <div class="form-field"><label for="registerEmail">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input id="registerEmail" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" maxlength="191" required></div>@error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
            <div class="form-field"><label for="registerPhone">Phone number <span class="optional-label">Optional</span></label><div class="input-with-icon"><i class="bi bi-telephone"></i><input id="registerPhone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+880 1XXXXXXXXX" autocomplete="tel" maxlength="30"></div>@error('phone')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
          </div>
          <div class="form-grid-two">
            <div class="form-field"><label for="registerPassword">Password</label><div class="password-input"><i class="bi bi-lock"></i><input id="registerPassword" name="password" type="password" placeholder="Create a password" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-toggle-password="registerPassword" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button></div><small class="field-hint">Use at least 8 characters.</small>@error('password')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
            <div class="form-field"><label for="confirmPassword">Confirm password</label><div class="password-input"><i class="bi bi-lock-fill"></i><input id="confirmPassword" name="password_confirmation" type="password" placeholder="Repeat password" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-toggle-password="confirmPassword" aria-label="Show password" aria-pressed="false"><i class="bi bi-eye"></i></button></div></div>
          </div>
          <label class="check-label terms-check"><input type="checkbox" name="terms" value="1" @checked(old('terms')) required><span>I agree to the <a href="{{ route('site.terms') }}">Terms of Use</a> and <a href="{{ route('site.privacy') }}">Privacy Policy</a>.</span></label>
          @error('terms')<small class="field-hint text-danger">{{ $message }}</small>@enderror
          <button class="btn btn-primary auth-submit" type="submit">Create Account <i class="bi bi-arrow-right"></i></button>
          @if($errors->any())<p class="form-feedback text-danger" role="alert">Please review the highlighted fields and try again.</p>@endif
        </form>
        <div class="auth-bottom">Already have an account? <a href="{{ route('customer.login') }}">Sign in</a></div>
        <div class="auth-trust"><i class="bi bi-shield-check"></i> Your account and profile requests are reviewed before access and publication.</div>
      </section>
    </div>
  </main>
@endsection

@section('scripts')
<script src="{{ asset('/') }}public/website/account-pages.js"></script>
@endsection
