@php
    $brandName = trim((string) ($siteSetting->site_name ?: 'CEOWiki'));
    $brandPrefix = \Illuminate\Support\Str::startsWith(strtolower($brandName), 'ceo') ? 'CEO' : null;
    $brandSuffix = $brandPrefix ? trim(substr($brandName, 3)) : null;
    $directoryType = request()->routeIs('site.profiles.index') ? request('type') : null;
    $isCompanyNav = request()->routeIs('site.companies.*', 'site.company-profile')
        || $directoryType === 'company'
        || (isset($profile) && $profile->profile_type === 'company');
    $isCeoNav = request()->routeIs('site.entrepreneurs.*', 'site.personal-profile', 'site.home')
        || $directoryType === 'personal'
        || (isset($profile) && $profile->profile_type === 'personal');
@endphp
<header class="site-header">
    <div class="container-xl h-100">
        <nav class="navbar h-100" aria-label="Main navigation">
            <a class="brand" href="{{ route('site.home') }}" aria-label="{{ $siteSetting->site_name }} home">
                <span class="brand-mark home-brand-mark">
                    @if($siteSetting->publicUrl('logo'))
                        <img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}">
                    @else
                        <img src="{{ asset('public/website/assets/images/brand-mark-reference.png') }}" alt="">
                    @endif
                </span>
                <span class="brand-copy">
                    <span class="brand-name">@if($brandPrefix)CEO<span>{{ $brandSuffix ?: 'Wiki' }}</span>@else{{ $brandName }}@endif</span>
                    <span class="brand-tag">{{ $siteSetting->tagline ?: 'The Encyclopedia of CEOs and Companies' }}</span>
                </span>
            </a>

            <div class="desktop-nav">
                <a class="{{ $isCeoNav ? 'active' : '' }}" href="{{ route('site.entrepreneurs.index') }}">CEO</a>
                <a class="{{ $isCompanyNav ? 'active' : '' }}" href="{{ route('site.companies.index') }}">Company</a>
                <a href="{{ route('site.about-us') }}">About</a>
            </div>

            <div class="header-actions">
                <form class="header-search site-search-box" role="search" aria-label="Site search" data-ajax-search data-search-url="{{ route('site.search') }}" data-results-id="header-search-results">
                    <button class="header-search-toggle" type="button" aria-label="Open search" aria-expanded="false"><i class="bi bi-search"></i></button>
                    <input class="form-control" type="search" name="q" autocomplete="off" placeholder="{{ $searchSettings['site_search_placeholder'] ?? 'Search CEO, company, or keyword...' }}" aria-label="Search CEO, company, or keyword">
                    <div id="header-search-results" class="search-results-dropdown" role="listbox" aria-label="Search results" hidden></div>
                </form>
                @if(auth('customer')->check())
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-blue btn-sm">My Dashboard</a>
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-gold btn-sm">Account</a>
                @else
                    <a href="{{ route('customer.login') }}" class="btn btn-outline-blue btn-sm">Login</a>
                    <a href="{{ route('customer.register') }}" class="btn btn-gold btn-sm">Request a Profile</a>
                @endif
            </div>

            <button class="mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#siteMenu" aria-controls="siteMenu" aria-label="Open navigation">
                <i class="bi bi-list"></i>
            </button>
        </nav>
    </div>
</header>
