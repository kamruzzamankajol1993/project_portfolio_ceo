<div class="offcanvas offcanvas-end mobile-offcanvas" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavTitle">
    <div class="offcanvas-header">
      <a class="brand-lockup" href="{{ route('site.home') }}#top" id="mobileNavTitle" aria-label="{{ $siteSetting->site_name }} home">
        <span class="brand-mark">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half" aria-hidden="true"></i>@endif</span>
        <span class="brand-copy"><strong>{{ $siteSetting->site_name }}</strong><small>{{ $siteSetting->tagline ?: 'The Encyclopedia of Entrepreneurs' }}</small></span>
      </a>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close navigation menu"></button>
    </div>
    <div class="offcanvas-body">
      <p class="offcanvas-kicker">EXPLORE {{ strtoupper($siteSetting->site_name) }}</p>
      <ul class="navbar-nav mobile-nav-links">
        <li class="nav-item"><a class="nav-link active" href="{{ route('site.home') }}#top" aria-current="page"><i class="bi bi-house-door" aria-hidden="true"></i><span>Home</span><i class="bi bi-chevron-right nav-chevron" aria-hidden="true"></i></a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('site.entrepreneurs.index') }}"><i class="bi bi-people" aria-hidden="true"></i><span>Entrepreneurs</span><i class="bi bi-chevron-right nav-chevron" aria-hidden="true"></i></a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('site.companies.index') }}"><i class="bi bi-buildings" aria-hidden="true"></i><span>Companies</span><i class="bi bi-chevron-right nav-chevron" aria-hidden="true"></i></a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('site.about-us') }}"><i class="bi bi-info-circle" aria-hidden="true"></i><span>About</span><i class="bi bi-chevron-right nav-chevron" aria-hidden="true"></i></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('site.home') }}#spotlights"><i class="bi bi-journal-richtext" aria-hidden="true"></i><span>Entrepreneur Spotlights</span><i class="bi bi-chevron-right nav-chevron" aria-hidden="true"></i></a></li>
      </ul>
      <div class="site-search-box mobile-search-box">
        <form class="nav-search mobile-nav-search d-flex" role="search" aria-label="Mobile site search" data-ajax-search data-search-url="{{ route('site.search') }}" data-results-id="mobile-search-results">
          <i class="bi bi-search" aria-hidden="true"></i>
          <input type="search" name="q" autocomplete="off" aria-label="Search entrepreneurs, companies, or industries" placeholder="{{ $searchSettings['site_search_placeholder'] ?? 'Search entrepreneurs, companies, or industries...' }}">
          <button type="submit" aria-label="Search"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>
        <div id="mobile-search-results" class="search-results-dropdown" role="listbox" aria-label="Search results" hidden></div>
      </div>
      <div class="mobile-offcanvas-actions">
        @if(auth('customer')->check())
          <a class="btn btn-outline-primary mobile-sign-in" href="{{ route('customer.dashboard') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i> My Dashboard</a>
          <a href="{{ route('customer.profile') }}" class="btn btn-primary"><i class="bi bi-person-gear" aria-hidden="true"></i> Account Settings</a>
        @else
          <a class="btn btn-outline-primary mobile-sign-in" href="{{ route('customer.login') }}"><i class="bi bi-person" aria-hidden="true"></i> Sign in</a>
          <a href="{{ route('customer.register') }}" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Create Your Profile</a>
        @endif
        <a href="{{ route('site.entrepreneurs.index') }}" class="btn btn-outline-primary">Explore Entrepreneurs <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
      </div>
      <div class="offcanvas-trust-note"><span><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><strong>Discover with confidence</strong><small>Independent profiles, real stories and verified information.</small></div></div>
    </div>
  </div>
