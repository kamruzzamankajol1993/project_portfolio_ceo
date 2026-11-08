@php
    $brandName = trim((string) ($siteSetting->site_name ?: 'CEOWiki'));
    $brandPrefix = \Illuminate\Support\Str::startsWith(strtolower($brandName), 'ceo') ? 'CEO' : null;
    $brandSuffix = $brandPrefix ? trim(substr($brandName, 3)) : null;
@endphp
<div class="offcanvas offcanvas-end" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel">
    <div class="offcanvas-header">
        <a class="brand" href="{{ route('site.home') }}" id="siteMenuLabel">
            <span class="brand-mark home-brand-mark"><img src="{{ $siteSetting->publicUrl('logo') ?: asset('public/website/assets/images/brand-mark-reference.png') }}" alt="{{ $siteSetting->site_name }}"></span>
            <span class="brand-copy"><span class="brand-name">@if($brandPrefix)CEO<span>{{ $brandSuffix ?: 'Wiki' }}</span>@else{{ $brandName }}@endif</span><span class="brand-tag">{{ $siteSetting->tagline ?: 'The Encyclopedia of CEOs and Companies' }}</span></span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-3 site-search-box mobile-search-box">
            <form role="search" data-ajax-search data-search-url="{{ route('site.search') }}" data-results-id="mobile-search-results">
                <input class="form-control" type="search" name="q" autocomplete="off" placeholder="{{ $searchSettings['site_search_placeholder'] ?? 'Search CEO, company, or keyword...' }}" aria-label="Search CEO, company, or keyword">
            </form>
            <div id="mobile-search-results" class="search-results-dropdown" role="listbox" aria-label="Search results" hidden></div>
        </div>
        <a class="mobile-nav-link {{ (request()->routeIs('site.entrepreneurs.*', 'site.personal-profile', 'site.home') || (request()->routeIs('site.profiles.index') && request('type') === 'personal') || (isset($profile) && $profile->profile_type === 'personal')) ? 'active' : '' }}" href="{{ route('site.entrepreneurs.index') }}" data-bs-dismiss="offcanvas"><i class="bi bi-person-badge"></i> CEOs</a>
        <a class="mobile-nav-link {{ (request()->routeIs('site.companies.*', 'site.company-profile') || (request()->routeIs('site.profiles.index') && request('type') === 'company') || (isset($profile) && $profile->profile_type === 'company')) ? 'active' : '' }}" href="{{ route('site.companies.index') }}" data-bs-dismiss="offcanvas"><i class="bi bi-buildings"></i> Companies</a>
        <a class="mobile-nav-link" href="{{ route('site.about-us') }}" data-bs-dismiss="offcanvas"><i class="bi bi-info-circle"></i> About</a>
        <a class="mobile-nav-link" href="{{ route('site.home') }}#updates" data-bs-dismiss="offcanvas"><i class="bi bi-clock-history"></i> Latest updates</a>
        <div class="offcanvas-actions">
            @if(auth('customer')->check())
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-blue">My Dashboard</a>
                <a href="{{ route('customer.profile') }}" class="btn btn-gold">Account Settings</a>
            @else
                <a href="{{ route('customer.login') }}" class="btn btn-outline-blue">Login</a>
                <a href="{{ route('customer.register') }}" class="btn btn-gold">Request a Profile</a>
            @endif
        </div>
    </div>
</div>
