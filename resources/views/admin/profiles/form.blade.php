@extends('admin.master.master')
@section('title',$formMode === 'create' ? 'Create Profile' : 'Edit Profile')
@section('body')
@php
  $isPersonal = old('profile_type', $profile->profile_type ?: 'personal') === 'personal';
  $formLinks = old('links', $version->links?->map(fn($item) => $item->only(['link_type','label','url','icon']))->all() ?: [['link_type'=>'website','label'=>'','url'=>'','icon'=>'']]);
  $formReferences = old('references', $version->references?->map(fn($item) => $item->only(['title','publisher','url','source_date','description']))->all() ?: [['title'=>'','publisher'=>'','url'=>'','source_date'=>'','description'=>'']]);
  $formSections = old('sections', $version->sections?->map(fn($section) => ['title'=>$section->title,'intro'=>$section->intro,'section_type'=>$section->section_type,'layout'=>$section->layout,'is_visible'=>$section->is_visible,'items'=>$section->items->map(fn($item) => $item->only(['title','subtitle','description','icon','image_path','url','is_visible']))->all()])->all() ?: [['title'=>'','intro'=>'','items'=>[['title'=>'','subtitle'=>'','description'=>'','icon'=>'','url'=>'']]]]);
  $formRelationships = old('relationships', $formRelationships ?? ($version->relationships?->map(fn($item) => $item->only(['related_profile_id','relation_type','position','description']))->all() ?: [['related_profile_id'=>'','relation_type'=>'founder','position'=>'','description'=>'']]));
@endphp
<div class="module-heading"><div><div class="eyebrow">PROFILE MANAGEMENT</div><h1><span class="heading-icon"><i class="bi bi-{{ $formMode==='create'?'plus-circle':'pencil-square' }}"></i></span>{{ $formMode==='create'?'Create profile':'Edit profile' }}</h1><p>Create a personal or company profile, optionally assign it to a customer, and publish only when ready.</p></div><a class="module-secondary" href="{{ route('admin.profiles.index') }}"><i class="bi bi-arrow-left"></i> Back to profiles</a></div>
@if($errors->any())<div class="alert alert-danger"><strong>Please correct the following:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="module-panel module-form" method="post" enctype="multipart/form-data" action="{{ $formMode==='create' ? route('admin.profiles.store') : route('admin.profiles.update',$profile) }}">
  @csrf @if($formMode==='edit')@method('PUT')@endif
  <div class="form-grid">
    @if($formMode==='create')<label>Profile type *<select class="form-select" name="profile_type" id="adminProfileType" required><option value="personal" @selected($isPersonal)>Personal profile</option><option value="company" @selected(!$isPersonal)>Company profile</option></select></label>@else<input type="hidden" name="profile_type" value="{{ $profile->profile_type }}"><div><span class="form-hint">Profile type</span><strong class="d-block">{{ ucfirst($profile->profile_type) }}</strong></div>@endif
    <label>Assign to customer<select class="form-select" name="customer_id"><option value="">No customer assignment</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id',$profile->customerAccess->first()?->customer_id ?? '') == $customer->id)>{{ $customer->name }} — {{ $customer->email }}</option>@endforeach</select><small class="form-hint">The selected customer will be able to manage this profile from their dashboard.</small></label>
    <label class="wide">Public URL slug<input class="form-control" name="proposed_slug" value="{{ old('proposed_slug',$version->proposed_slug ?: $profile->slug) }}" placeholder="person-or-company-name" maxlength="190"></label>
    <label class="checkrow"><input type="hidden" name="is_verified" value="0"><input type="checkbox" name="is_verified" value="1" @checked(old('is_verified',$profile->is_verified ?? false))> Mark profile as verified</label>
    <label class="checkrow"><input type="hidden" name="publish_now" value="0"><input type="checkbox" name="publish_now" value="1" @checked(old('publish_now',$profile->status==='published'))> Publish this version immediately</label>
  </div>
  <hr>
  <div data-admin-type-fields="personal" @if(!$isPersonal) hidden @endif>
    <fieldset @disabled(!$isPersonal) style="border:0;padding:0;margin:0;min-width:0">
    <h2 class="settings-title"><i class="bi bi-person-vcard"></i> Personal profile details</h2>
    <div class="form-grid">
      <label>Full name *<input class="form-control" name="full_name" value="{{ old('full_name',$details->full_name ?? '') }}" maxlength="180"></label>
      <label>Professional headline<input class="form-control" name="headline" value="{{ old('headline',$details->headline ?? '') }}"></label>
      <label>Professional title<input class="form-control" name="professional_title" value="{{ old('professional_title',$details->professional_title ?? '') }}"></label>
      <label>Current position<input class="form-control" name="current_position" value="{{ old('current_position',$details->current_position ?? '') }}"></label>
      <label>Company / organisation<input class="form-control" name="company_organisation" value="{{ old('company_organisation',$details->company_organisation ?? '') }}"></label>
      <label>Industry<select class="form-select" name="industry_id"><option value="">Select industry</option>@foreach($industries as $industry)<option value="{{ $industry->id }}" @selected((string)old('industry_id',$details->industry_id ?? '') === (string)$industry->id)>{{ $industry->name }}</option>@endforeach</select></label>
      <label>Location<input class="form-control" name="location" value="{{ old('location',$details->location ?? '') }}"></label>
      <label>Hometown<input class="form-control" name="hometown" value="{{ old('hometown',$details->hometown ?? '') }}"></label>
      <label>Nationality<input class="form-control" name="nationality" value="{{ old('nationality',$details->nationality ?? '') }}"></label>
      <label>Date of birth<input class="form-control" type="date" name="date_of_birth" value="{{ old('date_of_birth',$details?->date_of_birth?->format('Y-m-d') ?? '') }}"></label>
      <label>Occupation<input class="form-control" name="occupation" value="{{ old('occupation',$details->occupation ?? '') }}"></label>
      <label>Public contact email<input class="form-control" type="email" name="public_contact_email" value="{{ old('public_contact_email',$details->public_contact_email ?? '') }}"></label>
      <label>Public phone<input class="form-control" name="public_phone" value="{{ old('public_phone',$details->public_phone ?? '') }}"></label>
      <label>Personal website<input class="form-control" type="url" name="personal_website" value="{{ old('personal_website',$details->personal_website ?? '') }}"></label>
      <label class="wide">Short bio<textarea class="form-control" name="short_bio" rows="2">{{ old('short_bio',$details->short_bio ?? '') }}</textarea></label>
      <label class="wide">About<textarea class="form-control" name="about" rows="4">{{ old('about',$details->about ?? '') }}</textarea></label>
      <label class="wide">Profile quote<textarea class="form-control" name="profile_quote" rows="2">{{ old('profile_quote',$details->profile_quote ?? '') }}</textarea></label>
      <label class="wide">Early life<textarea class="form-control" name="early_life" rows="3">{{ old('early_life',$details->early_life ?? '') }}</textarea></label>
      <label class="wide">Career & entrepreneurship<textarea class="form-control" name="career_entrepreneurship" rows="4">{{ old('career_entrepreneurship',$details->career_entrepreneurship ?? '') }}</textarea></label>
      <label class="wide">Education summary<textarea class="form-control" name="education_summary" rows="3">{{ old('education_summary',$details->education_summary ?? '') }}</textarea></label>
      <label class="wide">Awards, publications & recognition<textarea class="form-control" name="awards_publications_recognition" rows="3">{{ old('awards_publications_recognition',$details->awards_publications_recognition ?? '') }}</textarea></label>
      <label class="checkrow"><input type="hidden" name="show_public_email" value="0"><input type="checkbox" name="show_public_email" value="1" @checked(old('show_public_email',$details->show_public_email ?? false))> Show public email</label>
      <label class="checkrow"><input type="hidden" name="show_public_phone" value="0"><input type="checkbox" name="show_public_phone" value="1" @checked(old('show_public_phone',$details->show_public_phone ?? false))> Show public phone</label>
      <label class="checkrow"><input type="hidden" name="show_date_of_birth" value="0"><input type="checkbox" name="show_date_of_birth" value="1" @checked(old('show_date_of_birth',$details->show_date_of_birth ?? false))> Show date of birth</label>
      <label>Profile photo<input class="form-control" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp"></label>
    </div>
    </fieldset>
  </div>
  <div data-admin-type-fields="company" @if($isPersonal) hidden @endif>
    <fieldset @disabled($isPersonal) style="border:0;padding:0;margin:0;min-width:0">
    <h2 class="settings-title"><i class="bi bi-buildings"></i> Company profile details</h2>
    <div class="form-grid">
      <label>Company name *<input class="form-control" name="company_name" value="{{ old('company_name',$details->company_name ?? '') }}" maxlength="180"></label>
      <label>Tagline<input class="form-control" name="tagline" value="{{ old('tagline',$details->tagline ?? '') }}"></label>
      <label>Industry<select class="form-select" name="industry_id"><option value="">Select industry</option>@foreach($industries as $industry)<option value="{{ $industry->id }}" @selected((string)old('industry_id',$details->industry_id ?? '') === (string)$industry->id)>{{ $industry->name }}</option>@endforeach</select></label>
      <label>Founded year<input class="form-control" type="number" name="founded_year" min="1800" max="{{ now()->year }}" value="{{ old('founded_year',$details->founded_year ?? '') }}"></label>
      <label>Headquarters<input class="form-control" name="headquarters" value="{{ old('headquarters',$details->headquarters ?? '') }}"></label>
      <label>Company size<input class="form-control" name="company_size" value="{{ old('company_size',$details->company_size ?? '') }}"></label>
      <label>Business type<input class="form-control" name="business_type" value="{{ old('business_type',$details->business_type ?? '') }}"></label>
      <label>Area served<input class="form-control" name="area_served" value="{{ old('area_served',$details->area_served ?? '') }}"></label>
      <label>Official website<input class="form-control" type="url" name="official_website" value="{{ old('official_website',$details->official_website ?? '') }}"></label>
      <label>Public email<input class="form-control" type="email" name="public_email" value="{{ old('public_email',$details->public_email ?? '') }}"></label>
      <label>Public phone<input class="form-control" name="public_phone" value="{{ old('public_phone',$details->public_phone ?? '') }}"></label>
      <label>Parent company<input class="form-control" name="parent_company" value="{{ old('parent_company',$details->parent_company ?? '') }}"></label>
      <label class="wide">Short description<textarea class="form-control" name="short_description" rows="2">{{ old('short_description',$details->short_description ?? '') }}</textarea></label>
      <label class="wide">Overview<textarea class="form-control" name="overview" rows="4">{{ old('overview',$details->overview ?? '') }}</textarea></label>
      <label class="wide">History & background<textarea class="form-control" name="history_background" rows="3">{{ old('history_background',$details->history_background ?? '') }}</textarea></label>
      <label>Mission<textarea class="form-control" name="mission" rows="3">{{ old('mission',$details->mission ?? '') }}</textarea></label>
      <label>Vision<textarea class="form-control" name="vision" rows="3">{{ old('vision',$details->vision ?? '') }}</textarea></label>
      <div class="wide border rounded p-3">
        <h3 class="h6 mb-3">Know This Company? CTA</h3>
        <label class="checkrow"><input type="hidden" name="accuracy_cta_enabled" value="0"><input type="checkbox" name="accuracy_cta_enabled" value="1" @checked(old('accuracy_cta_enabled',$details->accuracy_cta_enabled ?? true))> Show company accuracy CTA</label>
        <label>CTA title<input class="form-control" name="accuracy_cta_title" value="{{ old('accuracy_cta_title',$details->accuracy_cta_title ?? 'Know This Company?') }}"></label>
        <label>CTA description<textarea class="form-control" name="accuracy_cta_description" rows="2">{{ old('accuracy_cta_description',$details->accuracy_cta_description ?? 'Help us keep this information accurate. You can suggest an edit or request a company profile.') }}</textarea></label>
        <label>Primary button label<input class="form-control" name="accuracy_cta_button_label" value="{{ old('accuracy_cta_button_label',$details->accuracy_cta_button_label ?? 'Suggest an Edit') }}"></label>
        <label>Primary button URL<input class="form-control" type="url" name="accuracy_cta_button_url" value="{{ old('accuracy_cta_button_url',$details->accuracy_cta_button_url ?? '') }}" placeholder="Leave blank for the default suggestion form"></label>
        <label>Secondary button label<input class="form-control" name="accuracy_cta_secondary_label" value="{{ old('accuracy_cta_secondary_label',$details->accuracy_cta_secondary_label ?? 'Request a Company Profile') }}"></label>
        <label>Secondary button URL<input class="form-control" type="url" name="accuracy_cta_secondary_url" value="{{ old('accuracy_cta_secondary_url',$details->accuracy_cta_secondary_url ?? '') }}" placeholder="Leave blank for customer registration"></label>
      </div>
      <label class="checkrow"><input type="hidden" name="show_public_email" value="0"><input type="checkbox" name="show_public_email" value="1" @checked(old('show_public_email',$details->show_public_email ?? false))> Show public email</label>
      <label class="checkrow"><input type="hidden" name="show_public_phone" value="0"><input type="checkbox" name="show_public_phone" value="1" @checked(old('show_public_phone',$details->show_public_phone ?? false))> Show public phone</label>
      <label>Company logo<input class="form-control" type="file" name="company_logo" accept="image/jpeg,image/png,image/webp"></label>
      <label>Company banner<input class="form-control" type="file" name="company_banner" accept="image/jpeg,image/png,image/webp"></label>
    </div>
    </fieldset>
  </div>
  <hr>
  <h2 class="settings-title"><i class="bi bi-share"></i> Profile links</h2>
  <div id="admin-profile-links">
    @foreach($formLinks as $index => $link)<div class="form-grid border rounded p-3 mb-2" data-admin-row><label>Type<input class="form-control" name="links[{{ $index }}][link_type]" value="{{ $link['link_type'] ?? 'website' }}"></label><label>Label<input class="form-control" name="links[{{ $index }}][label]" value="{{ $link['label'] ?? '' }}"></label><label class="wide">URL<input class="form-control" type="url" name="links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}"></label><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove>Remove link</button></div>@endforeach
  </div>
  <button type="button" class="btn btn-sm btn-outline-primary mb-3" data-admin-add="admin-profile-links" data-admin-template="admin-link-template">Add link</button>
  <template id="admin-link-template"><div class="form-grid border rounded p-3 mb-2" data-admin-row><label>Type<input class="form-control" name="links[__INDEX__][link_type]" value="website"></label><label>Label<input class="form-control" name="links[__INDEX__][label]"></label><label class="wide">URL<input class="form-control" type="url" name="links[__INDEX__][url]"></label><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove>Remove link</button></div></template>
  <h2 class="settings-title"><i class="bi bi-journal-check"></i> References</h2>
  <div id="admin-profile-references">
    @foreach($formReferences as $index => $reference)<div class="form-grid border rounded p-3 mb-2" data-admin-row><label>Title<input class="form-control" name="references[{{ $index }}][title]" value="{{ $reference['title'] ?? '' }}"></label><label>Publisher<input class="form-control" name="references[{{ $index }}][publisher]" value="{{ $reference['publisher'] ?? '' }}"></label><label class="wide">URL<input class="form-control" type="url" name="references[{{ $index }}][url]" value="{{ $reference['url'] ?? '' }}"></label><label>Date<input class="form-control" type="date" name="references[{{ $index }}][source_date]" value="{{ $reference['source_date'] ?? '' }}"></label><label class="wide">Description<textarea class="form-control" name="references[{{ $index }}][description]" rows="2">{{ $reference['description'] ?? '' }}</textarea></label><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove>Remove reference</button></div>@endforeach
  </div>
  <button type="button" class="btn btn-sm btn-outline-primary mb-3" data-admin-add="admin-profile-references" data-admin-template="admin-reference-template">Add reference</button>
  <template id="admin-reference-template"><div class="form-grid border rounded p-3 mb-2" data-admin-row><label>Title<input class="form-control" name="references[__INDEX__][title]"></label><label>Publisher<input class="form-control" name="references[__INDEX__][publisher]"></label><label class="wide">URL<input class="form-control" type="url" name="references[__INDEX__][url]"></label><label>Date<input class="form-control" type="date" name="references[__INDEX__][source_date]"></label><label class="wide">Description<textarea class="form-control" name="references[__INDEX__][description]" rows="2"></textarea></label><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove>Remove reference</button></div></template>
  <h2 class="settings-title"><i class="bi bi-layout-text-window-reverse"></i> Additional profile sections</h2>
  <div id="admin-profile-sections">
    @foreach($formSections as $sectionIndex => $section)
      <div class="border rounded p-3 mb-3" data-admin-section>
        <div class="form-grid">
          <label>Section title<input class="form-control" name="sections[{{ $sectionIndex }}][title]" value="{{ $section['title'] ?? '' }}"></label>
          <label>Section type<select class="form-select" name="sections[{{ $sectionIndex }}][section_type]"><option value="content" @selected(($section['section_type'] ?? 'content')==='content')>Content</option><option value="cards" @selected(($section['section_type'] ?? '')==='cards')>Cards</option><option value="list" @selected(($section['section_type'] ?? '')==='list')>List</option></select></label>
          <label>Layout<select class="form-select" name="sections[{{ $sectionIndex }}][layout]"><option value="default" @selected(($section['layout'] ?? 'default')==='default')>Default</option><option value="grid" @selected(($section['layout'] ?? '')==='grid')>Grid</option><option value="timeline" @selected(($section['layout'] ?? '')==='timeline')>Timeline</option></select></label>
          <label class="checkrow"><input type="hidden" name="sections[{{ $sectionIndex }}][is_visible]" value="0"><input type="checkbox" name="sections[{{ $sectionIndex }}][is_visible]" value="1" @checked($section['is_visible'] ?? true)> Show this section</label>
          <label class="wide">Introduction<textarea class="form-control" name="sections[{{ $sectionIndex }}][intro]" rows="2">{{ $section['intro'] ?? '' }}</textarea></label>
        </div>
        <div class="mt-2" data-admin-section-items>
          @foreach(($section['items'] ?? [['title'=>'','subtitle'=>'','description'=>'','icon'=>'','image_path'=>'','url'=>'','is_visible'=>true]]) as $itemIndex => $item)
            <div class="form-grid border rounded p-3 mb-2" data-admin-section-item>
              <label>Item title<input class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][title]" value="{{ $item['title'] ?? '' }}"></label>
              <label>Subtitle<input class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][subtitle]" value="{{ $item['subtitle'] ?? '' }}"></label>
              <label>Icon<input class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][icon]" value="{{ $item['icon'] ?? '' }}"></label>
              <label>URL<input class="form-control" type="url" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][url]" value="{{ $item['url'] ?? '' }}"></label>
              <label class="wide">Description<textarea class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][description]" rows="2">{{ $item['description'] ?? '' }}</textarea></label>
              <div class="wide">
                @if(!empty($item['image_path']))
                  <img src="{{ \Illuminate\Support\Str::startsWith($item['image_path'], ['http://','https://']) ? $item['image_path'] : asset('public/'.ltrim($item['image_path'],'/')) }}" alt="Section item image" style="max-width:140px;max-height:90px;object-fit:cover;border-radius:6px;margin-bottom:8px">
                @endif
                <input type="hidden" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][image_path]" value="{{ $item['image_path'] ?? '' }}">
                <label>Item image<input class="form-control" type="file" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][image]" accept="image/jpeg,image/png,image/webp"></label>
              </div>
              <label class="checkrow"><input type="hidden" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][is_visible]" value="0"><input type="checkbox" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][is_visible]" value="1" @checked($item['is_visible'] ?? true)> Show this item</label>
              <button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove-section-item>Remove item</button>
            </div>
          @endforeach
        </div>
        <div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-primary" data-admin-add-section-item>Add item</button><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove-section>Remove section</button></div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-sm btn-outline-primary mb-3" data-admin-add-section>Add profile section</button>
  <template id="admin-section-template"><div class="border rounded p-3 mb-3" data-admin-section><div class="form-grid"><label>Section title<input class="form-control" name="sections[__SECTION__][title]"></label><label>Section type<select class="form-select" name="sections[__SECTION__][section_type]"><option value="content">Content</option><option value="cards">Cards</option><option value="list">List</option></select></label><label>Layout<select class="form-select" name="sections[__SECTION__][layout]"><option value="default">Default</option><option value="grid">Grid</option><option value="timeline">Timeline</option></select></label><label class="checkrow"><input type="hidden" name="sections[__SECTION__][is_visible]" value="0"><input type="checkbox" name="sections[__SECTION__][is_visible]" value="1" checked> Show this section</label><label class="wide">Introduction<textarea class="form-control" name="sections[__SECTION__][intro]" rows="2"></textarea></label></div><div class="mt-2" data-admin-section-items><div class="form-grid border rounded p-3 mb-2" data-admin-section-item><label>Item title<input class="form-control" name="sections[__SECTION__][items][0][title]"></label><label>Subtitle<input class="form-control" name="sections[__SECTION__][items][0][subtitle]"></label><label>Icon<input class="form-control" name="sections[__SECTION__][items][0][icon]"></label><label>URL<input class="form-control" type="url" name="sections[__SECTION__][items][0][url]"></label><label class="wide">Description<textarea class="form-control" name="sections[__SECTION__][items][0][description]" rows="2"></textarea></label><div class="wide"><input type="hidden" name="sections[__SECTION__][items][0][image_path]" value=""><label>Item image<input class="form-control" type="file" name="sections[__SECTION__][items][0][image]" accept="image/jpeg,image/png,image/webp"></label></div><label class="checkrow"><input type="hidden" name="sections[__SECTION__][items][0][is_visible]" value="0"><input type="checkbox" name="sections[__SECTION__][items][0][is_visible]" value="1" checked> Show this item</label><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove-section-item>Remove item</button></div></div><div class="d-flex gap-2"><button type="button" class="btn btn-sm btn-outline-primary" data-admin-add-section-item>Add item</button><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove-section>Remove section</button></div></div></template>
  <template id="admin-section-item-template"><div class="form-grid border rounded p-3 mb-2" data-admin-section-item><label>Item title<input class="form-control" name="sections[__SECTION__][items][__ITEM__][title]"></label><label>Subtitle<input class="form-control" name="sections[__SECTION__][items][__ITEM__][subtitle]"></label><label>Icon<input class="form-control" name="sections[__SECTION__][items][__ITEM__][icon]"></label><label>URL<input class="form-control" type="url" name="sections[__SECTION__][items][__ITEM__][url]"></label><label class="wide">Description<textarea class="form-control" name="sections[__SECTION__][items][__ITEM__][description]" rows="2"></textarea></label><div class="wide"><input type="hidden" name="sections[__SECTION__][items][__ITEM__][image_path]" value=""><label>Item image<input class="form-control" type="file" name="sections[__SECTION__][items][__ITEM__][image]" accept="image/jpeg,image/png,image/webp"></label></div><label class="checkrow"><input type="hidden" name="sections[__SECTION__][items][__ITEM__][is_visible]" value="0"><input type="checkbox" name="sections[__SECTION__][items][__ITEM__][is_visible]" value="1" checked> Show this item</label><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove-section-item>Remove item</button></div></template>
  <h2 class="settings-title"><i class="bi bi-diagram-3"></i> Related profiles</h2>
  <div id="admin-profile-relationships">
    @foreach($formRelationships as $index => $relationship)
      <div class="form-grid border rounded p-3 mb-2" data-admin-row>
        <label>Related profile<select class="form-select" name="relationships[{{ $index }}][related_profile_id]"><option value="">Select a published profile</option>@foreach($relatedProfiles as $relatedProfile)@php($relatedDetails = $relatedProfile->profile_type==='personal' ? $relatedProfile->publishedVersion?->personalDetails : $relatedProfile->publishedVersion?->companyDetails)@php($relatedName = $relatedProfile->profile_type==='personal' ? ($relatedDetails?->full_name ?? $relatedProfile->slug) : ($relatedDetails?->company_name ?? $relatedProfile->slug))<option value="{{ $relatedProfile->id }}" @selected((string)($relationship['related_profile_id'] ?? '') === (string)$relatedProfile->id)>{{ $relatedName }} — {{ ucfirst($relatedProfile->profile_type) }}</option>@endforeach</select></label>
        <label>Relationship type<input class="form-control" name="relationships[{{ $index }}][relation_type]" value="{{ $relationship['relation_type'] ?? 'related' }}" placeholder="founder / company / investor"></label>
        <label>Position<input class="form-control" name="relationships[{{ $index }}][position]" value="{{ $relationship['position'] ?? '' }}"></label>
        <label>Description<input class="form-control" name="relationships[{{ $index }}][description]" value="{{ $relationship['description'] ?? '' }}"></label>
        <button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove>Remove relationship</button>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-sm btn-outline-primary mb-3" data-admin-add="admin-profile-relationships" data-admin-template="admin-relationship-template">Add related profile</button>
  <template id="admin-relationship-template"><div class="form-grid border rounded p-3 mb-2" data-admin-row><label>Related profile<select class="form-select" name="relationships[__INDEX__][related_profile_id]"><option value="">Select a published profile</option>@foreach($relatedProfiles as $relatedProfile)@php($relatedDetails = $relatedProfile->profile_type==='personal' ? $relatedProfile->publishedVersion?->personalDetails : $relatedProfile->publishedVersion?->companyDetails)@php($relatedName = $relatedProfile->profile_type==='personal' ? ($relatedDetails?->full_name ?? $relatedProfile->slug) : ($relatedDetails?->company_name ?? $relatedProfile->slug))<option value="{{ $relatedProfile->id }}">{{ $relatedName }} — {{ ucfirst($relatedProfile->profile_type) }}</option>@endforeach</select></label><label>Relationship type<input class="form-control" name="relationships[__INDEX__][relation_type]" value="related"></label><label>Position<input class="form-control" name="relationships[__INDEX__][position]"></label><label>Description<input class="form-control" name="relationships[__INDEX__][description]"></label><button type="button" class="btn btn-sm btn-outline-danger" data-admin-remove>Remove relationship</button></div></template>

  <h2 class="settings-title"><i class="bi bi-search"></i> Search engine settings</h2>
  <div class="form-grid">
    <label>Meta title<input class="form-control" name="meta_title" maxlength="255" value="{{ old('meta_title',$version->seo?->meta_title ?? '') }}"></label>
    <label>Meta description<textarea class="form-control" name="meta_description" rows="2" maxlength="500">{{ old('meta_description',$version->seo?->meta_description ?? '') }}</textarea></label>
    <label class="wide">Meta keywords<textarea class="form-control" name="meta_keywords" rows="2">{{ old('meta_keywords',$version->seo?->meta_keywords ?? '') }}</textarea></label>
    <label>Robots<select class="form-select" name="robots">@foreach(['index,follow','noindex,follow','index,nofollow','noindex,nofollow'] as $robots)<option value="{{ $robots }}" @selected(old('robots',$version->seo?->robots ?? 'index,follow')===$robots)>{{ $robots }}</option>@endforeach</select></label>
    <label>Canonical URL<input class="form-control" type="url" name="canonical_url" value="{{ old('canonical_url',$version->seo?->canonical_url ?? '') }}"></label>
    <label>Open Graph title<input class="form-control" name="og_title" value="{{ old('og_title',$version->seo?->og_title ?? '') }}"></label>
    <label>Open Graph description<textarea class="form-control" name="og_description" rows="2">{{ old('og_description',$version->seo?->og_description ?? '') }}</textarea></label>
    <label>Open Graph image URL<input class="form-control" type="url" name="og_image" value="{{ old('og_image',$version->seo?->og_image ?? '') }}"></label>
    <label>Twitter title<input class="form-control" name="twitter_title" value="{{ old('twitter_title',$version->seo?->twitter_title ?? '') }}"></label>
    <label>Twitter description<textarea class="form-control" name="twitter_description" rows="2">{{ old('twitter_description',$version->seo?->twitter_description ?? '') }}</textarea></label>
    <label>Twitter image URL<input class="form-control" type="url" name="twitter_image" value="{{ old('twitter_image',$version->seo?->twitter_image ?? '') }}"></label>
  </div>
  <label class="d-block mt-3">Change summary<textarea class="form-control" name="change_summary" rows="2">{{ old('change_summary',$version->change_summary ?? '') }}</textarea></label>
  <div class="form-actions"><button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> {{ old('publish_now',$profile->status==='published') ? 'Save and publish' : 'Save profile' }}</button><a class="module-secondary" href="{{ route('admin.profiles.index') }}">Cancel</a>@if($formMode==='edit')<a class="module-secondary" href="{{ route('admin.profiles.history',$profile) }}">View revision history</a>@endif</div>
</form>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const type=document.getElementById('adminProfileType');
 const fixedType=document.querySelector('[data-admin-type-fields]:not([hidden])')?.dataset.adminTypeFields||'personal';
 const toggle=()=>document.querySelectorAll('[data-admin-type-fields]').forEach(el=>{const inactive=el.dataset.adminTypeFields!==(type?.value||fixedType);el.hidden=inactive;const fieldset=el.querySelector('fieldset');if(fieldset)fieldset.disabled=inactive;});
 type?.addEventListener('change',toggle); toggle();
 let counter=Date.now(); const next=()=>counter++;
 const clone=(id,replacements={})=>{const template=document.getElementById(id);if(!template)return null;let html=template.innerHTML;Object.entries(replacements).forEach(([key,value])=>html=html.replaceAll(`__${key}__`,String(value)));const holder=document.createElement('div');holder.innerHTML=html.trim();return holder.firstElementChild;};
 document.addEventListener('click',e=>{
  const add=e.target.closest('[data-admin-add]'); if(add){const row=clone(add.dataset.adminTemplate,{INDEX:next()});if(row)document.getElementById(add.dataset.adminAdd).appendChild(row);return;}
  const remove=e.target.closest('[data-admin-remove]'); if(remove){remove.closest('[data-admin-row]')?.remove();return;}
  if(e.target.closest('[data-admin-add-section]')){const row=clone('admin-section-template',{SECTION:next()});if(row){row.dataset.sectionIndex=row.querySelector('[name^="sections["]')?.name.match(/^sections\[(\d+)\]/)?.[1]||'';document.getElementById('admin-profile-sections').appendChild(row);}return;}
  if(e.target.closest('[data-admin-remove-section]')){e.target.closest('[data-admin-section]')?.remove();return;}
  if(e.target.closest('[data-admin-add-section-item]')){const section=e.target.closest('[data-admin-section]');const container=section?.querySelector('[data-admin-section-items]');if(!container)return;const match=(section.querySelector('[name^="sections["]')?.name||'').match(/^sections\[(\d+)\]/);const sectionIndex=match?match[1]:next();const indexes=Array.from(container.querySelectorAll('[name*="[items]["]')).map(field=>Number((field.name.match(/\[items\]\[(\d+)\]/)||[])[1]||0));const itemIndex=indexes.length?Math.max(...indexes)+1:0;const row=clone('admin-section-item-template',{SECTION:sectionIndex,ITEM:itemIndex});if(row)container.appendChild(row);return;}
  if(e.target.closest('[data-admin-remove-section-item]')){e.target.closest('[data-admin-section-item]')?.remove();}
 });
});
</script>
@endsection
