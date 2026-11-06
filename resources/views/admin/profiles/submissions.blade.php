@extends('admin.master.master')
@section('title','Profile Submissions')
@section('body')
<div class="module-heading"><div><div class="eyebrow">EDITORIAL REVIEW</div><h1><span class="heading-icon"><i class="bi bi-clipboard-check"></i></span>Profile Submissions</h1><p>Review new profiles and customer edits. Approving a version publishes it; returning it keeps the current public version unchanged.</p></div><a class="module-secondary" href="{{ route('admin.profiles.index') }}"><i class="bi bi-collection"></i> All profiles</a></div>
<div class="ops-stats"><div class="ops-stat violet"><i class="bi bi-hourglass-split"></i><div><strong>{{ number_format($pendingCount) }}</strong><span>Waiting for review</span></div></div></div>
<section class="module-panel module-list-panel">
  <form class="module-toolbar" method="get" action="{{ route('admin.profiles.submissions') }}"><div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Pending submissions</div><div class="d-flex flex-wrap gap-2 align-items-center"><select class="form-select" name="type"><option value="">All profile types</option><option value="personal" @selected(request('type')==='personal')>Personal</option><option value="company" @selected(request('type')==='company')>Company</option></select><label class="module-search"><i class="bi bi-search"></i><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search profile or customer..."></label><button class="module-secondary" type="submit">Filter</button></div></form>
  <div class="table-responsive"><table class="table admin-table module-table"><thead><tr><th>Profile</th><th>Type</th><th>Submitted by</th><th>Version</th><th>Submitted</th><th>Change summary</th><th class="actions-header">Action</th></tr></thead><tbody>
    @forelse($submissions as $version)
      @php($details = $version->profile->profile_type === 'personal' ? $version->personalDetails : $version->companyDetails)
      @php($name = $version->profile->profile_type === 'personal' ? ($details?->full_name ?? $version->profile->slug) : ($details?->company_name ?? $version->profile->slug))
      <tr><td><strong>{{ $name }}</strong><small class="d-block text-muted">/{{ $version->proposed_slug ?: $version->profile->slug }}</small></td><td>{{ ucfirst($version->profile->profile_type) }}</td><td>{{ $version->submittedByCustomer?->name ?? 'Admin submission' }}<small class="d-block text-muted">{{ $version->submittedByCustomer?->email }}</small></td><td>v{{ $version->version_number }}</td><td>{{ $version->submitted_at?->format('d M Y, h:i A') }}</td><td>{{ \Illuminate\Support\Str::limit($version->change_summary ?: '—', 70) }}</td><td class="actions-cell"><a class="btn btn-sm btn-primary" href="{{ route('admin.profiles.review',$version) }}"><i class="bi bi-eye"></i> Review</a></td></tr>
    @empty<tr><td colspan="7" class="text-center py-4">No pending profile submissions.</td></tr>@endforelse
  </tbody></table></div>
  @include('admin.shared._pagination', ['paginator' => $submissions])
</section>
@endsection
