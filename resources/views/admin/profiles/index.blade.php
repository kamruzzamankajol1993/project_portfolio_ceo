@extends('admin.master.master')
@section('title', request('type') === 'personal' ? 'Individual Profiles' : (request('type') === 'company' ? 'Company Profiles' : 'Profile Management'))
@section('body')
<div class="module-heading">
  <div><div class="eyebrow">PROFILE MANAGEMENT</div><h1><span class="heading-icon"><i class="bi bi-person-vcard"></i></span>{{ request('type') === 'personal' ? 'Individual Profiles' : (request('type') === 'company' ? 'Company Profiles' : 'All Profiles') }}</h1><p>Manage {{ request('type') === 'personal' ? 'individual' : (request('type') === 'company' ? 'company' : 'personal and company') }} profiles, assign customers, review versions and control publication.</p></div>
  <div class="d-flex flex-wrap gap-2 align-items-center">
    @if(request('type') !== 'company')
      <a class="module-btn" href="{{ route('admin.profiles.create', ['type' => 'personal']) }}"><i class="bi bi-person-plus"></i> Add Personal Profile</a>
    @endif
    @if(request('type') !== 'personal')
      <a class="module-btn" href="{{ route('admin.profiles.create', ['type' => 'company']) }}"><i class="bi bi-building"></i> Add Company Profile</a>
    @endif
  </div>
</div>
<div class="ops-stats">
  <div class="ops-stat"><i class="bi bi-collection"></i><div><strong>{{ number_format($stats['total']) }}</strong><span>Total profiles</span></div></div>
  <div class="ops-stat mint"><i class="bi bi-globe2"></i><div><strong>{{ number_format($stats['published']) }}</strong><span>Published</span></div></div>
  <div class="ops-stat violet"><i class="bi bi-hourglass-split"></i><div><strong>{{ number_format($stats['pending']) }}</strong><span>Pending submissions</span></div></div>
  <div class="ops-stat"><i class="bi bi-pencil-square"></i><div><strong>{{ number_format($stats['drafts']) }}</strong><span>Draft / correction</span></div></div>
</div>
<section class="module-panel module-list-panel">
  <form class="module-toolbar" method="get" action="{{ route('admin.profiles.index') }}">
    <div class="module-toolbar-hint"><span class="toolbar-indicator"></span> {{ request('type') === 'personal' ? 'Individual profiles' : (request('type') === 'company' ? 'Company profiles' : 'All profiles') }}</div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <select class="form-select" name="type" aria-label="Profile type"><option value="">All types</option><option value="personal" @selected(request('type')==='personal')>Personal</option><option value="company" @selected(request('type')==='company')>Company</option></select>
      <select class="form-select" name="status" aria-label="Profile status"><option value="">All statuses</option><option value="published" @selected(request('status')==='published')>Published</option><option value="pending" @selected(request('status')==='pending')>Pending review</option><option value="draft" @selected(request('status')==='draft')>Draft</option></select>
      <label class="module-search"><i class="bi bi-search"></i><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search profile or slug..."></label>
      <button class="module-secondary" type="submit">Filter</button>
    </div>
  </form>
  <div class="table-responsive"><table class="table admin-table module-table"><thead><tr><th>Profile</th><th>Type</th><th>Status</th><th>Customer</th><th>Published</th><th>Latest version</th><th class="actions-header">Actions</th></tr></thead><tbody>
    @forelse($profiles as $profile)
      @php
        $pending = $profile->latestVersion?->review_status === 'pending';
        $details = $profile->profile_type === 'personal'
            ? ($profile->publishedVersion?->personalDetails ?? $profile->latestVersion?->personalDetails)
            : ($profile->publishedVersion?->companyDetails ?? $profile->latestVersion?->companyDetails);
        $name = $profile->profile_type === 'personal' ? ($details?->full_name ?? $profile->slug) : ($details?->company_name ?? $profile->slug);
      @endphp
      <tr><td><strong>{{ $name }}</strong><small class="d-block text-muted">/{{ $profile->slug }}</small></td><td><span class="soft-tag">{{ ucfirst($profile->profile_type) }}</span></td><td>@if($pending)<span class="soft-tag tag-violet">Pending review</span>@elseif($profile->latestVersion?->review_status==='changes_requested')<span class="soft-tag tag-inactive">Correction requested</span>@elseif($profile->status==='published')<span class="soft-tag tag-active">Published</span>@else<span class="soft-tag tag-inactive">Draft</span>@endif</td><td>{{ $profile->customerAccess->first()?->customer?->name ?? $profile->createdByCustomer?->name ?? '—' }}</td><td>{{ $profile->published_at?->format('d M Y') ?? '—' }}</td><td>{{ $profile->latestVersion?->version_number ? 'v'.$profile->latestVersion->version_number.' · '.str($profile->latestVersion->review_status)->replace('_',' ')->title() : '—' }}</td><td class="actions-cell"><div class="d-flex justify-content-end flex-wrap gap-2">@if($pending)<a class="btn btn-sm btn-primary" href="{{ route('admin.profiles.review',$profile->latestVersion) }}">Review</a>@else<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.profiles.edit',$profile) }}">Edit</a>@endif<a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.profiles.history',$profile) }}">History</a>@if($profile->status==='published')<a class="btn btn-sm btn-light" href="{{ $profile->publicUrl() }}" target="_blank" rel="noopener">View</a>@endif</div></td></tr>
    @empty<tr><td colspan="7" class="text-center py-4">No profiles found.</td></tr>@endforelse
  </tbody></table></div>
  @include('admin.shared._pagination', ['paginator' => $profiles])
</section>
@endsection
