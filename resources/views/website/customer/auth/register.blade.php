@extends('website.master.master')

@section('title', 'Create account | ' . $siteSetting->site_name)

@section('body')
<main class="auth-shell register-shell">
    <section class="auth-art-panel">
        <div class="auth-art-content">
            <span class="auth-eyebrow">JOIN THE GLOBAL DIRECTORY</span>
            <h1>Make your story<br><em>part of the record.</em></h1>
            <p>Create an account to submit, manage and update your personal or company profile on {{ $siteSetting->site_name }}.</p>
            <div class="auth-art-points">
                <span><i class="bi bi-person-vcard"></i> Personal &amp; company profiles</span>
                <span><i class="bi bi-check2-circle"></i> Transparent review process</span>
                <span><i class="bi bi-bar-chart"></i> Track every submission</span>
            </div>
        </div>
        <div class="auth-art-orbit"><i class="bi bi-stars"></i></div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form-card auth-form-card-wide">
            <span class="auth-eyebrow">GET STARTED</span>
            <h2>Create your account</h2>
            <p class="auth-subtitle">A few details to set up your {{ $siteSetting->site_name }} workspace.</p>
            @if($errors->any())<p class="form-feedback text-danger" role="alert">{{ $errors->first() }}</p>@endif
            <form class="account-form" id="registerForm" action="{{ route('customer.register.submit') }}" method="post" data-server-form>
                @csrf
                <div class="form-field">
                    <label for="registerName">Full name</label>
                    <div class="input-with-icon"><i class="bi bi-person"></i><input id="registerName" name="name" type="text" value="{{ old('name') }}" placeholder="Your full name" autocomplete="name" maxlength="180" required></div>
                    @error('name')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-grid-two">
                    <div class="form-field">
                        <label for="registerEmail">Email address</label>
                        <div class="input-with-icon"><i class="bi bi-envelope"></i><input id="registerEmail" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" maxlength="191" required></div>
                        @error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-field">
                        <label for="registerPhone">Phone number <small>Optional</small></label>
                        <div class="input-with-icon"><i class="bi bi-telephone"></i><input id="registerPhone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+880 1XXXXXXXXX" autocomplete="tel" maxlength="30"></div>
                        @error('phone')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="form-grid-two">
                    <div class="form-field">
                        <label for="registerPassword">Password</label>
                        <div class="password-input"><i class="bi bi-lock"></i><input id="registerPassword" name="password" type="password" placeholder="Create a password" autocomplete="new-password" minlength="8" required><button class="password-toggle" type="button" data-toggle-password="registerPassword" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
                        <small class="field-hint">Use at least 8 characters.</small>
                        @error('password')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-field">
                        <label for="confirmPassword">Confirm password</label>
                        <div class="password-input"><i class="bi bi-lock-fill"></i><input id="confirmPassword" name="password_confirmation" type="password" placeholder="Repeat password" autocomplete="new-password" minlength="8" required><button class="password-toggle" type="button" data-toggle-password="confirmPassword" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
                    </div>
                </div>
                <label class="check-label terms-check"><input type="checkbox" name="terms" value="1" @checked(old('terms')) required><span>I agree to the <a href="{{ route('site.terms') }}">Terms of Use</a> and <a href="{{ route('site.privacy') }}">Privacy Policy</a>.</span></label>
                @error('terms')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                <button class="btn btn-primary auth-submit" type="submit">Create Account <i class="bi bi-arrow-right"></i></button>
            </form>
            <div class="auth-bottom">Already have an account? <a href="{{ route('customer.login') }}">Sign in</a></div>
            <div class="auth-trust"><i class="bi bi-shield-check"></i> Your account and profile requests are reviewed before publication.</div>
        </div>
    </section>
</main>
@endsection
