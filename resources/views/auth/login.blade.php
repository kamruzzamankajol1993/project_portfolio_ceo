<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | {{ $siteSetting->site_name }}</title>
  @if($siteSetting->publicUrl('favicon'))<link rel="icon" href="{{ $siteSetting->publicUrl('favicon') }}">@endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('public/admin/css/admin.css') }}" rel="stylesheet">
</head>
<body class="auth-body">
  <main class="auth-layout">
    <section class="auth-showcase">
      <a href="{{ route('site.home') }}" class="auth-brand"><span class="brand-symbol">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span><span>{{ $siteSetting->site_name }}</span></a>
      <div class="showcase-content">
        <span class="showcase-tag"><i class="bi bi-shield-check"></i> ADMINISTRATION PORTAL</span>
        <h1>Good to see<br>you <span>again.</span></h1>
        <p>Manage entrepreneur profiles, review submissions, and keep the {{ $siteSetting->site_name }} community growing.</p>
        <div class="showcase-stat-row">
          <div><strong>1,245+</strong><span>Profiles managed</span></div>
          <div><strong>99.9%</strong><span>Platform availability</span></div>
        </div>
      </div>
      <div class="showcase-footer"><span><i class="bi bi-lock-fill"></i> Secure administrator access</span><span>© 2025 {{ $siteSetting->site_name }}</span></div>
      <div class="showcase-orb orb-one"></div><div class="showcase-orb orb-two"></div>
    </section>
    <section class="auth-form-side">
      <div class="auth-card">
        @if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="auth-mobile-brand"><span class="brand-symbol">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span><strong>{{ $siteSetting->site_name }}</strong></div>
        <div class="auth-heading-icon"><i class="bi bi-person-lock"></i></div>
        <p class="auth-eyebrow">WELCOME BACK</p>
        <h2>Admin sign in</h2>
        <p class="auth-subtitle">Enter your credentials to access your dashboard.</p>
        <form action="{{ route('admin.login.submit') }}" method="post" class="auth-form">
          @csrf
          <div class="mb-3"><label for="email" class="form-label">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input id="email" name="email" value="{{ old('email') }}" type="email" class="form-control" placeholder="admin@entrepreneurwiki.org" autocomplete="username" required></div></div>
          <div class="mb-2"><div class="label-row"><label for="password" class="form-label">Password</label><a href="{{ route('admin.forgot') }}">Forgot password?</a></div><div class="input-with-icon password-field"><i class="bi bi-lock"></i><input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required><button type="button" class="password-toggle" aria-label="Show password" data-password-toggle="password"><i class="bi bi-eye"></i></button></div></div>
          <div class="form-check auth-remember"><input class="form-check-input" type="checkbox" id="remember" name="remember" value="1"><label class="form-check-label" for="remember">Remember me for 30 days</label></div>
          <button class="btn btn-primary auth-submit" type="submit">Sign in to dashboard <i class="bi bi-arrow-right"></i></button>
        </form>
        <div class="auth-security-note"><i class="bi bi-shield-lock"></i><span>Your session is protected with secure authentication.</span></div>
        <a class="back-to-site" href="{{ route('site.home') }}"><i class="bi bi-arrow-left"></i> Back to website</a>
      </div>
    </section>
  </main>
  @if(session('success'))<script>window.addEventListener('load',()=>alert(@json(session('success'))));</script>@endif
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('public/admin/js/admin.js') }}"></script>
</body>
</html>
