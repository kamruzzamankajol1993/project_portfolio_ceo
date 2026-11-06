@extends('website.master.master')

@section('title'){{ $profile ? 'Edit Company Profile' : 'Create Company Profile' }} - {{ $siteSetting->site_name }}@endsection

@section('css')
<link href="{{ asset('/') }}public/website/account-pages.css" rel="stylesheet">
<style>
.editor-repeat-row{position:relative;border:1px solid #e8eef7;border-radius:10px;padding:13px;margin:10px 0;background:#fff}.profile-dynamic-section{border:1px solid #e3ebf7;border-radius:11px;padding:14px;margin:12px 0;background:#f9fbff}.profile-section-items{margin-top:12px;padding-left:12px;border-left:3px solid #dce9fc}.editor-repeat-action{display:flex;flex-direction:column;justify-content:flex-end}.editor-form-actions{gap:14px}.editor-form-actions .action-buttons{display:flex;flex-wrap:wrap;gap:8px}.profile-current-media{max-width:170px;max-height:100px;object-fit:contain;border:1px solid #e6edf7;border-radius:8px;padding:5px;margin:6px 0}.customer-review-lock{padding:14px 16px;border:1px solid #f1d79a;background:#fff9e9;color:#76500a;border-radius:9px;margin-bottom:16px}
</style>
@endsection

@section('body')
@php
  $formLinks = old('links', $version?->links?->map(fn($item) => $item->only(['link_type','label','url','icon']))->all() ?: [['link_type'=>'website','label'=>'','url'=>'','icon'=>'']]);
  $formReferences = old('references', $version?->references?->map(fn($item) => $item->only(['title','publisher','url','source_date','description']))->all() ?: [['title'=>'','publisher'=>'','url'=>'','source_date'=>'','description'=>'']]);
  $formSections = old('sections', $version?->sections?->map(fn($section) => ['title'=>$section->title,'intro'=>$section->intro,'section_type'=>$section->section_type,'layout'=>$section->layout,'is_visible'=>$section->is_visible,'items'=>$section->items->map(fn($item) => $item->only(['title','subtitle','description','icon','image_path','url','is_visible']))->all()])->all() ?: [['title'=>'','intro'=>'','section_type'=>'content','items'=>[['title'=>'','subtitle'=>'','description'=>'','icon'=>'','url'=>'']]]]);
  $logoMedia = $version?->mediaLinks?->firstWhere('role','logo')?->media;
  $bannerMedia = $version?->mediaLinks?->firstWhere('role','banner')?->media;
@endphp
<main class="editor-wrap">
  <div class="editor-heading"><div><div class="breadcrumb-line"><a href="{{ route('site.home') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('customer.dashboard') }}">My account</a><i class="bi bi-chevron-right"></i><strong>Company profile</strong></div><span class="auth-eyebrow">YOUR BUSINESS PRESENCE</span><h1>{{ $profile ? 'Edit company profile' : 'Create company profile' }}</h1><p>Manage your organisation's details, story, media and official links.</p></div><span class="profile-status"><i class="bi bi-{{ $lockedForReview ? 'hourglass-split' : 'pencil-square' }}"></i> {{ $lockedForReview ? 'Under review' : ($profile ? 'Editing draft' : 'New profile') }}</span></div>
  @if(session('status'))<div class="customer-notice customer-notice-success" role="status">{{ session('status') }}</div>@endif
  @if($errors->any())<div class="customer-notice customer-notice-error" role="alert"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  @if($version?->review_note && !$lockedForReview)<div class="customer-review-lock"><strong><i class="bi bi-arrow-return-left"></i> Administrator correction request</strong><p class="mb-0 mt-1">{{ $version->review_note }}</p></div>@endif
  @if($lockedForReview)
    <div class="customer-review-lock"><strong><i class="bi bi-hourglass-split"></i> This version is waiting for administrator review.</strong><p class="mb-0 mt-1">You can continue editing after the administrator returns it for correction. Your currently published profile, if any, remains unchanged.</p></div>
  @endif
  <div class="editor-layout">
    <aside class="editor-sidebar">
      <div class="editor-user-card"><div class="editor-user-avatar"><i class="bi bi-person-fill"></i></div><div><strong>{{ $customer->name }}</strong><small>{{ $customer->customer_code }}</small></div></div>
      <span class="editor-nav-label">PROFILE MANAGEMENT</span>
      <nav class="editor-nav" aria-label="Profile management"><a href="{{ route('customer.personal-profile-update') }}"><i class="bi bi-person-vcard"></i> Personal Profile</a><a class="active" href="{{ route('customer.company-profile-update') }}"><i class="bi bi-buildings"></i> Company Profile</a></nav>
      <div class="editor-sidebar-note"><i class="bi bi-shield-check"></i><strong>Administrator approval required.</strong><p>Drafts are private. Submitted changes are published only after an administrator approves them.</p></div>
      <a class="editor-back-link" href="{{ route('customer.dashboard') }}"><i class="bi bi-arrow-left"></i> Back to dashboard</a>
    </aside>
    <section class="editor-main">
      <form class="profile-editor-form" method="post" enctype="multipart/form-data" data-server-form action="{{ $profile ? route('customer.profiles.company.update',$profile) : route('customer.profiles.company.store') }}">
        @csrf @if($profile)@method('PUT')@endif
        <fieldset @disabled($lockedForReview) style="border:0;padding:0;margin:0;min-width:0">
        <section class="editor-card profile-identity-card">
          <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-buildings"></i></span><div><h2>Company identity</h2><p>Add your official name, logo and banner image.</p></div></div><span class="section-step">01</span></div>
          <div class="company-upload-grid">
            <div class="company-logo-upload"><div class="company-logo-preview" id="companyLogoPreview">@if($logoMedia)<img src="{{ $logoMedia->url }}" alt="Company logo">@else<i class="bi bi-buildings"></i>@endif</div><div><strong>Company logo</strong><small>Square image recommended</small><label class="btn btn-outline-primary upload-button" for="companyLogo"><i class="bi bi-upload"></i> Upload logo</label><input class="visually-hidden" id="companyLogo" name="company_logo" type="file" accept="image/png,image/jpeg,image/webp" data-image-preview="companyLogoPreview" @disabled($lockedForReview)>@if($logoMedia)<small>Current logo saved</small>@endif</div></div>
            <div class="company-banner-upload" id="companyBannerPreview" @if($bannerMedia) style="background-image:linear-gradient(rgba(8,43,104,.12),rgba(8,43,104,.12)),url('{{ $bannerMedia->url }}')" @endif><div class="banner-upload-overlay"><i class="bi bi-image"></i><strong>Company banner</strong><small>Wide image recommended</small><label class="btn btn-light upload-button" for="companyBanner"><i class="bi bi-upload"></i> Upload banner</label></div><input class="visually-hidden" id="companyBanner" name="company_banner" type="file" accept="image/png,image/jpeg,image/webp" data-image-preview="companyBannerPreview" @disabled($lockedForReview)></div>
          </div>
          <div class="editor-form-grid">
            <div class="form-field"><label for="companyName">Company name <span class="required-star">*</span></label><input id="companyName" name="company_name" type="text" value="{{ old('company_name',$details->company_name ?? '') }}" placeholder="e.g. GrowBig Digital" required @disabled($lockedForReview)></div>
            <div class="form-field"><label for="companySlug">Profile URL slug</label><input id="companySlug" name="proposed_slug" type="text" value="{{ old('proposed_slug',$version->proposed_slug ?? $profile->slug ?? '') }}" placeholder="company-name" @disabled($lockedForReview)></div>
            <div class="form-field"><label for="companyTagline">Tagline</label><input id="companyTagline" name="tagline" type="text" value="{{ old('tagline',$details->tagline ?? '') }}" placeholder="A short description of your business" @disabled($lockedForReview)></div>
            <div class="form-field"><label for="companyIndustry">Industry</label><select id="companyIndustry" name="industry_id" @disabled($lockedForReview)><option value="">Select industry</option>@foreach($industries as $industry)<option value="{{ $industry->id }}" @selected((string)old('industry_id',$details->industry_id ?? '') === (string)$industry->id)>{{ $industry->name }}</option>@endforeach</select></div>
            <div class="form-field"><label for="companyFounded">Year founded</label><input id="companyFounded" name="founded_year" type="number" min="1800" max="{{ now()->year }}" value="{{ old('founded_year',$details->founded_year ?? '') }}" placeholder="e.g. 2018" @disabled($lockedForReview)></div>
          </div>
        </section>
        <section class="editor-card">
          <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-info-circle"></i></span><div><h2>Company information</h2><p>Help visitors understand what your organisation does.</p></div></div><span class="section-step">02</span></div>
          <div class="editor-form-grid">
            <div class="form-field"><label>Headquarters / Location</label><input name="headquarters" type="text" value="{{ old('headquarters',$details->headquarters ?? '') }}" placeholder="City, Country" @disabled($lockedForReview)></div>
            <div class="form-field"><label>Company size</label><select name="company_size" @disabled($lockedForReview)><option value="">Select company size</option>@foreach(['1–10 employees','11–50 employees','51–200 employees','201–500 employees','501–1,000 employees','1,001+ employees'] as $size)<option value="{{ $size }}" @selected(old('company_size',$details->company_size ?? '') === $size)>{{ $size }}</option>@endforeach</select></div>
            <div class="form-field"><label>Business type</label><input name="business_type" type="text" value="{{ old('business_type',$details->business_type ?? '') }}" placeholder="Private Company, Startup, Non-profit..." @disabled($lockedForReview)></div>
            <div class="form-field"><label>Area served</label><input name="area_served" type="text" value="{{ old('area_served',$details->area_served ?? '') }}" placeholder="Worldwide / Bangladesh / South Asia" @disabled($lockedForReview)></div>
            <div class="form-field"><label>Official website</label><input name="official_website" type="url" value="{{ old('official_website',$details->official_website ?? '') }}" placeholder="https://company.com" @disabled($lockedForReview)></div>
            <div class="form-field"><label>Public email</label><input name="public_email" type="email" value="{{ old('public_email',$details->public_email ?? '') }}" placeholder="info@company.com" @disabled($lockedForReview)></div>
            <div class="form-field"><label>Public phone</label><input name="public_phone" type="tel" value="{{ old('public_phone',$details->public_phone ?? '') }}" placeholder="+880 1XXXXXXXXX" @disabled($lockedForReview)></div>
            <div class="form-field field-span-2"><label>Short description</label><textarea name="short_description" rows="2" placeholder="A short introduction displayed in profile listings..." @disabled($lockedForReview)>{{ old('short_description',$details->short_description ?? '') }}</textarea></div>
            <div class="form-field field-span-2"><label>Company overview</label><textarea name="overview" rows="4" placeholder="Describe the organisation, its history, activities and areas of operation..." @disabled($lockedForReview)>{{ old('overview',$details->overview ?? '') }}</textarea></div>
            <div class="form-field field-span-2"><label>History & background</label><textarea name="history_background" rows="4" placeholder="Company history, milestones and background..." @disabled($lockedForReview)>{{ old('history_background',$details->history_background ?? '') }}</textarea></div>
            <div class="form-field"><label>Mission</label><textarea name="mission" rows="3" @disabled($lockedForReview)>{{ old('mission',$details->mission ?? '') }}</textarea></div>
            <div class="form-field"><label>Vision</label><textarea name="vision" rows="3" @disabled($lockedForReview)>{{ old('vision',$details->vision ?? '') }}</textarea></div>
            <div class="form-field"><label>Parent company</label><input name="parent_company" value="{{ old('parent_company',$details->parent_company ?? '') }}" placeholder="Parent organisation, if any" @disabled($lockedForReview)></div>
            <div class="form-field field-span-2 border rounded p-3">
              <h3 class="h6">Know This Company? CTA</h3>
              <div class="form-check mb-2"><input type="hidden" name="accuracy_cta_enabled" value="0"><input class="form-check-input" type="checkbox" id="accuracyCtaEnabled" name="accuracy_cta_enabled" value="1" @checked(old('accuracy_cta_enabled',$details->accuracy_cta_enabled ?? true)) @disabled($lockedForReview)><label class="form-check-label" for="accuracyCtaEnabled">Show this section publicly</label></div>
              <div class="form-field"><label>CTA title</label><input class="form-control" name="accuracy_cta_title" value="{{ old('accuracy_cta_title',$details->accuracy_cta_title ?? 'Know This Company?') }}" @disabled($lockedForReview)></div>
              <div class="form-field"><label>CTA description</label><textarea class="form-control" name="accuracy_cta_description" rows="2" @disabled($lockedForReview)>{{ old('accuracy_cta_description',$details->accuracy_cta_description ?? 'Help us keep this information accurate. You can suggest an edit or request a company profile.') }}</textarea></div>
              <div class="form-field"><label>Primary button label</label><input class="form-control" name="accuracy_cta_button_label" value="{{ old('accuracy_cta_button_label',$details->accuracy_cta_button_label ?? 'Suggest an Edit') }}" @disabled($lockedForReview)></div>
              <div class="form-field"><label>Primary button URL</label><input class="form-control" type="url" name="accuracy_cta_button_url" value="{{ old('accuracy_cta_button_url',$details->accuracy_cta_button_url ?? '') }}" placeholder="Default: suggestion form" @disabled($lockedForReview)></div>
              <div class="form-field"><label>Secondary button label</label><input class="form-control" name="accuracy_cta_secondary_label" value="{{ old('accuracy_cta_secondary_label',$details->accuracy_cta_secondary_label ?? 'Request a Company Profile') }}" @disabled($lockedForReview)></div>
              <div class="form-field"><label>Secondary button URL</label><input class="form-control" type="url" name="accuracy_cta_secondary_url" value="{{ old('accuracy_cta_secondary_url',$details->accuracy_cta_secondary_url ?? '') }}" placeholder="Default: customer registration" @disabled($lockedForReview)></div>
            </div>
            <div class="form-field"><label>Public contact visibility</label><div class="form-check"><input type="hidden" name="show_public_email" value="0"><input class="form-check-input" type="checkbox" id="showPublicEmail" name="show_public_email" value="1" @checked(old('show_public_email',$details->show_public_email ?? false)) @disabled($lockedForReview)><label class="form-check-label" for="showPublicEmail">Show public email on website</label></div><div class="form-check"><input type="hidden" name="show_public_phone" value="0"><input class="form-check-input" type="checkbox" id="showPublicPhone" name="show_public_phone" value="1" @checked(old('show_public_phone',$details->show_public_phone ?? false)) @disabled($lockedForReview)><label class="form-check-label" for="showPublicPhone">Show public phone on website</label></div></div>
          </div>
        </section>
        @include('website.customer.partials.profile-extra-fields')
        <section class="editor-card">
          <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-images"></i></span><div><h2>Additional images</h2><p>Upload supporting images for the public profile gallery.</p></div></div><span class="section-step">09</span></div>
          @if($version?->mediaLinks?->where('role','gallery')->isNotEmpty())<div class="d-flex flex-wrap gap-2 mb-3">@foreach($version->mediaLinks->where('role','gallery') as $mediaLink)<img class="profile-current-media" src="{{ $mediaLink->media?->url }}" alt="Profile gallery image">@endforeach</div>@endif
          <div class="form-field"><label>Gallery images (up to 12 new images)</label><input class="form-control" type="file" name="gallery[]" accept="image/png,image/jpeg,image/webp" multiple @disabled($lockedForReview)></div>
        </section>
        <section class="editor-card"><div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-send-check"></i></span><div><h2>Submit for review</h2><p>Save privately or send the complete profile to the administrator.</p></div></div><span class="section-step">10</span></div><div class="form-field"><label>Change summary (optional)</label><textarea name="change_summary" rows="2" placeholder="Briefly describe what you added or corrected..." @disabled($lockedForReview)>{{ old('change_summary',$version->change_summary ?? '') }}</textarea></div></section>
        <div class="editor-form-actions"><div class="save-feedback" role="status"><i class="bi bi-shield-check"></i> Your changes will not be public until an administrator approves them.</div><div class="action-buttons"><a class="btn btn-light cancel-button" href="{{ route('customer.dashboard') }}">Cancel</a><button class="btn btn-outline-primary" type="submit" name="submit_action" value="draft" @disabled($lockedForReview)><i class="bi bi-save"></i> Save Draft</button><button class="btn btn-primary" type="submit" name="submit_action" value="submit" @disabled($lockedForReview)>Submit for Review <i class="bi bi-arrow-right"></i></button></div></div>
        </fieldset>
      </form>
    </section>
    <aside class="editor-aside"><div class="editor-info-card"><span class="info-card-icon"><i class="bi bi-clipboard-check"></i></span><h3>Before you submit</h3><p>Make sure your information is accurate, current and supported by reliable details.</p><ul><li><i class="bi bi-check-circle-fill"></i> Check company name and dates</li><li><i class="bi bi-check-circle-fill"></i> Add official links and sources</li><li><i class="bi bi-check-circle-fill"></i> Upload clear company images</li></ul></div><div class="editor-info-card pale-info"><span class="info-card-icon"><i class="bi bi-hourglass-split"></i></span><h3>Review process</h3><p>Any edit creates a new reviewable version. The currently published profile stays unchanged until approval.</p><span class="review-flow"><b>Draft</b><i class="bi bi-arrow-right"></i><b>In review</b><i class="bi bi-arrow-right"></i><b>Published</b></span></div></aside>
  </div>
</main>
@endsection

@section('scripts')<script src="{{ asset('/') }}public/website/account-pages.js"></script>@endsection
