@extends('website.master.master')

@section('title', ($profile ? 'Edit' : 'Create') . ' company profile | ' . $siteSetting->site_name)

@section('body')
@php
    $company = $details;
    $logoMedia = $version?->mediaLinks?->firstWhere('role', 'logo')?->media;
    $bannerMedia = $version?->mediaLinks?->firstWhere('role', 'banner')?->media;
    $formLinks = old('links', $version?->links?->map(fn($item) => $item->only(['link_type','label','url','icon']))->all() ?: [['link_type'=>'website','label'=>'','url'=>'','icon'=>'']]);
    $formReferences = old('references', $version?->references?->map(fn($item) => ['title'=>$item->title,'publisher'=>$item->publisher,'url'=>$item->url,'source_date'=>$item->source_date?->format('Y-m-d'),'description'=>$item->description])->all() ?: [['title'=>'','publisher'=>'','url'=>'','source_date'=>'','description'=>'']]);
    $systemSectionKeys = ['_editor_overview_highlights','_editor_leadership','_editor_services','_editor_achievements','_editor_media'];
    $formSections = old('sections', $version?->sections?->reject(fn($section) => in_array($section->section_key, $systemSectionKeys, true))->map(fn($section) => ['title'=>$section->title,'intro'=>$section->intro,'section_type'=>$section->section_type,'layout'=>$section->layout,'is_visible'=>$section->is_visible,'items'=>$section->items->map(fn($item) => $item->only(['title','subtitle','description','icon','image_path','url','is_visible']))->all()])->values()->all() ?: [['title'=>'','intro'=>'','section_type'=>'content','layout'=>'default','is_visible'=>true,'items'=>[['title'=>'','subtitle'=>'','description'=>'','icon'=>'','image_path'=>'','url'=>'','is_visible'=>true]]]]);
    if (empty($formLinks)) $formLinks = [['link_type'=>'website','label'=>'','url'=>'','icon'=>'']];
    if (empty($formReferences)) $formReferences = [['title'=>'','publisher'=>'','url'=>'','source_date'=>'','description'=>'']];
    if (empty($formSections)) $formSections = [['title'=>'','intro'=>'','section_type'=>'content','layout'=>'default','is_visible'=>true,'items'=>[['title'=>'','subtitle'=>'','description'=>'','icon'=>'','image_path'=>'','url'=>'','is_visible'=>true]]]];

    $leadershipRows = old('leadership_items', $company?->leadership_items ?? []);
    $serviceRows = old('service_items', $company?->service_items ?? []);
    $milestoneRows = old('milestone_items', $company?->milestone_items ?? []);
    $mediaRows = old('media_items', $company?->media_items ?? []);
    $highlightRows = old('highlights', $company?->highlights ?? []);
    $relationshipRows = $formRelationships ?: [['related_profile_id'=>'','relation_type'=>'leader','position'=>'','description'=>'']];
    foreach (['leadershipRows','serviceRows','milestoneRows','mediaRows','highlightRows'] as $rowsName) {
        if (empty($$rowsName)) $$rowsName = [[]];
    }
    $seo = $version?->seo;
    $companyName = $company?->company_name ?: '';
    $formAction = $profile ? route('customer.profiles.company.update', $profile) : route('customer.profiles.company.store');
    $completionChecks = [
        filled($company?->company_name),
        filled($company?->short_description) || filled($company?->overview),
        filled($company?->industry_id) || filled($company?->headquarters),
        collect($leadershipRows)->contains(fn ($row) => filled($row['name'] ?? null)),
        collect($serviceRows)->contains(fn ($row) => filled($row['title'] ?? null)),
        collect($milestoneRows)->contains(fn ($row) => filled($row['title'] ?? null)),
        collect($mediaRows)->contains(fn ($row) => filled($row['title'] ?? null)),
        $version?->mediaLinks?->where('role', 'logo')->isNotEmpty() ?? false,
        $version?->links?->isNotEmpty() ?? false,
        filled($seo?->meta_title) || filled($seo?->meta_description),
    ];
    $completionCount = collect($completionChecks)->filter()->count();
    $completionPercent = (int) round(($completionCount / max(count($completionChecks), 1)) * 100);
@endphp

<main class="account-wrap profile-editor-wrap">
    <div class="account-heading">
        <div>
            <div class="breadcrumb-line"><a href="{{ route('site.home') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('customer.dashboard') }}">Dashboard</a><i class="bi bi-chevron-right"></i><strong>Company profile</strong></div>
            <span class="auth-eyebrow">PUBLIC COMPANY PRESENCE</span>
            <h1>{{ $profile ? 'Edit company profile' : 'Create company profile' }}</h1>
            <p>Manage every section of your company profile. Your changes remain a draft until submitted for editorial review.</p>
        </div>
        <span class="profile-status {{ $lockedForReview ? '' : 'status-draft' }}"><i class="bi bi-{{ $lockedForReview ? 'hourglass-split' : 'pencil-square' }}"></i> {{ $lockedForReview ? 'Under review' : 'Editing draft' }}</span>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($lockedForReview)<div class="customer-review-lock"><i class="bi bi-hourglass-split"></i> This version is waiting for editorial review. Editing is temporarily locked until the review is complete.</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="account-layout editor-layout-wide">
        <aside class="account-sidebar editor-sticky-sidebar">
            <div class="account-user-card">
                @if($logoMedia)<img src="{{ $logoMedia->url }}" alt="{{ $companyName }}">@else<span class="profile-list-icon company"><i class="bi bi-buildings"></i></span>@endif
                <div><strong>{{ $customer->name }}</strong><small>Profile owner</small></div>
            </div>
            <span class="account-nav-label">COMPANY PROFILE</span>
            <nav class="account-nav section-jump-nav">
                <a class="active" href="#company-identity"><i class="bi bi-buildings"></i> Company identity</a>
                <a href="#company-information"><i class="bi bi-card-text"></i> Company information</a>
                <a href="#company-overview"><i class="bi bi-file-richtext"></i> Overview &amp; story</a>
                <a href="#company-leadership"><i class="bi bi-people"></i> Leadership team</a>
                <a href="#company-services"><i class="bi bi-grid"></i> Products &amp; services</a>
                <a href="#company-achievements"><i class="bi bi-trophy"></i> Achievements</a>
                <a href="#company-media"><i class="bi bi-images"></i> Media &amp; news</a>
                <a href="#company-related"><i class="bi bi-diagram-3"></i> Related profiles</a>
                <a href="#company-links"><i class="bi bi-link-45deg"></i> Links &amp; references</a>
                <a href="#company-custom"><i class="bi bi-layout-text-window-reverse"></i> Custom sections</a>
                <a href="#company-seo"><i class="bi bi-search"></i> SEO &amp; visibility</a>
            </nav>
            <div class="account-sidebar-note"><i class="bi bi-shield-check"></i><strong>Editorial approval required.</strong><p>Submitted changes are reviewed before they appear on the public company profile.</p></div>
            <a class="account-signout" href="{{ route('customer.dashboard') }}"><i class="bi bi-arrow-left"></i> Back to dashboard</a>
        </aside>

        <section class="account-main editor-main-column">
            <div class="editor-progress" aria-label="Profile completion">
                <div><strong>Profile completion</strong><span>{{ $completionCount }} of {{ count($completionChecks) }} sections started · {{ $completionPercent }}%</span></div>
                <div class="progress"><div class="progress-bar" role="progressbar" style="width: {{ $completionPercent }}%" aria-valuenow="{{ $completionPercent }}" aria-valuemin="0" aria-valuemax="100"></div></div>
            </div>
            <form class="account-form editor-form profile-editor-form" action="{{ $formAction }}" method="post" enctype="multipart/form-data" data-server-form>
                @csrf
                @if($profile)@method('PUT')@endif
                <input type="hidden" name="submit_action" value="{{ old('submit_action','draft') }}" data-submit-action-value>
                <fieldset @disabled($lockedForReview) class="profile-editor-fieldset">
                    <section class="editor-card" id="company-identity">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-buildings"></i></span><div><h2>Company identity</h2><p>The company name, tagline and visual identity shown on your public profile.</p></div></div><span class="section-step">01</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field field-span-2"><label for="companyName">Company name <b>*</b></label><input class="form-control" id="companyName" name="company_name" value="{{ old('company_name',$company?->company_name) }}" maxlength="180" required>@error('company_name')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                            <div class="form-field"><label for="companySlug">Profile URL slug</label><input class="form-control" id="companySlug" name="proposed_slug" value="{{ old('proposed_slug',$version?->proposed_slug ?: $profile?->slug) }}" placeholder="company-name"></div>
                            <div class="form-field"><label for="companyTagline">Tagline</label><input class="form-control" id="companyTagline" name="tagline" value="{{ old('tagline',$company?->tagline) }}" placeholder="A short company tagline"></div>
                            <div class="form-field field-span-2">
                                <label>Company logo and cover image</label>
                                <div class="upload-pair">
                                    <div class="upload-tile">
                                        @if($logoMedia)<img id="companyLogoPreview" src="{{ $logoMedia->url }}" alt="Current company logo">@else<img id="companyLogoPreview" src="{{ asset('public/website/assets/images/brand-mark-reference.png') }}" alt="Company logo preview">@endif
                                        <div>
                                            <strong>Company logo</strong>
                                            <small>Square image · JPG, PNG or WebP · 5 MB max</small>
                                            <label class="btn btn-outline-primary btn-sm mb-0" for="companyLogo"><i class="bi bi-upload"></i> Choose logo</label>
                                            <input class="visually-hidden" id="companyLogo" name="company_logo" type="file" accept=".jpg,.jpeg,.png,.webp" data-preview-target="companyLogoPreview">
                                            @error('company_logo')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                    <div class="upload-tile">
                                        <div class="banner-preview" id="companyBannerPreview" @if($bannerMedia) style="background-image:linear-gradient(110deg,rgba(4,20,45,.25),rgba(4,20,45,.08)),url('{{ $bannerMedia->url }}');background-size:cover;background-position:center" @endif><span>Cover image preview</span></div>
                                        <div>
                                            <strong>Cover image</strong>
                                            <small>Wide image · JPG, PNG or WebP · 8 MB max</small>
                                            <label class="btn btn-outline-primary btn-sm mb-0" for="companyBanner"><i class="bi bi-upload"></i> Choose cover</label>
                                            <input class="visually-hidden" id="companyBanner" name="company_banner" type="file" accept=".jpg,.jpeg,.png,.webp" data-preview-background="companyBannerPreview">
                                            @error('company_banner')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="editor-card" id="company-information">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-card-text"></i></span><div><h2>Company information</h2><p>Official details, industry, location and contact information.</p></div></div><span class="section-step">02</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field"><label for="companyIndustry">Industry</label><select class="form-select" id="companyIndustry" name="industry_id"><option value="">Select industry</option>@foreach($industries as $industry)<option value="{{ $industry->id }}" @selected((string)old('industry_id',$company?->industry_id)===(string)$industry->id)>{{ $industry->name }}</option>@endforeach</select></div>
                            <div class="form-field"><label for="foundedYear">Year founded</label><input class="form-control" id="foundedYear" name="founded_year" type="number" min="1800" max="{{ now()->year }}" value="{{ old('founded_year',$company?->founded_year) }}"></div>
                            <div class="form-field"><label for="headquarters">Headquarters / Location</label><input class="form-control" id="headquarters" name="headquarters" value="{{ old('headquarters',$company?->headquarters) }}" placeholder="City, Country"></div>
                            <div class="form-field"><label for="companySize">Company size</label><select class="form-select" id="companySize" name="company_size"><option value="">Select company size</option>@foreach(['1–10 employees','11–50 employees','51–200 employees','201–500 employees','501–1,000 employees','1,001+ employees'] as $size)<option value="{{ $size }}" @selected(old('company_size',$company?->company_size)===$size)>{{ $size }}</option>@endforeach</select></div>
                            <div class="form-field"><label for="businessType">Business type</label><input class="form-control" id="businessType" name="business_type" value="{{ old('business_type',$company?->business_type) }}" placeholder="Private Company, Startup, Non-profit..."></div>
                            <div class="form-field"><label for="areaServed">Area served</label><input class="form-control" id="areaServed" name="area_served" value="{{ old('area_served',$company?->area_served) }}" placeholder="Bangladesh, Global"></div>
                            <div class="form-field"><label for="officialWebsite">Official website</label><input class="form-control" id="officialWebsite" name="official_website" type="url" value="{{ old('official_website',$company?->official_website) }}" placeholder="https://"></div>
                            <div class="form-field"><label for="parentCompany">Parent company</label><input class="form-control" id="parentCompany" name="parent_company" value="{{ old('parent_company',$company?->parent_company) }}" placeholder="Optional"></div>
                            <div class="form-field"><label for="companyEmail">Public email</label><input class="form-control" id="companyEmail" name="public_email" type="email" value="{{ old('public_email',$company?->public_email) }}" placeholder="hello@company.com"></div>
                            <div class="form-field"><label for="companyPhone">Public phone</label><input class="form-control" id="companyPhone" name="public_phone" type="tel" value="{{ old('public_phone',$company?->public_phone) }}" placeholder="+880 ..."></div>
                        </div>
                        <div class="visibility-row">
                            <strong>Public visibility</strong>
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="show_public_email" value="1" @checked(old('show_public_email',$company?->show_public_email))><span>Show email</span></label>
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="show_public_phone" value="1" @checked(old('show_public_phone',$company?->show_public_phone))><span>Show phone</span></label>
                        </div>
                    </section>

                    <section class="editor-card" id="company-overview">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-file-richtext"></i></span><div><h2>Overview &amp; company story</h2><p>Describe what the company does, its purpose and its values.</p></div></div><span class="section-step">03</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field field-span-2"><label for="companyShortDescription">Short description</label><textarea class="form-control" id="companyShortDescription" name="short_description" rows="3">{{ old('short_description',$company?->short_description) }}</textarea></div>
                            <div class="form-field field-span-2"><label for="companyOverview">Company overview</label><textarea class="form-control" id="companyOverview" name="overview" rows="6" placeholder="Describe the company, its services, customers and impact...">{{ old('overview',$company?->overview) }}</textarea></div>
                            <div class="form-field field-span-2"><label for="companyHistory">History &amp; background</label><textarea class="form-control" id="companyHistory" name="history_background" rows="4" placeholder="Company origins, key turning points...">{{ old('history_background',$company?->history_background) }}</textarea></div>
                            <div class="form-field"><label for="companyMission">Mission</label><textarea class="form-control" id="companyMission" name="mission" rows="3" placeholder="What is the company's mission?">{{ old('mission',$company?->mission) }}</textarea></div>
                            <div class="form-field"><label for="companyVision">Vision</label><textarea class="form-control" id="companyVision" name="vision" rows="3" placeholder="What future does the company aim to create?">{{ old('vision',$company?->vision) }}</textarea></div>
                            <div class="form-field field-span-2"><label for="companyValues">Values &amp; culture</label><textarea class="form-control" id="companyValues" name="values_culture" rows="3" placeholder="Core values and working culture...">{{ old('values_culture',$company?->values_culture) }}</textarea></div>
                        </div>
                        <h3 class="editor-subheading mt-4">Company highlights</h3>
                        <div class="repeat-list" id="company-highlight-rows">
                            @foreach($highlightRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Highlight {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Highlight title</label><input class="form-control" name="highlights[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}"></div>
                                    <div class="form-field"><label>Icon class</label><input class="form-control" name="highlights[{{ $i }}][icon]" value="{{ $row['icon'] ?? '' }}" placeholder="bi-stars"></div>
                                    <div class="form-field field-span-2"><label>Description</label><input class="form-control" name="highlights[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-highlight-rows"><i class="bi bi-plus-lg"></i> Add highlight</button>
                    </section>

                    <section class="editor-card" id="company-leadership">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-people"></i></span><div><h2>Leadership team</h2><p>Add founders, directors and key members of the leadership team.</p></div></div><span class="section-step">04</span></div>
                        <div class="repeat-list" id="company-leadership-rows">
                            @foreach($leadershipRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Leader {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Person name</label><input class="form-control" name="leadership_items[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}"></div>
                                    <div class="form-field"><label>Position / role</label><input class="form-control" name="leadership_items[{{ $i }}][position]" value="{{ $row['position'] ?? '' }}"></div>
                                    <div class="form-field field-span-2"><label>Profile URL (optional)</label><input class="form-control" type="url" name="leadership_items[{{ $i }}][profile_url]" value="{{ $row['profile_url'] ?? '' }}" placeholder="https://..."></div>
                                    <div class="form-field field-span-2"><label>Leader photo URL (optional)</label><input class="form-control" type="url" name="leadership_items[{{ $i }}][photo_url]" value="{{ $row['photo_url'] ?? '' }}" placeholder="https://..."></div>
                                    <div class="form-field field-span-2"><label>Short bio</label><textarea class="form-control" name="leadership_items[{{ $i }}][bio]" rows="2" placeholder="A short introduction">{{ $row['bio'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-leadership-rows"><i class="bi bi-plus-lg"></i> Add leader</button>
                    </section>

                    <section class="editor-card" id="company-services">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-grid"></i></span><div><h2>Products &amp; services</h2><p>Describe the products and services offered by the company.</p></div></div><span class="section-step">05</span></div>
                        <div class="repeat-list" id="company-service-rows">
                            @foreach($serviceRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Service {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Service / product title</label><input class="form-control" name="service_items[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="SEO Services"></div>
                                    <div class="form-field"><label>Icon class</label><input class="form-control" name="service_items[{{ $i }}][icon]" value="{{ $row['icon'] ?? '' }}" placeholder="bi-graph-up-arrow"></div>
                                    <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="service_items[{{ $i }}][description]" rows="2" placeholder="Describe this service...">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-service-rows"><i class="bi bi-plus-lg"></i> Add product / service</button>
                    </section>

                    <section class="editor-card" id="company-achievements">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-trophy"></i></span><div><h2>Achievements &amp; milestones</h2><p>Highlight meaningful growth, awards and company milestones.</p></div></div><span class="section-step">06</span></div>
                        <div class="repeat-list" id="company-milestone-rows">
                            @foreach($milestoneRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Milestone {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Milestone / achievement</label><input class="form-control" name="milestone_items[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="e.g. Reached 100 global clients"></div>
                                    <div class="form-field"><label>Year / date</label><input class="form-control" name="milestone_items[{{ $i }}][year]" value="{{ $row['year'] ?? '' }}" placeholder="2024"></div>
                                    <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="milestone_items[{{ $i }}][description]" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-milestone-rows"><i class="bi bi-plus-lg"></i> Add milestone</button>
                    </section>

                    <section class="editor-card" id="company-media">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-images"></i></span><div><h2>Media, news &amp; additional images</h2><p>Upload company photos and add media coverage or articles.</p></div></div><span class="section-step">07</span></div>
                        <div class="form-field mb-3"><label for="companyGallery">Gallery images</label><input class="form-control" id="companyGallery" name="gallery[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple><small class="field-hint">Select multiple JPG, PNG or WebP images (maximum 5 MB each).</small></div>
                        @if($version?->mediaLinks?->where('role','gallery')->isNotEmpty())
                            <div class="current-gallery mb-3">@foreach($version->mediaLinks->where('role','gallery') as $gallery)<img class="profile-current-media" src="{{ $gallery->media?->url }}" alt="{{ $gallery->media?->alt_text }}">@endforeach</div>
                        @endif
                        <div class="repeat-list" id="company-media-rows">
                            @foreach($mediaRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Media item {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field field-span-2"><label>Article / media title</label><input class="form-control" name="media_items[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="News headline"></div>
                                    <div class="form-field"><label>Publisher</label><input class="form-control" name="media_items[{{ $i }}][publisher]" value="{{ $row['publisher'] ?? '' }}" placeholder="Publication name"></div>
                                    <div class="form-field"><label>Publication date</label><input class="form-control" type="date" name="media_items[{{ $i }}][date]" value="{{ $row['date'] ?? '' }}"></div>
                                    <div class="form-field field-span-2"><label>Article URL</label><input class="form-control" type="url" name="media_items[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="https://"></div>
                                    <div class="form-field field-span-2"><label>Thumbnail image URL (optional)</label><input class="form-control" type="url" name="media_items[{{ $i }}][thumbnail_url]" value="{{ $row['thumbnail_url'] ?? '' }}" placeholder="https://..."></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-media-rows"><i class="bi bi-plus-lg"></i> Add media item</button>
                    </section>

                    <section class="editor-card" id="company-related">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-diagram-3"></i></span><div><h2>Related people &amp; companies</h2><p>Connect your company with published people and organisations.</p></div></div><span class="section-step">08</span></div>
                        <div class="repeat-list" id="company-related-rows">
                            @foreach($relationshipRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Related profile {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field field-span-2"><label>Related person / company</label><select class="form-select" name="relationships[{{ $i }}][related_profile_id]"><option value="">Select a published profile</option>@foreach($relatedProfiles as $relatedProfile)@php($relatedName = $relatedProfile->profile_type === 'personal' ? ($relatedProfile->publishedVersion?->personalDetails?->full_name ?: $relatedProfile->slug) : ($relatedProfile->publishedVersion?->companyDetails?->company_name ?: $relatedProfile->slug))<option value="{{ $relatedProfile->id }}" @selected((string)($row['related_profile_id'] ?? '')===(string)$relatedProfile->id)>{{ $relatedProfile->profile_type === 'personal' ? 'Person' : 'Company' }} · {{ $relatedName }}</option>@endforeach</select></div>
                                    <div class="form-field"><label>Relationship type</label><select class="form-select" name="relationships[{{ $i }}][relation_type]">@foreach(['founder'=>'Founder','co_founder'=>'Co-founder','ceo'=>'CEO','director'=>'Director','subsidiary'=>'Subsidiary','partner'=>'Partner','related'=>'Related'] as $value=>$label)<option value="{{ $value }}" @selected(($row['relation_type'] ?? 'leader')===$value)>{{ $label }}</option>@endforeach</select></div>
                                    <div class="form-field"><label>Position / relationship</label><input class="form-control" name="relationships[{{ $i }}][position]" value="{{ $row['position'] ?? '' }}"></div>
                                    <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="relationships[{{ $i }}][description]" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-related-rows"><i class="bi bi-plus-lg"></i> Add related profile</button>
                    </section>

                    <section class="editor-card" id="company-links">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-link-45deg"></i></span><div><h2>Official links &amp; references</h2><p>Provide official links and sources that support the company information.</p></div></div><span class="section-step">09</span></div>
                        <h3 class="editor-subheading">Official links</h3>
                        <div class="repeat-list" id="company-link-rows">
                            @foreach($formLinks as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Link {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Link type</label><select class="form-select" name="links[{{ $i }}][link_type]">@foreach(['website'=>'Website','linkedin'=>'LinkedIn','facebook'=>'Facebook','instagram'=>'Instagram','youtube'=>'YouTube','x'=>'X / Twitter','other'=>'Other'] as $value=>$label)<option value="{{ $value }}" @selected(($row['link_type'] ?? 'website')===$value)>{{ $label }}</option>@endforeach</select></div>
                                    <div class="form-field"><label>Link label</label><input class="form-control" name="links[{{ $i }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="Official website"></div>
                                    <div class="form-field field-span-2"><label>URL</label><input class="form-control" type="url" name="links[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="https://"></div>
                                    <input type="hidden" name="links[{{ $i }}][icon]" value="{{ $row['icon'] ?? '' }}">
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-link-rows"><i class="bi bi-plus-lg"></i> Add official link</button>

                        <h3 class="editor-subheading mt-4">References &amp; sources</h3>
                        <div class="repeat-list" id="company-reference-rows">
                            @foreach($formReferences as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Reference {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field field-span-2"><label>Reference title</label><input class="form-control" name="references[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="Article or source title"></div>
                                    <div class="form-field"><label>Publisher</label><input class="form-control" name="references[{{ $i }}][publisher]" value="{{ $row['publisher'] ?? '' }}" placeholder="Publisher / organisation"></div>
                                    <div class="form-field"><label>Source date</label><input class="form-control" type="date" name="references[{{ $i }}][source_date]" value="{{ $row['source_date'] ?? '' }}"></div>
                                    <div class="form-field field-span-2"><label>Source URL</label><input class="form-control" type="url" name="references[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="https://"></div>
                                    <div class="form-field field-span-2"><label>Source note</label><textarea class="form-control" name="references[{{ $i }}][description]" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-reference-rows"><i class="bi bi-plus-lg"></i> Add reference</button>
                    </section>

                    <section class="editor-card" id="company-custom">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-layout-text-window-reverse"></i></span><div><h2>Additional custom sections</h2><p>Create extra sections for company information not covered above.</p></div></div><span class="section-step">10</span></div>
                        <div class="repeat-list" id="company-custom-rows">
                            @foreach($formSections as $i => $section)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Custom section {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove section"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Section title</label><input class="form-control" name="sections[{{ $i }}][title]" value="{{ $section['title'] ?? '' }}" placeholder="e.g. Sustainability initiatives"></div>
                                    <div class="form-field"><label>Section layout</label><select class="form-select" name="sections[{{ $i }}][layout]">@foreach(['default'=>'Default','grid'=>'Grid','timeline'=>'Timeline','list'=>'List'] as $value=>$label)<option value="{{ $value }}" @selected(($section['layout'] ?? 'default')===$value)>{{ $label }}</option>@endforeach</select></div>
                                    <div class="form-field field-span-2"><label>Section introduction</label><textarea class="form-control" name="sections[{{ $i }}][intro]" rows="2">{{ $section['intro'] ?? '' }}</textarea></div>
                                    <input type="hidden" name="sections[{{ $i }}][section_type]" value="{{ $section['section_type'] ?? 'content' }}">
                                    <input type="hidden" name="sections[{{ $i }}][is_visible]" value="0"><label class="check-label"><input type="checkbox" name="sections[{{ $i }}][is_visible]" value="1" @checked($section['is_visible'] ?? true)><span>Show this section publicly</span></label>
                                </div>
                                @php($sectionItems = $section['items'] ?? [[]])
                                @if(empty($sectionItems))@php($sectionItems=[[]])@endif
                                <div class="repeat-list nested-repeat-list mt-3" id="company-custom-items-{{ $i }}" data-index-depth="1">
                                    @foreach($sectionItems as $j => $item)
                                    <div class="repeat-row" data-repeat-row>
                                        <div class="repeat-row-heading"><strong>Content item {{ $j+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove item"><i class="bi bi-trash"></i></button></div>
                                        <div class="editor-form-grid">
                                            <div class="form-field"><label>Content item title</label><input class="form-control" name="sections[{{ $i }}][items][{{ $j }}][title]" value="{{ $item['title'] ?? '' }}" placeholder="Item title"></div>
                                            <div class="form-field"><label>Subtitle / date</label><input class="form-control" name="sections[{{ $i }}][items][{{ $j }}][subtitle]" value="{{ $item['subtitle'] ?? '' }}" placeholder="Optional"></div>
                                            <div class="form-field field-span-2"><label>Content / description</label><textarea class="form-control" name="sections[{{ $i }}][items][{{ $j }}][description]" rows="2">{{ $item['description'] ?? '' }}</textarea></div>
                                            <div class="form-field"><label>Optional image</label><input class="form-control" type="file" name="sections[{{ $i }}][items][{{ $j }}][image]" accept=".jpg,.jpeg,.png,.webp"></div>
                                            <div class="form-field"><label>Optional URL</label><input class="form-control" type="url" name="sections[{{ $i }}][items][{{ $j }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="https://"></div>
                                            <input type="hidden" name="sections[{{ $i }}][items][{{ $j }}][image_path]" value="{{ $item['image_path'] ?? '' }}" data-clear-on-clone>
                                            <input type="hidden" name="sections[{{ $i }}][items][{{ $j }}][icon]" value="{{ $item['icon'] ?? '' }}">
                                            <input type="hidden" name="sections[{{ $i }}][items][{{ $j }}][is_visible]" value="{{ ($item['is_visible'] ?? true) ? '1' : '0' }}">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-light btn-sm mt-2" data-add-row="company-custom-items-{{ $i }}"><i class="bi bi-plus-lg"></i> Add content item</button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="company-custom-rows"><i class="bi bi-plus-lg"></i> Add custom section</button>
                    </section>

                    <section class="editor-card" id="company-seo">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-search"></i></span><div><h2>SEO &amp; public visibility</h2><p>Manage search and social sharing information for this company profile.</p></div></div><span class="section-step">11</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field field-span-2"><label for="companyMetaTitle">SEO title</label><input class="form-control" id="companyMetaTitle" name="meta_title" value="{{ old('meta_title',$seo?->meta_title) }}" placeholder="Company name | {{ $siteSetting->site_name }}"></div>
                            <div class="form-field field-span-2"><label for="companyCanonical">Canonical URL (optional)</label><input class="form-control" id="companyCanonical" name="canonical_url" type="url" value="{{ old('canonical_url',$seo?->canonical_url) }}" placeholder="https://"></div>
                            <div class="form-field field-span-2"><label for="companyMetaDescription">Meta description</label><textarea class="form-control" id="companyMetaDescription" name="meta_description" rows="3" placeholder="Short search description...">{{ old('meta_description',$seo?->meta_description) }}</textarea></div>
                            <div class="form-field"><label for="companyMetaKeywords">Meta keywords</label><input class="form-control" id="companyMetaKeywords" name="meta_keywords" value="{{ old('meta_keywords',$seo?->meta_keywords) }}"></div>
                            <div class="form-field"><label for="companyRobots">Search visibility</label><select class="form-select" id="companyRobots" name="robots">@foreach(['index,follow'=>'Index and follow','noindex,follow'=>'No index, follow','index,nofollow'=>'Index, no follow','noindex,nofollow'=>'No index, no follow'] as $value=>$label)<option value="{{ $value }}" @selected(($seo?->robots ?: 'index,follow')===$value)>{{ $label }}</option>@endforeach</select></div>
                            <div class="form-field"><label for="companyOgTitle">Social title</label><input class="form-control" id="companyOgTitle" name="og_title" value="{{ old('og_title',$seo?->og_title) }}"></div>
                            <div class="form-field"><label for="companyOgImage">Social image URL</label><input class="form-control" id="companyOgImage" name="og_image" type="url" value="{{ old('og_image',$seo?->og_image) }}"></div>
                            <div class="form-field field-span-2"><label for="companyOgDescription">Social description</label><textarea class="form-control" id="companyOgDescription" name="og_description" rows="2">{{ old('og_description',$seo?->og_description) }}</textarea></div>
                            <div class="form-field"><label for="companyTwitterTitle">Twitter title</label><input class="form-control" id="companyTwitterTitle" name="twitter_title" value="{{ old('twitter_title',$seo?->twitter_title) }}"></div>
                            <div class="form-field"><label for="companyTwitterImage">Twitter image URL</label><input class="form-control" id="companyTwitterImage" name="twitter_image" type="url" value="{{ old('twitter_image',$seo?->twitter_image) }}"></div>
                            <div class="form-field field-span-2"><label for="companyTwitterDescription">Twitter description</label><textarea class="form-control" id="companyTwitterDescription" name="twitter_description" rows="2">{{ old('twitter_description',$seo?->twitter_description) }}</textarea></div>
                        </div>

                        <h3 class="editor-subheading mt-4">Company profile update request card</h3>
                        <label class="check-label mb-2"><input type="checkbox" name="accuracy_cta_enabled" value="1" @checked(old('accuracy_cta_enabled',$company?->accuracy_cta_enabled ?? true))><span>Show the public “Know this company?” card</span></label>
                        <div class="editor-form-grid">
                            <div class="form-field"><label>CTA title</label><input class="form-control" name="accuracy_cta_title" value="{{ old('accuracy_cta_title',$company?->accuracy_cta_title) }}" placeholder="Know this company?"></div>
                            <div class="form-field"><label>CTA description</label><input class="form-control" name="accuracy_cta_description" value="{{ old('accuracy_cta_description',$company?->accuracy_cta_description) }}" placeholder="Request an update to this company profile"></div>
                            <div class="form-field"><label>Primary button label</label><input class="form-control" name="accuracy_cta_button_label" value="{{ old('accuracy_cta_button_label',$company?->accuracy_cta_button_label) }}" placeholder="Visit website"></div>
                            <div class="form-field"><label>Primary button URL</label><input class="form-control" type="url" name="accuracy_cta_button_url" value="{{ old('accuracy_cta_button_url',$company?->accuracy_cta_button_url) }}" placeholder="https://"></div>
                            <div class="form-field"><label>Secondary button label</label><input class="form-control" name="accuracy_cta_secondary_label" value="{{ old('accuracy_cta_secondary_label',$company?->accuracy_cta_secondary_label) }}" placeholder="Suggest an edit"></div>
                            <div class="form-field"><label>Secondary button URL</label><input class="form-control" type="url" name="accuracy_cta_secondary_url" value="{{ old('accuracy_cta_secondary_url',$company?->accuracy_cta_secondary_url) }}" placeholder="https://"></div>
                        </div>
                    </section>

                    <section class="editor-card">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-pencil-square"></i></span><div><h2>Change summary</h2><p>Tell the editorial team what you have added or changed.</p></div></div></div>
                        <div class="form-field"><label for="companyChangeSummary">Summary of updates</label><textarea class="form-control" id="companyChangeSummary" name="change_summary" rows="3" placeholder="Briefly describe the updates you made...">{{ old('change_summary',$version?->change_summary) }}</textarea></div>
                    </section>
                </fieldset>

                <div class="editor-form-actions sticky-form-actions">
                    <div class="save-feedback"><i class="bi bi-shield-check"></i> Your changes remain private until approved.</div>
                    <div class="action-buttons">
                        <a class="btn btn-light" href="{{ route('customer.dashboard') }}">Cancel</a>
                        <button class="btn btn-outline-primary" type="submit" data-submit-action="draft" @disabled($lockedForReview)>Save Draft</button>
                        <button class="btn btn-primary" type="submit" data-submit-action="submit" @disabled($lockedForReview)>Submit for Review <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>
@endsection
