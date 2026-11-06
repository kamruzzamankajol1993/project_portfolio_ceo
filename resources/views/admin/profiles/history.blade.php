@extends('admin.master.master')
@section('title','Profile Revision History')
@section('body')
<div class="module-heading"><div><div class="eyebrow">PROFILE MANAGEMENT</div><h1><span class="heading-icon"><i class="bi bi-clock-history"></i></span>Revision History</h1><p>Every submission, approval, correction request and resubmission for this profile.</p></div><a class="module-secondary" href="{{ route('admin.profiles.index') }}"><i class="bi bi-arrow-left"></i> Back to profiles</a></div>
@foreach($versions as $version)
  @php($details = $profile->profile_type==='personal' ? $version->personalDetails : $version->companyDetails)
  @php($name = $profile->profile_type==='personal' ? ($details?->full_name ?? $profile->slug) : ($details?->company_name ?? $profile->slug))
  <section class="module-panel mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start"><div><h2 class="h5 mb-1">Version {{ $version->version_number }} · {{ $name }}</h2><p class="text-muted small mb-0">Created {{ $version->created_at?->format('d M Y, h:i A') }} · Submitted {{ $version->submitted_at?->format('d M Y, h:i A') ?? 'Not submitted' }}</p></div><span class="soft-tag {{ $version->review_status==='approved'?'tag-active':($version->review_status==='pending'?'tag-violet':'tag-inactive') }}">{{ str($version->review_status)->replace('_',' ')->title() }}</span></div>
    @if($version->review_note)<div class="alert alert-warning mt-3 mb-0"><strong>Latest admin note:</strong> {{ $version->review_note }}</div>@endif
    <div class="mt-3">@forelse($version->reviewLogs as $log)<div class="border-start border-2 ps-3 pb-3 mb-2"><strong>{{ str($log->action)->replace('_',' ')->title() }}</strong><small class="d-block text-muted">{{ $log->created_at?->format('d M Y, h:i A') }} · {{ $log->admin?->name ?? $log->customer?->name ?? 'System' }}</small>@if($log->note)<p class="small mb-0 mt-1">{{ $log->note }}</p>@endif</div>@empty<p class="text-muted small">No history for this version.</p>@endforelse</div>
    @if($version->review_status==='pending')<a class="btn btn-sm btn-primary" href="{{ route('admin.profiles.review',$version) }}">Open review</a>@endif
  </section>
@endforeach
@endsection
