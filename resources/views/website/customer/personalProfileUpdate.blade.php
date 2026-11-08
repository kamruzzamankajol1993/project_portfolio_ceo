@extends('website.master.master')

@section('title', ($profile ? 'Edit' : 'Create') . ' personal profile | ' . $siteSetting->site_name)

@section('body')
@php
    $person = $details;
    $portraitMedia = $version?->mediaLinks?->firstWhere('role', 'portrait')?->media;
    $formLinks = old('links', $version?->links?->map(fn($item) => $item->only(['link_type','label','url','icon']))->all() ?: [['link_type'=>'website','label'=>'','url'=>'','icon'=>'']]);
    $formReferences = old('references', $version?->references?->map(fn($item) => ['title'=>$item->title,'publisher'=>$item->publisher,'url'=>$item->url,'source_date'=>$item->source_date?->format('Y-m-d'),'description'=>$item->description])->all() ?: [['title'=>'','publisher'=>'','url'=>'','source_date'=>'','description'=>'']]);
    $systemSectionKeys = ['_editor_career_timeline','_editor_companies','_editor_education','_editor_achievements','_editor_media'];
    $formSections = old('sections', $version?->sections?->reject(fn($section) => in_array($section->section_key, $systemSectionKeys, true))->map(fn($section) => ['title'=>$section->title,'intro'=>$section->intro,'section_type'=>$section->section_type,'layout'=>$section->layout,'is_visible'=>$section->is_visible,'items'=>$section->items->map(fn($item) => $item->only(['title','subtitle','description','icon','image_path','url','is_visible']))->all()])->values()->all() ?: [['title'=>'','intro'=>'','section_type'=>'content','layout'=>'default','is_visible'=>true,'items'=>[['title'=>'','subtitle'=>'','description'=>'','icon'=>'','image_path'=>'','url'=>'','is_visible'=>true]]]]);
    if (empty($formLinks)) $formLinks = [['link_type'=>'website','label'=>'','url'=>'','icon'=>'']];
    if (empty($formReferences)) $formReferences = [['title'=>'','publisher'=>'','url'=>'','source_date'=>'','description'=>'']];
    if (empty($formSections)) $formSections = [['title'=>'','intro'=>'','section_type'=>'content','layout'=>'default','is_visible'=>true,'items'=>[['title'=>'','subtitle'=>'','description'=>'','icon'=>'','image_path'=>'','url'=>'','is_visible'=>true]]]];

    $careerRows = old('career_timeline', $person?->career_timeline ?? []);
    $affiliationRows = old('affiliations', $person?->affiliations ?? []);
    $educationRows = old('education_items', $person?->education_items ?? []);
    $achievementRows = old('achievement_items', $person?->achievement_items ?? []);
    $mediaRows = old('media_items', $person?->media_items ?? []);
    $relationshipRows = $formRelationships ?: [['related_profile_id'=>'','relation_type'=>'colleague','position'=>'','description'=>'']];
    foreach (['careerRows','affiliationRows','educationRows','achievementRows','mediaRows'] as $rowsName) {
        if (empty($$rowsName)) $$rowsName = [[]];
    }
    $seo = $version?->seo;
    $personName = $person?->full_name ?: $customer->name;
    $formAction = $profile ? route('customer.profiles.personal.update', $profile) : route('customer.profiles.personal.store');
    $completionChecks = [
        filled($person?->full_name),
        filled($person?->short_bio) || filled($person?->about),
        collect($careerRows)->contains(fn ($row) => filled($row['title'] ?? null)),
        collect($affiliationRows)->contains(fn ($row) => filled($row['name'] ?? null) || filled($row['company'] ?? null)),
        collect($educationRows)->contains(fn ($row) => filled($row['institution'] ?? null)),
        collect($achievementRows)->contains(fn ($row) => filled($row['title'] ?? null)),
        collect($mediaRows)->contains(fn ($row) => filled($row['title'] ?? null)),
        $version?->mediaLinks?->where('role', 'portrait')->isNotEmpty() ?? false,
        $version?->links?->isNotEmpty() ?? false,
        filled($seo?->meta_title) || filled($seo?->meta_description),
    ];
    $completionCount = collect($completionChecks)->filter()->count();
    $completionPercent = (int) round(($completionCount / max(count($completionChecks), 1)) * 100);
@endphp

<main class="account-wrap profile-editor-wrap">
    <div class="account-heading">
        <div>
            <div class="breadcrumb-line"><a href="{{ route('site.home') }}">Home</a><i class="bi bi-chevron-right"></i><a href="{{ route('customer.dashboard') }}">Dashboard</a><i class="bi bi-chevron-right"></i><strong>Personal profile</strong></div>
            <span class="auth-eyebrow">YOUR PUBLIC PRESENCE</span>
            <h1>{{ $profile ? 'Edit personal profile' : 'Create personal profile' }}</h1>
            <p>Update your professional story, career, education, achievements and public profile sections.</p>
        </div>
        <span class="profile-status {{ $lockedForReview ? '' : 'status-draft' }}"><i class="bi bi-{{ $lockedForReview ? 'hourglass-split' : 'pencil-square' }}"></i> {{ $lockedForReview ? 'Under review' : 'Editing draft' }}</span>
    </div>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($lockedForReview)<div class="customer-review-lock"><i class="bi bi-hourglass-split"></i> This version is waiting for editorial review. Editing is temporarily locked until the review is complete.</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="account-layout editor-layout-wide">
        <aside class="account-sidebar editor-sticky-sidebar">
            <div class="account-user-card">
                @if($portraitMedia)<img src="{{ $portraitMedia->url }}" alt="{{ $personName }}">@else<span class="profile-list-icon"><i class="bi bi-person-fill"></i></span>@endif
                <div><strong>{{ $customer->name }}</strong><small>Profile owner</small></div>
            </div>
            <span class="account-nav-label">PERSONAL PROFILE</span>
            <nav class="account-nav section-jump-nav">
                <a class="active" href="#person-identity"><i class="bi bi-person-badge"></i> Profile identity</a>
                <a href="#person-information"><i class="bi bi-person-lines-fill"></i> Personal information</a>
                <a href="#person-biography"><i class="bi bi-file-richtext"></i> Biography &amp; story</a>
                <a href="#person-career"><i class="bi bi-briefcase"></i> Career timeline</a>
                <a href="#person-companies"><i class="bi bi-buildings"></i> Companies</a>
                <a href="#person-education"><i class="bi bi-mortarboard"></i> Education</a>
                <a href="#person-achievements"><i class="bi bi-trophy"></i> Achievements</a>
                <a href="#person-media"><i class="bi bi-play-btn"></i> Media &amp; interviews</a>
                <a href="#person-links"><i class="bi bi-link-45deg"></i> Links &amp; references</a>
                <a href="#person-related"><i class="bi bi-diagram-3"></i> Related profiles</a>
                <a href="#person-custom"><i class="bi bi-layout-text-window-reverse"></i> Custom sections</a>
                <a href="#person-seo"><i class="bi bi-search"></i> SEO &amp; visibility</a>
            </nav>
            <div class="account-sidebar-note"><i class="bi bi-shield-check"></i><strong>Editorial approval required.</strong><p>Your submitted changes remain private until approved by the {{ $siteSetting->site_name }} editorial team.</p></div>
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
                    <section class="editor-card" id="person-identity">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-person-badge"></i></span><div><h2>Profile identity</h2><p>Your name, headline and profile image appear at the top of your public page.</p></div></div><span class="section-step">01</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field field-span-2"><label for="fullName">Full name <b>*</b></label><input class="form-control" id="fullName" name="full_name" value="{{ old('full_name',$person?->full_name) }}" maxlength="180" required>@error('full_name')<small class="field-hint text-danger">{{ $message }}</small>@enderror</div>
                            <div class="form-field"><label for="profileSlug">Profile URL slug</label><input class="form-control" id="profileSlug" name="proposed_slug" value="{{ old('proposed_slug',$version?->proposed_slug ?: $profile?->slug) }}" placeholder="your-name"></div>
                            <div class="form-field"><label for="professionalHeadline">Professional headline</label><input class="form-control" id="professionalHeadline" name="headline" value="{{ old('headline',$person?->headline) }}" maxlength="255" placeholder="Founder, CEO, business leader..."></div>
                            <div class="form-field"><label for="professionalTitle">Professional title</label><input class="form-control" id="professionalTitle" name="professional_title" value="{{ old('professional_title',$person?->professional_title) }}" maxlength="180"></div>
                            <div class="form-field"><label for="currentPosition">Current position</label><input class="form-control" id="currentPosition" name="current_position" value="{{ old('current_position',$person?->current_position) }}" maxlength="180"></div>
                            <div class="form-field field-span-2">
                                <label>Profile portrait</label>
                                <div class="upload-tile person-photo-tile">
                                    @if($portraitMedia)
                                        <img id="personPortraitPreview" src="{{ $portraitMedia->url }}" alt="Current profile portrait">
                                    @else
                                        <img id="personPortraitPreview" src="{{ asset('public/website/assets/images/person-placeholder.svg') }}" alt="Portrait preview">
                                    @endif
                                    <div>
                                        <strong>Upload a professional portrait</strong>
                                        <small>JPG, PNG or WebP · Maximum 5 MB</small>
                                        <label class="btn btn-outline-primary btn-sm mb-0" for="profilePhoto"><i class="bi bi-upload"></i> Choose photo</label>
                                        <input class="visually-hidden" id="profilePhoto" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp" data-preview-target="personPortraitPreview">
                                        @error('profile_photo')<small class="field-hint text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="editor-card" id="person-information">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-person-lines-fill"></i></span><div><h2>Personal information</h2><p>Basic facts and public contact details.</p></div></div><span class="section-step">02</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field"><label for="companyOrganisation">Company / organisation</label><input class="form-control" id="companyOrganisation" name="company_organisation" value="{{ old('company_organisation',$person?->company_organisation) }}"></div>
                            <div class="form-field"><label for="personLocation">Location</label><input class="form-control" id="personLocation" name="location" value="{{ old('location',$person?->location) }}" placeholder="City, Country"></div>
                            <div class="form-field"><label for="personIndustry">Industry</label><select class="form-select" id="personIndustry" name="industry_id"><option value="">Select industry</option>@foreach($industries as $industry)<option value="{{ $industry->id }}" @selected((string)old('industry_id',$person?->industry_id)===(string)$industry->id)>{{ $industry->name }}</option>@endforeach</select></div>
                            <div class="form-field"><label for="personHometown">Hometown</label><input class="form-control" id="personHometown" name="hometown" value="{{ old('hometown',$person?->hometown) }}"></div>
                            <div class="form-field"><label for="personNationality">Nationality</label><input class="form-control" id="personNationality" name="nationality" value="{{ old('nationality',$person?->nationality) }}"></div>
                            <div class="form-field"><label for="personDob">Date of birth</label><input class="form-control" id="personDob" name="date_of_birth" type="date" value="{{ old('date_of_birth',$person?->date_of_birth?->format('Y-m-d')) }}"></div>
                            <div class="form-field field-span-2"><label for="personOccupation">Occupation</label><input class="form-control" id="personOccupation" name="occupation" value="{{ old('occupation',$person?->occupation) }}" placeholder="Entrepreneur, Digital Marketer, Content Creator"></div>
                            <div class="form-field"><label for="personWebsite">Personal website</label><input class="form-control" id="personWebsite" name="personal_website" type="url" value="{{ old('personal_website',$person?->personal_website) }}" placeholder="https://"></div>
                            <div class="form-field"><label for="personEmail">Public contact email</label><input class="form-control" id="personEmail" name="public_contact_email" type="email" value="{{ old('public_contact_email',$person?->public_contact_email) }}" placeholder="hello@example.com"></div>
                            <div class="form-field"><label for="personPhone">Public phone</label><input class="form-control" id="personPhone" name="public_phone" type="tel" value="{{ old('public_phone',$person?->public_phone) }}" placeholder="+880 ..."></div>
                        </div>
                        <div class="visibility-row">
                            <strong>Public visibility</strong>
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="show_public_email" value="1" @checked(old('show_public_email',$person?->show_public_email))><span>Show email</span></label>
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="show_public_phone" value="1" @checked(old('show_public_phone',$person?->show_public_phone))><span>Show phone</span></label>
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="show_date_of_birth" value="1" @checked(old('show_date_of_birth',$person?->show_date_of_birth))><span>Show date of birth</span></label>
                        </div>
                    </section>

                    <section class="editor-card" id="person-biography">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-file-richtext"></i></span><div><h2>Biography &amp; professional story</h2><p>Help readers understand your background and professional journey.</p></div></div><span class="section-step">03</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field field-span-2"><label for="personShortBio">Short biography</label><textarea class="form-control" id="personShortBio" name="short_bio" rows="3" placeholder="A concise introduction shown near the top of your profile...">{{ old('short_bio',$person?->short_bio) }}</textarea></div>
                            <div class="form-field field-span-2"><label for="personAbout">About / professional journey</label><textarea class="form-control" id="personAbout" name="about" rows="7" placeholder="Write a detailed professional biography...">{{ old('about',$person?->about) }}</textarea></div>
                            <div class="form-field field-span-2"><label for="personQuote">Profile quote</label><textarea class="form-control" id="personQuote" name="profile_quote" rows="2" placeholder="A quote that represents your approach...">{{ old('profile_quote',$person?->profile_quote) }}</textarea></div>
                            <div class="form-field field-span-2"><label for="personEarlyLife">Early life &amp; background</label><textarea class="form-control" id="personEarlyLife" name="early_life" rows="4" placeholder="Background, influences and early interests...">{{ old('early_life',$person?->early_life) }}</textarea></div>
                        </div>
                    </section>

                    <section class="editor-card" id="person-career">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-briefcase"></i></span><div><h2>Career timeline</h2><p>Add the important stages, roles and milestones in your career.</p></div></div><span class="section-step">04</span></div>
                        <div class="repeat-list" id="person-career-rows">
                            @foreach($careerRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Career milestone {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Year / period</label><input class="form-control" name="career_timeline[{{ $i }}][year]" value="{{ $row['year'] ?? '' }}" placeholder="2024"></div>
                                    <div class="form-field"><label>Role / milestone title</label><input class="form-control" name="career_timeline[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="Acting CEO"></div>
                                    <div class="form-field"><label>Organisation</label><input class="form-control" name="career_timeline[{{ $i }}][organization]" value="{{ $row['organization'] ?? '' }}" placeholder="Company name"></div>
                                    <div class="form-field"><label>Location</label><input class="form-control" name="career_timeline[{{ $i }}][location]" value="{{ $row['location'] ?? '' }}" placeholder="City, Country"></div>
                                    <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="career_timeline[{{ $i }}][description]" rows="2" placeholder="Responsibilities, outcomes and impact...">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-career-rows"><i class="bi bi-plus-lg"></i> Add career milestone</button>
                        <div class="form-field mt-3"><label for="careerEntrepreneurship">Career &amp; entrepreneurship summary</label><textarea class="form-control" id="careerEntrepreneurship" name="career_entrepreneurship" rows="3">{{ old('career_entrepreneurship',$person?->career_entrepreneurship) }}</textarea></div>
                    </section>

                    <section class="editor-card" id="person-companies">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-buildings"></i></span><div><h2>Companies &amp; affiliations</h2><p>List organisations where you have worked or held a leadership role.</p></div></div><span class="section-step">05</span></div>
                        <div class="repeat-list" id="person-affiliation-rows">
                            @foreach($affiliationRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Affiliation {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Company / organisation</label><input class="form-control" name="affiliations[{{ $i }}][company]" value="{{ $row['company'] ?? '' }}"></div>
                                    <div class="form-field"><label>Position / relationship</label><input class="form-control" name="affiliations[{{ $i }}][position]" value="{{ $row['position'] ?? '' }}"></div>
                                    <div class="form-field"><label>From – To</label><input class="form-control" name="affiliations[{{ $i }}][period]" value="{{ $row['period'] ?? '' }}" placeholder="2021 – Present"></div>
                                    <div class="form-field"><label>Company profile URL</label><input class="form-control" type="url" name="affiliations[{{ $i }}][company_url]" value="{{ $row['company_url'] ?? '' }}" placeholder="https://..."></div>
                                    <div class="form-field"><label>Company logo URL (optional)</label><input class="form-control" type="url" name="affiliations[{{ $i }}][logo_url]" value="{{ $row['logo_url'] ?? '' }}" placeholder="https://..."></div>
                                    <div class="form-field field-span-2"><label>Details</label><textarea class="form-control" name="affiliations[{{ $i }}][description]" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-affiliation-rows"><i class="bi bi-plus-lg"></i> Add company / affiliation</button>
                    </section>

                    <section class="editor-card" id="person-education">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-mortarboard"></i></span><div><h2>Education &amp; qualifications</h2><p>Add degrees, institutions and professional training.</p></div></div><span class="section-step">06</span></div>
                        <div class="repeat-list" id="person-education-rows">
                            @foreach($educationRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Education {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field field-span-2"><label>Institution</label><input class="form-control" name="education_items[{{ $i }}][institution]" value="{{ $row['institution'] ?? '' }}"></div>
                                    <div class="form-field"><label>Degree / qualification</label><input class="form-control" name="education_items[{{ $i }}][degree]" value="{{ $row['degree'] ?? '' }}" placeholder="Degree or certification"></div>
                                    <div class="form-field"><label>Field of study</label><input class="form-control" name="education_items[{{ $i }}][field]" value="{{ $row['field'] ?? '' }}" placeholder="Technology / Business"></div>
                                    <div class="form-field"><label>Year</label><input class="form-control" name="education_items[{{ $i }}][year]" value="{{ $row['year'] ?? '' }}" placeholder="2018 – 2022"></div>
                                    <div class="form-field field-span-2"><label>Institution image URL (optional)</label><input class="form-control" type="url" name="education_items[{{ $i }}][image_url]" value="{{ $row['image_url'] ?? '' }}" placeholder="https://..."></div>
                                    <div class="form-field field-span-2"><label>Details</label><textarea class="form-control" name="education_items[{{ $i }}][details]" rows="2">{{ $row['details'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-education-rows"><i class="bi bi-plus-lg"></i> Add education</button>
                        <div class="form-field mt-3"><label for="educationSummary">Additional education summary</label><textarea class="form-control" id="educationSummary" name="education_summary" rows="2">{{ old('education_summary',$person?->education_summary) }}</textarea></div>
                    </section>

                    <section class="editor-card" id="person-achievements">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-trophy"></i></span><div><h2>Awards, publications &amp; recognition</h2><p>Share verifiable awards, publications and contributions.</p></div></div><span class="section-step">07</span></div>
                        <div class="repeat-list" id="person-achievement-rows">
                            @foreach($achievementRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Achievement {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field field-span-2"><label>Achievement title</label><input class="form-control" name="achievement_items[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="Award / contribution / publication"></div>
                                    <div class="form-field"><label>Issuer / publisher</label><input class="form-control" name="achievement_items[{{ $i }}][issuer]" value="{{ $row['issuer'] ?? '' }}" placeholder="Organisation"></div>
                                    <div class="form-field"><label>Icon class (optional)</label><input class="form-control" name="achievement_items[{{ $i }}][icon]" value="{{ $row['icon'] ?? '' }}" placeholder="bi-graph-up-arrow"></div>
                                    <div class="form-field"><label>Year</label><input class="form-control" name="achievement_items[{{ $i }}][year]" value="{{ $row['year'] ?? '' }}" placeholder="2024"></div>
                                    <div class="form-field field-span-2"><label>Reference URL</label><input class="form-control" type="url" name="achievement_items[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="https://"></div>
                                    <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="achievement_items[{{ $i }}][description]" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-achievement-rows"><i class="bi bi-plus-lg"></i> Add achievement</button>
                        <div class="form-field mt-3"><label for="awardsSummary">Additional recognition summary</label><textarea class="form-control" id="awardsSummary" name="awards_publications_recognition" rows="3">{{ old('awards_publications_recognition',$person?->awards_publications_recognition) }}</textarea></div>
                    </section>

                    <section class="editor-card" id="person-media">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-play-btn"></i></span><div><h2>Media, interviews &amp; gallery</h2><p>Add interviews, articles and profile images.</p></div></div><span class="section-step">08</span></div>
                        <div class="form-field mb-3"><label for="personGallery">Additional images</label><input class="form-control" id="personGallery" name="gallery[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple><small class="field-hint">You can select multiple JPG, PNG or WebP images (maximum 5 MB each).</small></div>
                        @if($version?->mediaLinks?->where('role','gallery')->isNotEmpty())
                            <div class="current-gallery mb-3">@foreach($version->mediaLinks->where('role','gallery') as $gallery)<img class="profile-current-media" src="{{ $gallery->media?->url }}" alt="{{ $gallery->media?->alt_text }}">@endforeach</div>
                        @endif
                        <div class="repeat-list" id="person-media-rows">
                            @foreach($mediaRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Media item {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field field-span-2"><label>Media / interview title</label><input class="form-control" name="media_items[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="Interview or article title"></div>
                                    <div class="form-field"><label>Publisher / channel</label><input class="form-control" name="media_items[{{ $i }}][publisher]" value="{{ $row['publisher'] ?? '' }}" placeholder="YouTube / publication"></div>
                                    <div class="form-field"><label>Date</label><input class="form-control" type="date" name="media_items[{{ $i }}][date]" value="{{ $row['date'] ?? '' }}"></div>
                                    <div class="form-field field-span-2"><label>Media URL</label><input class="form-control" type="url" name="media_items[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="https://"></div>
                                    <div class="form-field field-span-2"><label>Thumbnail image URL (optional)</label><input class="form-control" type="url" name="media_items[{{ $i }}][thumbnail_url]" value="{{ $row['thumbnail_url'] ?? '' }}" placeholder="https://..."></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-media-rows"><i class="bi bi-plus-lg"></i> Add media item</button>
                    </section>

                    <section class="editor-card" id="person-links">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-link-45deg"></i></span><div><h2>Official links &amp; references</h2><p>Provide official links and sources that support the profile information.</p></div></div><span class="section-step">09</span></div>
                        <h3 class="editor-subheading">Official links</h3>
                        <div class="repeat-list" id="person-link-rows">
                            @foreach($formLinks as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Link {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Link type</label><select class="form-select" name="links[{{ $i }}][link_type]">@foreach(['website'=>'Website','linkedin'=>'LinkedIn','facebook'=>'Facebook','instagram'=>'Instagram','youtube'=>'YouTube','x'=>'X / Twitter','other'=>'Other'] as $value=>$label)<option value="{{ $value }}" @selected(($row['link_type'] ?? 'website')===$value)>{{ $label }}</option>@endforeach</select></div>
                                    <div class="form-field"><label>Link label</label><input class="form-control" name="links[{{ $i }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="LinkedIn profile"></div>
                                    <div class="form-field field-span-2"><label>URL</label><input class="form-control" type="url" name="links[{{ $i }}][url]" value="{{ $row['url'] ?? '' }}" placeholder="https://"></div>
                                    <input type="hidden" name="links[{{ $i }}][icon]" value="{{ $row['icon'] ?? '' }}">
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-link-rows"><i class="bi bi-plus-lg"></i> Add official link</button>

                        <h3 class="editor-subheading mt-4">References &amp; sources</h3>
                        <div class="repeat-list" id="person-reference-rows">
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
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-reference-rows"><i class="bi bi-plus-lg"></i> Add reference</button>
                    </section>

                    <section class="editor-card" id="person-related">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-diagram-3"></i></span><div><h2>Related people &amp; organisations</h2><p>Connect this profile with other published profiles in the directory.</p></div></div><span class="section-step">10</span></div>
                        <div class="repeat-list" id="person-related-rows">
                            @foreach($relationshipRows as $i => $row)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Related profile {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove row"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field field-span-2"><label>Related profile</label><select class="form-select" name="relationships[{{ $i }}][related_profile_id]"><option value="">Select a published profile</option>@foreach($relatedProfiles as $relatedProfile)@php($relatedName = $relatedProfile->profile_type === 'personal' ? ($relatedProfile->publishedVersion?->personalDetails?->full_name ?: $relatedProfile->slug) : ($relatedProfile->publishedVersion?->companyDetails?->company_name ?: $relatedProfile->slug))<option value="{{ $relatedProfile->id }}" @selected((string)($row['related_profile_id'] ?? '')===(string)$relatedProfile->id)>{{ $relatedProfile->profile_type === 'personal' ? 'Person' : 'Company' }} · {{ $relatedName }}</option>@endforeach</select></div>
                                    <div class="form-field"><label>Relationship type</label><select class="form-select" name="relationships[{{ $i }}][relation_type]">@foreach(['founder'=>'Founder','co_founder'=>'Co-founder','employer'=>'Employer','employee'=>'Employee','colleague'=>'Colleague','partner'=>'Partner','related'=>'Related'] as $value=>$label)<option value="{{ $value }}" @selected(($row['relation_type'] ?? 'colleague')===$value)>{{ $label }}</option>@endforeach</select></div>
                                    <div class="form-field"><label>Position / relationship</label><input class="form-control" name="relationships[{{ $i }}][position]" value="{{ $row['position'] ?? '' }}"></div>
                                    <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="relationships[{{ $i }}][description]" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-related-rows"><i class="bi bi-plus-lg"></i> Add related profile</button>
                    </section>

                    <section class="editor-card" id="person-custom">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-layout-text-window-reverse"></i></span><div><h2>Additional custom sections</h2><p>Create extra sections for information that does not fit the standard profile layout.</p></div></div><span class="section-step">11</span></div>
                        <div class="repeat-list" id="person-custom-rows">
                            @foreach($formSections as $i => $section)
                            <div class="repeat-row" data-repeat-row>
                                <div class="repeat-row-heading"><strong>Custom section {{ $i+1 }}</strong><button type="button" class="repeat-remove" data-remove-row aria-label="Remove section"><i class="bi bi-trash"></i></button></div>
                                <div class="editor-form-grid">
                                    <div class="form-field"><label>Section title</label><input class="form-control" name="sections[{{ $i }}][title]" value="{{ $section['title'] ?? '' }}" placeholder="e.g. Community initiatives"></div>
                                    <div class="form-field"><label>Section layout</label><select class="form-select" name="sections[{{ $i }}][layout]">@foreach(['default'=>'Default','grid'=>'Grid','timeline'=>'Timeline','list'=>'List'] as $value=>$label)<option value="{{ $value }}" @selected(($section['layout'] ?? 'default')===$value)>{{ $label }}</option>@endforeach</select></div>
                                    <div class="form-field field-span-2"><label>Section introduction</label><textarea class="form-control" name="sections[{{ $i }}][intro]" rows="2">{{ $section['intro'] ?? '' }}</textarea></div>
                                    <input type="hidden" name="sections[{{ $i }}][section_type]" value="{{ $section['section_type'] ?? 'content' }}">
                                    <input type="hidden" name="sections[{{ $i }}][is_visible]" value="0"><label class="check-label"><input type="checkbox" name="sections[{{ $i }}][is_visible]" value="1" @checked($section['is_visible'] ?? true)><span>Show this section publicly</span></label>
                                </div>
                                @php($sectionItems = $section['items'] ?? [[]])
                                @if(empty($sectionItems))@php($sectionItems=[[]])@endif
                                <div class="repeat-list nested-repeat-list mt-3" id="person-custom-items-{{ $i }}" data-index-depth="1">
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
                                <button type="button" class="btn btn-light btn-sm mt-2" data-add-row="person-custom-items-{{ $i }}"><i class="bi bi-plus-lg"></i> Add content item</button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="person-custom-rows"><i class="bi bi-plus-lg"></i> Add custom section</button>
                    </section>

                    <section class="editor-card" id="person-seo">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-search"></i></span><div><h2>SEO &amp; profile visibility</h2><p>Help search engines and social networks understand your profile.</p></div></div><span class="section-step">12</span></div>
                        <div class="editor-form-grid">
                            <div class="form-field field-span-2"><label for="personMetaTitle">SEO title</label><input class="form-control" id="personMetaTitle" name="meta_title" value="{{ old('meta_title',$seo?->meta_title) }}" placeholder="Name | {{ $siteSetting->site_name }}"></div>
                            <div class="form-field field-span-2"><label for="personCanonical">Canonical URL</label><input class="form-control" id="personCanonical" name="canonical_url" type="url" value="{{ old('canonical_url',$seo?->canonical_url) }}" placeholder="https://"></div>
                            <div class="form-field field-span-2"><label for="personMetaDescription">Meta description</label><textarea class="form-control" id="personMetaDescription" name="meta_description" rows="3" placeholder="Short search description...">{{ old('meta_description',$seo?->meta_description) }}</textarea></div>
                            <div class="form-field"><label for="personMetaKeywords">Meta keywords</label><input class="form-control" id="personMetaKeywords" name="meta_keywords" value="{{ old('meta_keywords',$seo?->meta_keywords) }}"></div>
                            <div class="form-field"><label for="personRobots">Search visibility</label><select class="form-select" id="personRobots" name="robots">@foreach(['index,follow'=>'Index and follow','noindex,follow'=>'No index, follow','index,nofollow'=>'Index, no follow','noindex,nofollow'=>'No index, no follow'] as $value=>$label)<option value="{{ $value }}" @selected(($seo?->robots ?: 'index,follow')===$value)>{{ $label }}</option>@endforeach</select></div>
                            <div class="form-field"><label for="personOgTitle">Social title</label><input class="form-control" id="personOgTitle" name="og_title" value="{{ old('og_title',$seo?->og_title) }}"></div>
                            <div class="form-field"><label for="personOgImage">Social image URL</label><input class="form-control" id="personOgImage" name="og_image" type="url" value="{{ old('og_image',$seo?->og_image) }}"></div>
                            <div class="form-field field-span-2"><label for="personOgDescription">Social description</label><textarea class="form-control" id="personOgDescription" name="og_description" rows="2">{{ old('og_description',$seo?->og_description) }}</textarea></div>
                            <div class="form-field"><label for="personTwitterTitle">Twitter title</label><input class="form-control" id="personTwitterTitle" name="twitter_title" value="{{ old('twitter_title',$seo?->twitter_title) }}"></div>
                            <div class="form-field"><label for="personTwitterImage">Twitter image URL</label><input class="form-control" id="personTwitterImage" name="twitter_image" type="url" value="{{ old('twitter_image',$seo?->twitter_image) }}"></div>
                            <div class="form-field field-span-2"><label for="personTwitterDescription">Twitter description</label><textarea class="form-control" id="personTwitterDescription" name="twitter_description" rows="2">{{ old('twitter_description',$seo?->twitter_description) }}</textarea></div>
                        </div>
                    </section>

                    <section class="editor-card">
                        <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-pencil-square"></i></span><div><h2>Change summary</h2><p>Tell the editorial team what you have added or changed.</p></div></div></div>
                        <div class="form-field"><label for="personChangeSummary">Summary of updates</label><textarea class="form-control" id="personChangeSummary" name="change_summary" rows="3" placeholder="Briefly describe the updates you made...">{{ old('change_summary',$version?->change_summary) }}</textarea></div>
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
