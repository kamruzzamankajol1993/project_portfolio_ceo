@extends('admin.master.master')
@section('title')
  Dashboard
@endsection
@section('css')
@endsection
@section('body')
 <section class="page-heading">
          <div>
            <div class="eyebrow">OVERVIEW</div>
            <h1>Dashboard</h1>
            <p>Welcome back, {{ auth()->user()->name }}. Here's an overview of your {{ $siteSetting->site_name }}.</p>
          </div>
          <div class="dropdown date-dropdown">
            <button class="date-filter dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-calendar3"></i><span>{{ $growthStartDate->format('d M Y') }} - {{ $growthEndDate->format('d M Y') }}</span></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => '7d']) }}">Last 7 days</a></li>
              <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => '30d']) }}">Last 30 days</a></li>
              <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => '6m']) }}">Last 6 months</a></li>
            </ul>
          </div>
        </section>


        <div class="dashboard-preview-notice" role="note"><i class="bi bi-info-circle-fill"></i><span><strong>Live dashboard</strong> — Profile, customer, review, media and system figures are loaded from the current database.</span></div>


        <div class="live-count-strip" aria-label="Live admin totals">
          <span class="live-count-heading"><i class="bi bi-database-check"></i> Live administration</span>
          <span><i class="bi bi-people-fill"></i> Users <b>{{ $userCount }}</b></span>
          <span><i class="bi bi-shield-check"></i> Roles <b>{{ $roleCount }}</b></span>
          <span><i class="bi bi-key-fill"></i> Permissions <b>{{ $permissionCount }}</b></span>
        </div>

        <section class="stats-grid" aria-label="Platform statistics">
          <article class="stat-card">
            <div class="stat-icon stat-blue"><i class="bi bi-person-fill"></i></div>
            <div class="stat-content"><div class="stat-title">Pending Accounts</div><div class="stat-number-row"><strong>{{ number_format($pendingAccountCount) }}</strong></div><small>Awaiting email verification or admin approval</small></div>
            <span class="stat-corner"><i class="bi bi-three-dots"></i></span>
          </article>
          <article class="stat-card">
            <div class="stat-icon stat-green"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div class="stat-content"><div class="stat-title">Pending Submissions</div><div class="stat-number-row"><strong>{{ number_format($pendingSubmissionCount) }}</strong></div><small>Profiles awaiting review</small></div>
            <span class="stat-corner"><i class="bi bi-three-dots"></i></span>
          </article>
          <article class="stat-card">
            <div class="stat-icon stat-purple"><i class="bi bi-person"></i></div>
            <div class="stat-content"><div class="stat-title">Published Entrepreneurs</div><div class="stat-number-row"><strong>{{ number_format($publishedPersonalCount) }}</strong></div><small>Total individual profiles</small></div>
            <span class="stat-corner"><i class="bi bi-three-dots"></i></span>
          </article>
          <article class="stat-card">
            <div class="stat-icon stat-orange"><i class="bi bi-buildings-fill"></i></div>
            <div class="stat-content"><div class="stat-title">Published Companies</div><div class="stat-number-row"><strong>{{ number_format($publishedCompanyCount) }}</strong></div><small>Total company profiles</small></div>
            <span class="stat-corner"><i class="bi bi-three-dots"></i></span>
          </article>
        </section>

        <section class="analytics-grid">
          <article class="panel growth-panel">
            <div class="panel-heading">
              <h2>Profile Growth</h2>
              <div class="chart-legend"><span><i class="legend-dot dot-blue"></i>Entrepreneurs</span><span><i class="legend-dot dot-green"></i>Companies</span></div>
              <div class="dropdown ms-auto">
                <button class="select-button dropdown-toggle" data-bs-toggle="dropdown">{{ $growthPeriod === '30d' ? 'Last 30 Days' : ($growthPeriod === '12m' ? 'Last 12 Months' : 'Last 6 Months') }}</button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => '30d']) }}">Last 30 Days</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => '6m']) }}">Last 6 Months</a></li>
                  <li><a class="dropdown-item" href="{{ route('admin.dashboard', ['period' => '12m']) }}">Last 12 Months</a></li>
                </ul>
              </div>
            </div>
            <div class="growth-chart" role="img" aria-label="Line chart showing entrepreneur and company profile growth for {{ implode(', ', $growthLabels) }}">
              <div class="chart-y-labels"><span>{{ number_format($chartMax) }}</span><span>{{ number_format((int) round($chartMax * .75)) }}</span><span>{{ number_format((int) round($chartMax * .5)) }}</span><span>{{ number_format((int) round($chartMax * .25)) }}</span><span>0</span></div>
              <div class="chart-plot">
                <svg viewBox="0 0 620 190" preserveAspectRatio="none" aria-hidden="true">
                  <defs>
                    <linearGradient id="blueFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#1682f8" stop-opacity=".22"/><stop offset="100%" stop-color="#1682f8" stop-opacity=".025"/></linearGradient>
                    <linearGradient id="greenFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity=".2"/><stop offset="100%" stop-color="#10b981" stop-opacity=".025"/></linearGradient>
                  </defs>
                  <g class="chart-grid-lines"><path d="M0 12H620 M0 52H620 M0 92H620 M0 132H620 M0 172H620"/><path d="{{ $chartGridVertical }}"/></g>
                  <path d="{{ $personalChart['fill'] }}" fill="url(#blueFill)"/>
                  <path d="{{ $companyChart['fill'] }}" fill="url(#greenFill)"/>
                  <path class="chart-line-blue" d="{{ $personalChart['line'] }}"/>
                  <path class="chart-line-green" d="{{ $companyChart['line'] }}"/>
                  <g class="chart-points-blue">
                    @foreach($personalChart['points'] as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5"/>@endforeach
                  </g>
                  <g class="chart-points-green">
                    @foreach($companyChart['points'] as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5"/>@endforeach
                  </g>
                </svg>
                <div class="chart-x-labels">@foreach($growthLabels as $label)<span>{{ $label }}</span>@endforeach</div>
              </div>
            </div>
          </article>

          <article class="panel status-panel">
            <div class="panel-heading"><h2>Profile Version Status Overview</h2><button class="panel-more" aria-label="More options"><i class="bi bi-three-dots"></i></button></div>
            <div class="status-content">
              <div class="donut-wrap"><div class="donut-chart" style="background: {{ $statusDonutStyle }};"><div class="donut-hole"><strong>{{ number_format($statusTotal) }}</strong><span>Latest Versions</span></div></div></div>
              <div class="status-legend">
                @foreach($statusLegend as $status)
                  <div>
                    <span class="status-name"><i class="legend-dot" style="background: {{ $status['color'] }};"></i>{{ $status['label'] }}</span>
                    <strong>{{ number_format($status['count']) }} <small>({{ number_format($status['percentage'], 1) }}%)</small></strong>
                  </div>
                @endforeach
              </div>
            </div>
          </article>
        </section>

        <section class="tables-grid">
          @can('customers.manage')
          <article class="panel table-panel" id="account-requests">
            <div class="panel-heading"><h2>Recent Account Requests</h2><a class="view-all" href="{{ route('admin.customer-accounts.index') }}">View All <i class="bi bi-arrow-right"></i></a></div>
            <div class="table-responsive">
              <table class="table admin-table align-middle mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Registered On</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                  @forelse($recentCustomers as $customer)
                    @php($customerStatusClass = match($customer->status) { 'active' => 'pill-published', 'rejected' => 'pill-changes', default => 'pill-pending' })
                    <tr>
                      <td><span class="person-cell"><span class="mini-avatar avatar-one">{{ strtoupper(mb_substr($customer->name, 0, 2)) }}</span>{{ $customer->name }}</span></td>
                      <td>{{ $customer->email }}</td>
                      <td>{{ $customer->created_at?->format('d M Y') ?? '—' }}</td>
                      <td><span class="status-pill {{ $customerStatusClass }}">{{ ucwords(str_replace('_', ' ', $customer->status)) }}</span></td>
                      <td><a href="{{ route('admin.customer-accounts.index', ['search' => $customer->email]) }}" class="btn btn-sm btn-primary table-action">Review</a></td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No customer registrations yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </article>
          @endcan

          @can('profiles.manage')
          <article class="panel table-panel" id="submissions">
            <div class="panel-heading"><h2>Recent Profile Submissions</h2><a class="view-all" href="{{ route('admin.profiles.submissions') }}">View All <i class="bi bi-arrow-right"></i></a></div>
            <div class="table-responsive">
              <table class="table admin-table align-middle mb-0">
                <thead><tr><th>Title</th><th>Type</th><th>Submitted By</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                  @forelse($recentSubmissions as $submission)
                    @php
                      $submissionIsPersonal = $submission->profile?->profile_type === \App\Models\Profile::TYPE_PERSONAL;
                      $submissionName = $submissionIsPersonal
                        ? ($submission->personalDetails?->full_name ?: $submission->profile?->slug)
                        : ($submission->companyDetails?->company_name ?: $submission->profile?->slug);
                      $submissionCustomer = $submission->submittedByCustomer ?: $submission->createdByCustomer;
                      $submissionStatusClass = match($submission->review_status) {
                        'pending' => 'pill-review',
                        'approved' => 'pill-published',
                        'rejected', 'changes_requested' => 'pill-changes',
                        default => 'pill-draft',
                      };
                      $submissionDate = $submission->submitted_at ?: $submission->updated_at;
                    @endphp
                    <tr>
                      <td><span class="person-cell"><span class="mini-avatar {{ $submissionIsPersonal ? 'avatar-one' : 'logo-mini' }}">{{ strtoupper(mb_substr($submissionName ?: 'PR', 0, 2)) }}</span>{{ $submissionName ?: 'Untitled profile' }}</span></td>
                      <td>{{ $submissionIsPersonal ? 'Entrepreneur' : 'Company' }}</td>
                      <td>{{ $submissionCustomer?->email ?: '—' }}</td>
                      <td>{{ $submissionDate?->format('d M Y') ?: '—' }}</td>
                      <td><span class="status-pill {{ $submissionStatusClass }}">{{ ucwords(str_replace('_', ' ', $submission->review_status)) }}</span></td>
                      <td>
                        @if($submission->review_status === 'pending')
                          <a href="{{ route('admin.profiles.review', $submission) }}" class="btn btn-sm btn-outline-secondary table-view">Review</a>
                        @elseif($submission->profile)
                          <a href="{{ route('admin.profiles.edit', $submission->profile) }}" class="btn btn-sm btn-outline-secondary table-view">View</a>
                        @else
                          <span class="text-muted">—</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No customer profile submissions yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </article>
          @endcan
        </section>

        <section class="bottom-grid">
          <article class="panel storage-panel" id="storage">
            <div class="panel-heading"><h2><i class="bi bi-database-fill"></i> Storage Usage</h2>@can('storage.view')<a href="{{ route('admin.storage.index') }}" class="manage-link">Manage</a>@endcan</div>
            <div class="storage-progress" aria-label="Images are {{ number_format($storagePercent, 1) }} percent of registered media size"><span style="width: {{ $storagePercent }}%;"></span></div>
            <div class="storage-summary"><strong>{{ $formatBytes($totalMediaBytes) }}</strong><span>{{ number_format($mediaAssetCount) }} registered media assets</span></div>
            <div class="storage-breakdown"><span><i class="storage-dot storage-images"></i>Images</span><strong>{{ $formatBytes($imageBytes) }}</strong></div>
            <div class="storage-breakdown"><span><i class="storage-dot storage-docs"></i>Documents</span><strong>{{ $formatBytes($documentBytes) }}</strong></div>
            <div class="storage-breakdown"><span><i class="storage-dot storage-other"></i>Other Files</span><strong>{{ $formatBytes($otherBytes) }}</strong></div>
          </article>

          <article class="panel quick-actions-panel">
            <div class="panel-heading"><h2><i class="bi bi-lightning-charge-fill"></i> Quick Actions</h2></div>
            <div class="quick-actions">
              @can('profiles.manage')<a href="{{ route('admin.profiles.index') }}" class="quick-action quick-blue"><i class="bi bi-person-vcard"></i><span>Manage Profiles</span></a>@endcan
              @can('profiles.manage')<a href="{{ route('admin.profiles.submissions') }}" class="quick-action quick-blue"><i class="bi bi-file-earmark-check"></i><span>Review Submissions</span></a>@endcan
              @can('settings.manage')<a href="{{ route('admin.homepage-manager.index') }}" class="quick-action quick-green"><i class="bi bi-house-door"></i><span>Manage Homepage</span></a>@endcan
              @can('backups.manage')<a href="{{ route('admin.backups.index') }}" class="quick-action quick-red"><i class="bi bi-database"></i><span>Manage Backups</span></a>@endcan
            </div>
          </article>

          <article class="panel system-panel" id="settings">
            <div class="panel-heading"><h2><i class="bi bi-gear-wide-connected"></i> System Information</h2></div>
            <dl class="system-list">
              <div><dt>Total Profiles</dt><dd>{{ number_format($totalProfiles) }}</dd><span class="system-online"><i class="bi bi-circle-fill"></i> Live</span></div>
              <div><dt>Media Assets</dt><dd>{{ number_format($mediaAssetCount) }}</dd><span></span></div>
              <div><dt>Last Backup</dt><dd>{{ $lastBackup?->format('d M Y, h:i A') ?: 'No backup yet' }}</dd><span></span></div>
              <div><dt>PHP Version</dt><dd>{{ PHP_VERSION }}</dd><span></span></div>
              <div><dt>Laravel Version</dt><dd>{{ app()->version() }}</dd><span></span></div>
              <div><dt>Database</dt><dd>{{ strtoupper($databaseDriver) }}</dd><span></span></div>
            </dl>
          </article>
        </section>
@endsection
@section('scripts')
@endsection
