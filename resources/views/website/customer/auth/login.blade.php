@extends('website.master.master')

@section('title', 'Sign in | ' . $siteSetting->site_name)

@section('body')
<main class="auth-shell">
    <section class="auth-art-panel">
        <div class="auth-art-content">
            <span class="auth-eyebrow">YOUR {{ strtoupper($siteSetting->site_name) }} WORKSPACE</span>
            <h1>Build your story.<br><em>Shape your legacy.</em></h1>
            <p>Manage your professional profile, share company milestones and keep your public information accurate.</p>
            <div class="auth-art-points">
                <span><i class="bi bi-shield-check"></i> Verified profile workflow</span>
                <span><i class="bi bi-globe2"></i> Global visibility</span>
                <span><i class="bi bi-journal-richtext"></i> Your story, your control</span>
            </div>
        </div>
        <div class="auth-art-orbit"><i class="bi bi-book-half"></i></div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form-card">
            <span class="auth-eyebrow">WELCOME BACK</span>
            <h2>Sign in to your account</h2>
            <p class="auth-subtitle">Access your dashboard and manage your profiles.</p>
            @if(session('status'))<p class="form-feedback" role="status">{{ session('status') }}</p>@endif
            @if($errors->any())<p class="form-feedback text-danger" role="alert">{{ $errors->first() }}</p>@endif
            <form class="account-form" id="loginForm" action="{{ route('customer.login.submit') }}" method="post" data-server-form>
                @csrf
                <div class="form-field">
                    <label for="loginEmail">Email address</label>
                    <div class="input-with-icon"><i class="bi bi-envelope"></i><input id="loginEmail" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required></div>
                    @error('email')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-field">
                    <label for="loginPassword">Password</label>
                    <div class="password-input"><i class="bi bi-lock"></i><input id="loginPassword" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required><button class="password-toggle" type="button" data-toggle-password="loginPassword" aria-label="Show password"><i class="bi bi-eye"></i></button></div>
                    @error('password')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="auth-form-options">
                    <label class="check-label"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Remember me</span></label>
                    <a class="text-link" href="{{ route('customer.forgot-password') }}">Forgot password?</a>
                </div>
                <button class="btn btn-primary auth-submit" type="submit">Sign In <i class="bi bi-arrow-right"></i></button>
            </form>
            <div class="auth-bottom">Don't have an account? <a href="{{ route('customer.register') }}">Create an account</a></div>
            <div class="auth-trust"><i class="bi bi-shield-lock"></i> Your account information stays private and secure.</div>
        </div>
    </section>
</main>
@endsection
