<aside class="sidebar d-none d-lg-flex">
  <a href="{{ route('admin.dashboard') }}" class="admin-brand"><span class="brand-symbol">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span><span>{{ $siteSetting->site_name }}</span></a>
  <div class="sidebar-scroll" data-sidebar-scroll>@include('admin.include._sidebar_nav')</div>
  <div class="sidebar-bottom"><div class="sidebar-help-icon"><i class="bi bi-headset"></i></div><div><strong>Need some help?</strong><small>Contact the support team</small></div><i class="bi bi-arrow-up-right ms-auto"></i></div>
</aside>
<div class="mobile-sidebar offcanvas offcanvas-start" tabindex="-1" id="adminMobileNav" aria-labelledby="adminMobileNavLabel">
  <div class="offcanvas-header"><a href="{{ route('admin.dashboard') }}" class="admin-brand" id="adminMobileNavLabel"><span class="brand-symbol">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="30" height="30" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span><span>{{ $siteSetting->site_name }}</span></a><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close menu"></button></div>
  <div class="offcanvas-body p-0 sidebar-scroll" data-sidebar-scroll>@include('admin.include._sidebar_nav')</div>
</div>
