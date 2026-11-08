<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#f7faff">
  @include('partials.seo')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400..900&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('public/website/style.css') }}" rel="stylesheet">
  @yield('css')
</head>

@if(Route::currentRouteName() === 'site.profile.show')
    <body class="profile-page company-page individual-page">
@else
<body>
    @endif
  @include('website.include.topheader')
  @include('website.include.header')
  @include('website.include.offcanvas')

  @yield('body')

  @include('website.include.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('public/website/search.js') }}"></script>
  <script>
    // Keep prototype-only forms inert while allowing forms explicitly connected to Laravel to submit.
    document.querySelectorAll('form:not([data-server-form]):not([data-ajax-search])').forEach(form => form.addEventListener('submit', event => event.preventDefault()));
    const closeButton = document.querySelector('.announcement-close');
    closeButton?.addEventListener('click', () => document.querySelector('.announcement-wrap')?.classList.add('d-none'));

    const mobileNavElement = document.getElementById('mobileNav');
    document.querySelectorAll('#mobileNav a').forEach(link => {
      const destination = new URL(link.href, window.location.href);
      if (destination.origin === window.location.origin && destination.pathname === window.location.pathname && destination.hash) {
        link.addEventListener('click', () => {
          if (window.bootstrap && mobileNavElement) {
            window.bootstrap.Offcanvas.getOrCreateInstance(mobileNavElement).hide();
          }
        });
      }
    });

    @if(session('status') || session('success'))
      if (window.Swal) {
        Swal.fire({icon: 'success', title: 'Success', text: @json(session('status') ?: session('success')), confirmButtonColor: '#0865f5'});
      } else {
        window.alert(@json(session('status') ?: session('success')));
      }
    @elseif($errors->any())
      if (window.Swal) {
        Swal.fire({icon: 'error', title: 'Please check the form', text: @json($errors->first()), confirmButtonColor: '#0865f5'});
      } else {
        window.alert(@json($errors->first()));
      }
    @endif
  </script>
  @yield('scripts')
  {!! $siteSetting->body_scripts !!}
</body>
</html>
