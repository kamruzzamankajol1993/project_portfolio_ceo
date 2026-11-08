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
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    @php
        $isCustomerPage = request()->routeIs('customer.*');
        $usesReferenceDesign = request()->routeIs('site.home', 'site.profile.show', 'site.company-profile', 'site.personal-profile') || $isCustomerPage;
        $isLegacyPublicPage = !$usesReferenceDesign;
        $bodyClasses = collect(preg_split('/\\s+/', trim($__env->yieldContent('body_class')) ?: ''))
            ->filter()
            ->when($isCustomerPage, fn ($classes) => $classes->push('account-page'))
            ->when($isLegacyPublicPage, fn ($classes) => $classes->push('legacy-public-page'))
            ->unique()
            ->implode(' ');
    @endphp

    @if($usesReferenceDesign)
        {{-- Homepage and profile-detail pages use the original reference design system. --}}
        <link href="{{ asset('public/website/assets/css/style.css') }}?v={{ filemtime(public_path('website/assets/css/style.css')) }}" rel="stylesheet">
        <link href="{{ asset('public/website/assets/css/search-results.css') }}?v={{ filemtime(public_path('website/assets/css/search-results.css')) }}" rel="stylesheet">
        @if(request()->routeIs('site.home'))
            <link href="{{ asset('public/website/assets/css/homepage-hero-fix.css') }}?v={{ filemtime(public_path('website/assets/css/homepage-hero-fix.css')) }}" rel="stylesheet">
        @endif
        @if($isCustomerPage)
            <link href="{{ asset('public/website/assets/css/account-pages.css') }}?v={{ filemtime(public_path('website/assets/css/account-pages.css')) }}" rel="stylesheet">
            <link href="{{ asset('public/website/assets/css/account-pages-overrides.css') }}?v={{ filemtime(public_path('website/assets/css/account-pages-overrides.css')) }}" rel="stylesheet">
        @endif
    @else
        {{-- Keep directory / information-page content on its own CSS; do not let style.css reset it. --}}
        <link href="{{ asset('public/website/legacy-style.css') }}?v={{ filemtime(public_path('website/legacy-style.css')) }}" rel="stylesheet">
        <link href="{{ asset('public/website/profile-pages.css') }}?v={{ filemtime(public_path('website/profile-pages.css')) }}" rel="stylesheet">
        <link href="{{ asset('public/website/assets/css/search-results.css') }}?v={{ filemtime(public_path('website/assets/css/search-results.css')) }}" rel="stylesheet">
    @endif

    @yield('css')

    @if($isLegacyPublicPage || $isCustomerPage)
        {{-- Same Homepage header/footer on legacy public and customer pages; content layouts stay page-specific. --}}
        <link href="{{ asset('public/website/assets/css/legacy-shared-chrome.css') }}?v={{ filemtime(public_path('website/assets/css/legacy-shared-chrome.css')) }}" rel="stylesheet">
    @endif
    <link href="{{ asset('public/website/assets/css/montserrat-global.css') }}?v={{ filemtime(public_path('website/assets/css/montserrat-global.css')) }}" rel="stylesheet">
</head>
<body class="{{ $bodyClasses }}">
    {{-- The same Homepage header, mobile menu and footer are shared by all public pages. --}}
    @include('website.include.header')
    @include('website.include.offcanvas')

    @yield('body')

    @include('website.include.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('public/website/assets/js/app.js') }}?v={{ filemtime(public_path('website/assets/js/app.js')) }}"></script>
    @if($isLegacyPublicPage)
        <script src="{{ asset('public/website/homepage-profile-slider.js') }}"></script>
    @endif
    <script src="{{ asset('public/website/search.js') }}?v={{ filemtime(public_path('website/search.js')) }}"></script>
    @if($isCustomerPage)
        <script src="{{ asset('public/website/assets/js/account-pages.js') }}?v={{ filemtime(public_path('website/assets/js/account-pages.js')) }}"></script>
    @endif
    @yield('scripts')
    <script>
        document.querySelector('.announcement-close')?.addEventListener('click', () => document.querySelector('.announcement-wrap')?.classList.add('d-none'));
        document.querySelectorAll('.offcanvas a[href*="#"]').forEach(link => {
            link.addEventListener('click', () => {
                const target = link.closest('.offcanvas');
                if (target && window.bootstrap) window.bootstrap.Offcanvas.getOrCreateInstance(target).hide();
            });
        });
        @if($isLegacyPublicPage && (session('status') || session('success')))
            if (window.Swal) Swal.fire({icon:'success',title:'Success',text:@json(session('status') ?: session('success')),confirmButtonColor:'#0865f5'});
            else window.alert(@json(session('status') ?: session('success')));
        @elseif($isLegacyPublicPage && $errors->any())
            if (window.Swal) Swal.fire({icon:'error',title:'Please check the form',text:@json($errors->first()),confirmButtonColor:'#0865f5'});
            else window.alert(@json($errors->first()));
        @endif
    </script>
    {!! $siteSetting->body_scripts !!}
</body>
</html>
