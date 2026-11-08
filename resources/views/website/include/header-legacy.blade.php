 <header class="site-header">
    <nav class="navbar page-shell container-fluid site-navbar" aria-label="Main navigation">
      <a class="navbar-brand brand-lockup" href="{{ route('site.home') }}#top" aria-label="{{ $siteSetting->site_name }} home">
        <span class="brand-mark">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half" aria-hidden="true"></i>@endif</span>
        <span class="brand-copy"><strong>{{ $siteSetting->site_name }}</strong><small>{{ $siteSetting->tagline ?: 'The Encyclopedia of Entrepreneurs' }}</small></span>
      </a>
      <ul class="navbar-nav nav-links desktop-nav-links d-none d-lg-flex">
        <li class="nav-item"><a class="nav-link active" href="{{ route('site.home') }}#top" aria-current="page">Home</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('site.entrepreneurs.index') }}">Entrepreneurs</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('site.companies.index') }}">Companies</a></li>
<li class="nav-item"><a class="nav-link" href="{{ route('site.about-us') }}">About</a></li>
      </ul>
      <div class="desktop-nav-actions d-none d-lg-flex">
        <div class="site-search-box header-search-box">
          <form class="nav-search d-flex" role="search" aria-label="Site search" data-ajax-search data-search-url="{{ route('site.search') }}" data-results-id="header-search-results">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" autocomplete="off" aria-label="Search entrepreneurs, companies, or industries" placeholder="{{ $searchSettings['site_search_placeholder'] ?? 'Search entrepreneurs, companies, or industries...' }}">
            <button type="submit" aria-label="Search"><i class="bi bi-search" aria-hidden="true"></i></button>
          </form>
          <div id="header-search-results" class="search-results-dropdown" role="listbox" aria-label="Search results" hidden></div>
        </div>
        @if(auth('customer')->check())
          <a class="sign-in-btn" href="{{ route('customer.dashboard') }}">My Dashboard</a>
          <a href="{{ route('customer.profile') }}" class="btn btn-primary create-profile-btn">Account Settings</a>
        @else
          <a class="sign-in-btn" href="{{ route('customer.login') }}">Sign in</a>
          <a href="{{ route('customer.register') }}" class="btn btn-primary create-profile-btn">Create Profile</a>
        @endif
      </div>
      <button class="mobile-menu-toggle d-inline-flex d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open navigation menu">
        <span>Menu</span><i class="bi bi-list" aria-hidden="true"></i>
      </button>
    </nav>
  </header>
