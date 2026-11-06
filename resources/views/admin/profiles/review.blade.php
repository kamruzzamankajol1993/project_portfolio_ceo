@extends('admin.master.master')
@section('title','Review Profile Submission')
@section('body')
@php
  $profile = $version->profile;
  $isPersonal = $profile->profile_type === 'personal';
  $details = $isPersonal ? $version->personalDetails : $version->companyDetails;
  $name = $isPersonal ? ($details?->full_name ?? $profile->slug) : ($details?->company_name ?? $profile->slug);
  $publishedDetails = $isPersonal ? $profile->publishedVersion?->personalDetails : $profile->publishedVersion?->companyDetails;
  $publishedName = $isPersonal ? ($publishedDetails?->full_name ?? null) : ($publishedDetails?->company_name ?? null);
@endphp
<div class="module-heading"><div><div class="eyebrow">EDITORIAL REVIEW · VERSION {{ $version->version_number }}</div><h1><span class="heading-icon"><i class="bi bi-clipboard-check"></i></span>Review {{ $isPersonal ? 'Personal' : 'Company' }} Profile</h1><p>{{ $name }} · submitted {{ $version->submitted_at?->diffForHumans() }}</p></div><a class="module-secondary" href="{{ route('admin.profiles.submissions') }}"><i class="bi bi-arrow-left"></i> Back to submissions</a></div>
<div class="row g-3">
  <div class="col-lg-8">
    <section class="module-panel mb-3">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-2"><div><span class="soft-tag tag-violet">PENDING REVIEW</span><h2 class="h4 mt-2 mb-1">{{ $name }}</h2><p class="text-muted mb-0">{{ $version->proposed_slug ?: $profile->slug }}</p></div><span class="soft-tag">{{ ucfirst($profile->profile_type) }} profile</span></div>
      @if($version->change_summary)<div class="alert alert-light border mt-3 mb-0"><strong>Customer change summary</strong><p class="mb-0 mt-1">{{ $version->change_summary }}</p></div>@endif
      @if($publishedName)<div class="alert alert-info mt-3 mb-0"><strong>Existing public profile:</strong> {{ $publishedName }}. Approval will replace the current public version; returning it will leave the existing version unchanged.</div>@endif
    </section>
    <section class="module-panel mb-3"><h2 class="h5 mb-3">{{ $isPersonal ? 'Personal information' : 'Company information' }}</h2><div class="table-responsive"><table class="table table-sm align-middle"><tbody>
      @if($isPersonal)
        @foreach(['full_name'=>'Full name','headline'=>'Headline','professional_title'=>'Professional title','current_position'=>'Current position','company_organisation'=>'Company / organisation','location'=>'Location','hometown'=>'Hometown','nationality'=>'Nationality','date_of_birth'=>'Date of birth','occupation'=>'Occupation','public_contact_email'=>'Public email','public_phone'=>'Public phone','personal_website'=>'Website','short_bio'=>'Short bio','about'=>'About','profile_quote'=>'Profile quote','early_life'=>'Early life','career_entrepreneurship'=>'Career & entrepreneurship','education_summary'=>'Education','awards_publications_recognition'=>'Awards & recognition'] as $field=>$label)
          @if(filled($details?->{$field}))<tr><th style="width:190px">{{ $label }}</th><td>{!! nl2br(e($details->{$field})) !!}</td></tr>@endif
        @endforeach
      @else
        @foreach(['company_name'=>'Company name','tagline'=>'Tagline','founded_year'=>'Founded year','headquarters'=>'Headquarters','company_size'=>'Company size','business_type'=>'Business type','area_served'=>'Area served','official_website'=>'Website','public_email'=>'Public email','public_phone'=>'Public phone','short_description'=>'Short description','overview'=>'Overview','history_background'=>'History & background','mission'=>'Mission','vision'=>'Vision','parent_company'=>'Parent company'] as $field=>$label)
          @if(filled($details?->{$field}))<tr><th style="width:190px">{{ $label }}</th><td>{!! nl2br(e($details->{$field})) !!}</td></tr>@endif
        @endforeach
      @endif
      @if($details?->industry)<tr><th>Industry</th><td>{{ $details->industry->name }}</td></tr>@endif
    </tbody></table></div></section>
    @foreach($version->sections as $section)<section class="module-panel mb-3"><h2 class="h5">{{ $section->title }}</h2>@if($section->intro)<p>{{ $section->intro }}</p>@endif @foreach($section->items as $item)<div class="border rounded p-3 mb-2"><strong>{{ $item->title }}</strong>@if($item->subtitle)<div class="text-muted">{{ $item->subtitle }}</div>@endif @if($item->description)<p class="mb-1">{{ $item->description }}</p>@endif @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener">{{ $item->url }}</a>@endif</div>@endforeach</section>@endforeach
    @if($version->mediaLinks->isNotEmpty())<section class="module-panel mb-3"><h2 class="h5">Uploaded media</h2><div class="d-flex flex-wrap gap-3">@foreach($version->mediaLinks as $mediaLink)@if($mediaLink->media)<figure class="mb-0" style="max-width:180px"><img src="{{ $mediaLink->media->url }}" alt="{{ $mediaLink->media->alt_text ?: $name }}" style="width:100%;height:130px;object-fit:contain;border:1px solid #e5eaf1;border-radius:8px;padding:4px"><figcaption class="small text-muted mt-1">{{ str($mediaLink->role)->replace('_',' ')->title() }}</figcaption></figure>@endif @endforeach</div></section>@endif
        @if($version->links->isNotEmpty())<section class="module-panel mb-3"><h2 class="h5">Links</h2>@foreach($version->links as $link)<p class="mb-1"><strong>{{ $link->label ?: $link->link_type }}:</strong> <a href="{{ $link->url }}" target="_blank" rel="noopener">{{ $link->url }}</a></p>@endforeach</section>@endif
    @if($version->references->isNotEmpty())<section class="module-panel mb-3"><h2 class="h5">References</h2>@foreach($version->references as $reference)<div class="mb-2"><strong>{{ $reference->title }}</strong> @if($reference->publisher)<span class="text-muted"> — {{ $reference->publisher }}</span>@endif @if($reference->url)<div><a href="{{ $reference->url }}" target="_blank" rel="noopener">{{ $reference->url }}</a></div>@endif @if($reference->description)<p class="mb-0">{{ $reference->description }}</p>@endif</div>@endforeach</section>@endif
    @if($version->relationships->isNotEmpty())<section class="module-panel mb-3"><h2 class="h5">Related profiles</h2>@foreach($version->relationships as $relationship)@php($related=$relationship->relatedProfile)@php($relatedDetails=$related?->profile_type==='personal' ? $related->publishedVersion?->personalDetails : $related?->publishedVersion?->companyDetails)@php($relatedName=$related?->profile_type==='personal' ? ($relatedDetails?->full_name ?? $related?->slug) : ($relatedDetails?->company_name ?? $related?->slug))<p class="mb-1"><strong>{{ str($relationship->relation_type)->replace('_',' ')->title() }}:</strong> {{ $relatedName }} @if($relationship->position) — {{ $relationship->position }}@endif</p>@if($relationship->description)<p class="text-muted small">{{ $relationship->description }}</p>@endif @endforeach</section>@endif
    @if($version->seo)<section class="module-panel mb-3"><h2 class="h5">SEO settings</h2><p class="mb-1"><strong>Meta title:</strong> {{ $version->seo->meta_title ?: '—' }}</p><p class="mb-1"><strong>Meta description:</strong> {{ $version->seo->meta_description ?: '—' }}</p><p class="mb-0"><strong>Robots:</strong> {{ $version->seo->robots }}</p></section>@endif
  </div>
  <div class="col-lg-4">
    <section class="module-panel mb-3"><h2 class="h5">Submission details</h2><p class="mb-1"><strong>Customer:</strong> {{ $version->submittedByCustomer?->name ?? '—' }}</p><p class="mb-1"><strong>Email:</strong> {{ $version->submittedByCustomer?->email ?? '—' }}</p><p class="mb-1"><strong>Submitted:</strong> {{ $version->submitted_at?->format('d M Y, h:i A') }}</p><p class="mb-0"><strong>Profile:</strong> <a href="{{ route('admin.profiles.history',$profile) }}">View full history</a></p></section>
    @if($version->review_status === 'pending')
      <section class="module-panel mb-3"><h2 class="h5">Approve & publish</h2><p class="small text-muted">The approved version becomes the public version immediately.</p><form method="post" action="{{ route('admin.profiles.approve',$version) }}" data-confirm="Approve and publish this profile version?" data-confirm-button="Approve and publish">@csrf<button class="btn btn-success w-100" type="submit"><i class="bi bi-check-circle"></i> Approve & Publish</button></form></section>
      <section class="module-panel mb-3"><h2 class="h5">Return for correction</h2><p class="small text-muted">Explain what needs to be corrected. The customer can edit and resubmit; the exchange is saved in history.</p><form method="post" action="{{ route('admin.profiles.return',$version) }}">@csrf<label class="form-label">Correction note *</label><textarea class="form-control mb-3" name="review_note" rows="4" required minlength="5" maxlength="2000" placeholder="Please correct the company name, add source links, clarify the dates..."></textarea><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-arrow-return-left"></i> Return to customer</button></form></section>
      <section class="module-panel"><h2 class="h5">Decline submission</h2><form method="post" action="{{ route('admin.profiles.reject',$version) }}">@csrf<textarea class="form-control mb-2" name="review_note" rows="2" placeholder="Optional reason"></textarea><button class="btn btn-outline-danger w-100" type="submit">Decline submission</button></form></section>
    @else
      <div class="alert alert-info">This version is no longer pending review.</div>
    @endif
    <section class="module-panel mt-3"><h2 class="h5">Review history</h2>@forelse($version->reviewLogs as $log)<div class="border-start border-2 ps-3 pb-3 mb-2"><strong>{{ str($log->action)->replace('_',' ')->title() }}</strong><small class="d-block text-muted">{{ $log->created_at?->format('d M Y, h:i A') }} · {{ $log->admin?->name ?? $log->customer?->name ?? 'System' }}</small>@if($log->note)<p class="small mb-0 mt-1">{{ $log->note }}</p>@endif</div>@empty<p class="text-muted small mb-0">No review activity recorded yet.</p>@endforelse</section>
  </div>
</div>
@endsection
