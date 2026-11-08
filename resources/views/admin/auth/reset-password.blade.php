<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset Password | {{ $siteSetting->site_name }}</title>
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
        <span class="showcase-tag"><i class="bi bi-shield-check"></i> PASSWORD SECURITY</span>
        <h1>Protect your<br><span>workspace.</span></h1>
        <p>Create a strong, unique password to keep your administrator account protected.</p>
        <div class="showcase-stat-row"><div><strong>Private</strong><span>Account access</span></div><div><strong>Secure</strong><span>Password reset</span></div></div>
      </div>
      <div class="showcase-footer"><span><i class="bi bi-lock-fill"></i> Secure administrator access</span><span>© 2025 {{ $siteSetting->site_name }}</span></div>
      <div class="showcase-orb orb-one"></div><div class="showcase-orb orb-two"></div>
    </section>
    <section class="auth-form-side">
      <div class="auth-card">
        @if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="auth-mobile-brand"><span class="brand-symbol">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span><strong>{{ $siteSetting->site_name }}</strong></div>
        <div class="auth-heading-icon"><i class="bi bi-key"></i></div>
        <p class="auth-eyebrow">NEW CREDENTIALS</p>
        <h2>Reset your password</h2>
        <p class="auth-subtitle">Choose a strong password you don’t use on another website.</p>
        <form action="{{ route('admin.reset.submit',$token) }}" method="post" class="auth-form">
          @csrf
          <div class="mb-3"><label for="new-password" class="form-label">New password</label><div class="input-with-icon password-field"><i class="bi bi-lock"></i><input id="new-password" name="password" type="password" class="form-control" placeholder="At least 10 characters, mixed case and numbers" minlength="10" autocomplete="new-password" required><button type="button" class="password-toggle" aria-label="Show password" data-password-toggle="new-password"><i class="bi bi-eye"></i></button></div></div>
          <div class="mb-3"><label for="confirm-password" class="form-label">Confirm new password</label><div class="input-with-icon password-field"><i class="bi bi-lock"></i><input id="confirm-password" name="password_confirmation" type="password" class="form-control" placeholder="Re-enter new password" minlength="10" autocomplete="new-password" required><button type="button" class="password-toggle" aria-label="Show password" data-password-toggle="confirm-password"><i class="bi bi-eye"></i></button></div><div class="invalid-feedback d-block d-none" id="password-match-error">Passwords do not match.</div></div>
          <button class="btn btn-primary auth-submit" type="submit">Save new password <i class="bi bi-arrow-right"></i></button>
        </form>
        <a class="back-to-site auth-back-link" href="{{ route('admin.login') }}"><i class="bi bi-arrow-left"></i> Back to sign in</a>
      </div>
    </section>
  </main>
  @if(session('success'))<script>window.addEventListener('load',()=>alert(@json(session('success'))));</script>@endif
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('public/admin/js/admin.js') }}"></script>
  <script>
    const resetForm = document.querySelector('.auth-form');
    resetForm.addEventListener('submit', function(event) {
      const first = document.getElementById('new-password');
      const second = document.getElementById('confirm-password');
      if (first.value !== second.value) {
        event.preventDefault();
        document.getElementById('password-match-error').classList.remove('d-none');
        second.classList.add('is-invalid');
      }
    });
  </script>
</body>
</html>
