<header class="topbar">
        <div class="topbar-left">
          <button class="icon-button mobile-nav-trigger d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileNav" aria-controls="adminMobileNav" aria-label="Open admin menu"><i class="bi bi-list"></i></button>
          <button class="icon-button top-search-trigger d-none d-sm-inline-flex" type="button" aria-label="Focus search"><i class="bi bi-search"></i></button>
          <form class="global-search d-none d-sm-flex" role="search" method="get" action="{{ route('admin.users.index') }}">
            <input type="search" name="search" value="{{ request()->routeIs('admin.users.*') ? request('search') : '' }}" placeholder="Search users..." aria-label="Global user search">
            <kbd>⌘ K</kbd>
          </form>
        </div>
        <div class="topbar-actions">
          @can('backups.manage')
          <form method="post" action="{{ route('admin.backups.cache') }}" class="topbar-cache-form" data-confirm="Clear all application caches now?" data-confirm-button="Clear cache">
            @csrf
            <input type="hidden" name="scope" value="app">
            <button type="submit" class="clear-cache-btn" title="Clear application cache" aria-label="Clear application cache">
              <i class="bi bi-arrow-clockwise" aria-hidden="true"></i><span class="d-none d-sm-inline">Clear Cache</span>
            </button>
          </form>
          @endcan
          <a class="view-site-btn d-none d-md-inline-flex" href="{{ route('site.home') }}" target="_blank" rel="noopener noreferrer">View Website <i class="bi bi-box-arrow-up-right"></i></a>
          @can('customers.manage')
            @php($customerNotificationCursor = \App\Models\Customer::max('id') ?? 0)
            <button class="icon-button notification-button" type="button" aria-label="Customer registration notifications" title="New customer registrations" data-customer-notifications data-endpoint="{{ route('admin.notifications.customer-registrations') }}" data-after-id="{{ $customerNotificationCursor }}" data-view-url="{{ route('admin.customer-accounts.index') }}">
              <i class="bi bi-bell"></i><span class="notification-dot" data-notification-badge hidden>0</span>
            </button>
          @endcan
          <div class="topbar-divider"></div>
          <div class="dropdown">
            <button class="admin-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="admin-avatar">{{ strtoupper(mb_substr(auth()->user()->name,0,2)) }}</span>
              <span class="admin-user-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->getRoleNames()->first() ?? 'Administrator' }}</small></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end admin-dropdown">
              @can('settings.manage')<li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="bi bi-person-gear"></i> Site settings</a></li>@endcan
              <li><form action="{{ route('admin.logout') }}" method="post">@csrf<button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Sign out</button></form></li>
            </ul>
          </div>
        </div>
      </header>
