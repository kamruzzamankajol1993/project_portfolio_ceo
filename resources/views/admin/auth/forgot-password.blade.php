<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password | {{ $siteSetting->site_name }}</title>
  @if($siteSetting->publicUrl('favicon'))<link rel="icon" href="{{ $siteSetting->publicUrl('favicon') }}">@endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400..900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('public/admin/css/admin.css') }}" rel="stylesheet">
</head>
<body class="auth-body">
  <main class="auth-layout">
    <section class="auth-showcase">
      <a href="{{ route('site.home') }}" class="auth-brand"><span class="brand-symbol">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span><span>{{ $siteSetting->site_name }}</span></a>
      <div class="showcase-content">
        <span class="showcase-tag"><i class="bi bi-shield-check"></i> ACCOUNT RECOVERY</span>
        <h1>Account<br><span>recovery.</span></h1>
        <p>Reset your administrator password and get back to managing your editorial workspace.</p>
        <div class="showcase-stat-row"><div><strong>No email</strong><span>Database lookup</span></div><div><strong>Private</strong><span>Account recovery</span></div></div>
      </div>
      <div class="showcase-footer"><span><i class="bi bi-lock-fill"></i> Secure administrator access</span><span>© 2025 {{ $siteSetting->site_name }}</span></div>
      <div class="showcase-orb orb-one"></div><div class="showcase-orb orb-two"></div>
    </section>
    <section class="auth-form-side">
      <div class="auth-card">
        @if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="auth-mobile-brand"><span class="brand-symbol">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span><strong>{{ $siteSetting->site_name }}</strong></div>
        <div class="auth-heading-icon"><i class="bi bi-envelope-paper"></i></div>
        <p class="auth-eyebrow">ACCOUNT RECOVERY</p>
        <h2>Forgot password?</h2>
        <p class="auth-subtitle">No worries. Enter the email address associated with your administrator account and you can continue to a password change screen. No email is sent. <strong>Security warning:</strong> anyone who knows an eligible email could reset its password. Do not expose this flow on a public website.</p>
        <form action="{{ route('admin.forgot.submit') }}" method="post" class="auth-form">
          @csrf
          <div class="mb-4"><label for="email" class="form-label">Email address</label><div class="input-with-icon"><i class="bi bi-envelope"></i><input id="email" name="email" type="email" class="form-control" placeholder="admin@entrepreneurwiki.org" autocomplete="email" required></div></div>
          <button class="btn btn-primary auth-submit" type="submit">Check email and continue <i class="bi bi-arrow-right"></i></button>
        </form>
        <a class="back-to-site auth-back-link" href="{{ route('admin.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a>
      </div>
    </section>
  </main>
  @if(session('success'))<script>window.addEventListener('load',()=>alert(@json(session('success'))));</script>@endif
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
