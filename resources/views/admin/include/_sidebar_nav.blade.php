<div class="sidebar-label">WORKSPACE</div>
<nav class="sidebar-nav" aria-label="Admin navigation">
  <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif><i class="bi bi-house-door"></i><span>Dashboard</span></a>
  @php($profileWorkflowMenu = request()->routeIs('admin.dashboard', 'admin.customer-accounts.*', 'admin.profile-requests.*', 'admin.profiles.*'))
  <details class="sidebar-group {{ $profileWorkflowMenu ? 'has-active' : '' }}" @if($profileWorkflowMenu) open @endif>
    <summary class="side-group-toggle"><i class="bi bi-layers"></i><span>Profiles & Content</span><i class="bi bi-chevron-down side-chevron"></i></summary>
    <div class="sidebar-submenu">
      @can('customers.manage')
        <a class="side-link {{ request()->routeIs('admin.customer-accounts.*') ? 'active' : '' }}" href="{{ route('admin.customer-accounts.index') }}"><i class="bi bi-person-check"></i><span>Customer Accounts</span></a>
      @endcan
      @can('profile-requests.manage')
        <a class="side-link {{ request()->routeIs('admin.profile-requests.*') ? 'active' : '' }}" href="{{ route('admin.profile-requests.index') }}"><i class="bi bi-clipboard-check"></i><span>Profile Access Requests</span></a>
      @endcan
      @can('profiles.manage')
        <a class="side-link {{ request()->routeIs('admin.profiles.submissions','admin.profiles.review') ? 'active' : '' }}" href="{{ route('admin.profiles.submissions') }}"><i class="bi bi-file-earmark-check"></i><span>Profile Submissions</span></a>
        <a class="side-link {{ request()->routeIs('admin.profiles.individual') ? 'active' : '' }}" href="{{ route('admin.profiles.individual') }}"><i class="bi bi-person-vcard"></i><span>Individual Profiles</span></a>
        <a class="side-link {{ request()->routeIs('admin.profiles.create') && request('type') === 'personal' ? 'active' : '' }}" href="{{ route('admin.profiles.create', ['type' => 'personal']) }}"><i class="bi bi-person-plus"></i><span>Add Personal Profile</span></a>
        <a class="side-link {{ request()->routeIs('admin.profiles.companies') ? 'active' : '' }}" href="{{ route('admin.profiles.companies') }}"><i class="bi bi-buildings"></i><span>Company Profiles</span></a>
        <a class="side-link {{ request()->routeIs('admin.profiles.create') && request('type') === 'company' ? 'active' : '' }}" href="{{ route('admin.profiles.create', ['type' => 'company']) }}"><i class="bi bi-building"></i><span>Add Company Profile</span></a>
        <a class="side-link {{ request()->routeIs('admin.profiles.revision-history','admin.profiles.history') ? 'active' : '' }}" href="{{ route('admin.profiles.revision-history') }}"><i class="bi bi-clock-history"></i><span>Revision History</span></a>
        <a class="side-link {{ request()->routeIs('admin.profiles.verification-center') ? 'active' : '' }}" href="{{ route('admin.profiles.verification-center') }}"><i class="bi bi-patch-check"></i><span>Verification Center</span></a>
        <a class="side-link {{ request()->routeIs('admin.profiles.index','admin.profiles.create','admin.profiles.edit') ? 'active' : '' }}" href="{{ route('admin.profiles.index') }}"><i class="bi bi-collection"></i><span>All Profiles</span></a>
      @endcan
    </div>
  </details>
  @php($siteMenu = request()->routeIs('admin.settings.*','admin.homepage-manager.*','admin.global-notice.*','admin.seo.*','admin.blog.*','admin.extra-pages.*','admin.contact-messages.*','admin.newsletter-subscribers.*'))
  <details class="sidebar-group {{ $siteMenu ? 'has-active' : '' }}" @if($siteMenu) open @endif>
    <summary class="side-group-toggle"><i class="bi bi-globe"></i><span>Website</span><i class="bi bi-chevron-down side-chevron"></i></summary>
    <div class="sidebar-submenu">
      @can('settings.manage')<a class="side-link {{ request()->routeIs('admin.homepage-manager.*') ? 'active' : '' }}" href="{{ route('admin.homepage-manager.index') }}" @if(request()->routeIs('admin.homepage-manager.*')) aria-current="page" @endif><i class="bi bi-house-gear"></i><span>Homepage Manager</span></a>@endcan
      @can('seo.manage')<a class="side-link {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}" href="{{ route('admin.seo.index') }}" @if(request()->routeIs('admin.seo.*')) aria-current="page" @endif><i class="bi bi-search"></i><span>SEO Manager</span></a>@endcan
      @can('blog.manage')<a class="side-link {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}" href="{{ route('admin.blog.index') }}" @if(request()->routeIs('admin.blog.*')) aria-current="page" @endif><i class="bi bi-journal-richtext"></i><span>Blog</span></a>@endcan
      @can('contact-messages.manage')<a class="side-link {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}" href="{{ route('admin.contact-messages.index') }}" @if(request()->routeIs('admin.contact-messages.*')) aria-current="page" @endif><i class="bi bi-inbox"></i><span>Contact Inbox</span></a>@endcan
      @can('settings.manage')<a class="side-link {{ request()->routeIs('admin.newsletter-subscribers.*') ? 'active' : '' }}" href="{{ route('admin.newsletter-subscribers.index') }}" @if(request()->routeIs('admin.newsletter-subscribers.*')) aria-current="page" @endif><i class="bi bi-envelope-paper"></i><span>Newsletter Subscribers</span></a>@endcan
      @can('extra-pages.manage')
        <a class="side-link {{ request()->routeIs('admin.extra-pages.*') && request('tab', 'about-us') === 'about-us' ? 'active' : '' }}" href="{{ route('admin.extra-pages.index', ['tab' => 'about-us']) }}"><i class="bi bi-info-circle"></i><span>About Us</span></a>
        <a class="side-link {{ request()->routeIs('admin.extra-pages.*') && request('tab') === 'contact-us' ? 'active' : '' }}" href="{{ route('admin.extra-pages.index', ['tab' => 'contact-us']) }}"><i class="bi bi-envelope"></i><span>Contact Us</span></a>
        <a class="side-link {{ request()->routeIs('admin.extra-pages.*') && in_array(request('tab'), ['terms-and-conditions', 'privacy-policy']) ? 'active' : '' }}" href="{{ route('admin.extra-pages.index', ['tab' => 'terms-and-conditions']) }}"><i class="bi bi-file-earmark-check"></i><span>Terms & Privacy</span></a>
      @endcan
      @can('settings.manage')<a class="side-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}" @if(request()->routeIs('admin.settings.*')) aria-current="page" @endif><i class="bi bi-gear"></i><span>Site Settings</span></a>@endcan
      @can('settings.manage')<a class="side-link {{ request()->routeIs('admin.global-notice.*') ? 'active' : '' }}" href="{{ route('admin.global-notice.index') }}" @if(request()->routeIs('admin.global-notice.*')) aria-current="page" @endif><i class="bi bi-megaphone"></i><span>Global Notice</span></a>@endcan
    </div>
  </details>
  @php($operationMenu = request()->routeIs('admin.activity.*','admin.storage.*','admin.backups.*'))
  <details class="sidebar-group {{ $operationMenu ? 'has-active' : '' }}" @if($operationMenu) open @endif>
    <summary class="side-group-toggle"><i class="bi bi-tools"></i><span>Operations</span><i class="bi bi-chevron-down side-chevron"></i></summary>
    <div class="sidebar-submenu">
      @can('activity.view')<a class="side-link {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}" href="{{ route('admin.activity.index') }}" @if(request()->routeIs('admin.activity.*')) aria-current="page" @endif><i class="bi bi-journal-text"></i><span>Activity Logs</span></a>@endcan
      @can('storage.view')<a class="side-link {{ request()->routeIs('admin.storage.*') ? 'active' : '' }}" href="{{ route('admin.storage.index') }}" @if(request()->routeIs('admin.storage.*')) aria-current="page" @endif><i class="bi bi-device-hdd"></i><span>Storage & Cleanup</span></a>@endcan
      @can('backups.manage')<a class="side-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}" @if(request()->routeIs('admin.backups.*')) aria-current="page" @endif><i class="bi bi-cloud-arrow-up"></i><span>Backup & Maintenance</span></a>@endcan
    </div>
  </details>
  @php($peopleMenu = request()->routeIs('admin.users.*','admin.roles.*','admin.permissions.*'))
  <details class="sidebar-group {{ $peopleMenu ? 'has-active' : '' }}" @if($peopleMenu) open @endif>
    <summary class="side-group-toggle"><i class="bi bi-people"></i><span>People & Permissions</span><i class="bi bi-chevron-down side-chevron"></i></summary>
    <div class="sidebar-submenu">
      @can('users.manage')<a class="side-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif><i class="bi bi-people"></i><span>User Management</span></a>@endcan
      @can('roles.manage')<a class="side-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}" @if(request()->routeIs('admin.roles.*')) aria-current="page" @endif><i class="bi bi-shield-lock"></i><span>Roles & Permissions</span></a>@endcan
      @can('permissions.manage')<a class="side-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}" @if(request()->routeIs('admin.permissions.*')) aria-current="page" @endif><i class="bi bi-key-fill"></i><span>Permissions</span></a>@endcan
    </div>
  </details>
</nav>
