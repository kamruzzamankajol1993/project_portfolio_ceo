@extends('website.master.master')

@section('title')
    {{ $version->seo?->meta_title ?: (($details->full_name ?: $profile->slug) . ' | ' . $siteSetting->site_name) }}
@endsection

@section('body_class', 'person-page')

@section('body')
@php
    $personName = $details->full_name ?: $profile->slug;
    $portrait = $version->mediaLinks->firstWhere('role', 'portrait')?->media?->url
        ?: asset('public/website/assets/images/person-placeholder.svg');
    $companyRelationships = $version->relationships->filter(fn ($item) => $item->is_visible && $item->relatedProfile?->profile_type === 'company');
    $peopleRelationships = $version->relationships->filter(fn ($item) => $item->is_visible && $item->relatedProfile?->profile_type === 'personal');
    $timelineItems = collect($details->career_timeline ?? [])->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['year'] ?? null));
    $affiliations = collect($details->affiliations ?? [])->filter(fn ($item) => filled($item['company'] ?? null) || filled($item['position'] ?? null));
    $educationItems = collect($details->education_items ?? [])->filter(fn ($item) => filled($item['institution'] ?? null) || filled($item['degree'] ?? null));
    $achievementItems = collect($details->achievement_items ?? [])->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['description'] ?? null));
    $mediaItems = collect($details->media_items ?? [])->filter(fn ($item) => filled($item['title'] ?? null));
    $galleryItems = $version->mediaLinks->where('role', 'gallery');
    $tags = collect(preg_split('/[,|]/', (string) ($details->occupation ?: $details->professional_title ?: 'Entrepreneur')))
        ->map(fn ($tag) => trim($tag))->filter()->unique()->take(4);
    $primaryWebsite = $details->personal_website ?: $version->links->firstWhere('link_type', 'website')?->url;
    $customSections = $version->sections->filter(fn ($section) => $section->is_visible && !str_starts_with($section->section_key, '_editor_'));
    $legacyCareerSection = $customSections->firstWhere('section_key', 'career_entrepreneurship');
    $legacyImpactSection = $customSections->firstWhere('section_key', 'impact');
    $legacyImpactItems = $legacyImpactSection?->items?->where('is_visible', true)->values() ?? collect();
    $personAchievementItems = $achievementItems->isNotEmpty()
        ? $achievementItems
        : $legacyImpactItems->map(fn ($item) => [
            'title' => $item->title,
            'issuer' => $item->subtitle,
            'year' => null,
            'description' => $item->description,
            'url' => $item->url,
            'icon' => $item->icon,
        ]);
    $careerSummary = $details->career_entrepreneurship ?: $legacyCareerSection?->intro;
    $customSections = $customSections->reject(fn ($section) => in_array($section->section_key, [
        'early_life', 'career_entrepreneurship', 'impact',
    ], true));
    $firstEducation = $educationItems->first();
@endphp

<main>
    <section class="person-hero">
        <div class="container-xl">
            <div class="breadcrumbs">
                <a href="{{ route('site.home') }}">Home</a><i class="bi bi-chevron-right"></i>
                <a href="{{ route('site.entrepreneurs.index') }}">CEO</a><i class="bi bi-chevron-right"></i>
                <span>{{ $personName }}</span>
            </div>
            <div class="person-hero-grid {{ $details->profile_quote ? '' : 'no-quote' }}">
                <img class="person-portrait" src="{{ $portrait }}" alt="{{ $personName }}" loading="eager">
                <div class="person-intro">
                    @if($profile->is_verified)
                        <div class="verified-pill"><i class="bi bi-patch-check-fill"></i> VERIFIED PROFILE</div>
                    @endif
                    <h1>{{ $personName }}</h1>
                    <h2>{{ $details->headline ?: ($details->current_position ?: $details->professional_title) }}</h2>
                    <div class="person-tags">
                        @foreach($tags as $tag)<span>{{ $tag }}</span>@endforeach
                        @if($details->industry && !$tags->contains($details->industry->name))<span>{{ $details->industry->name }}</span>@endif
                    </div>
                    <p>{{ $details->short_bio ?: (\Illuminate\Support\Str::limit(strip_tags((string) $details->about), 420) ?: 'Professional information will be added to this profile soon.') }}</p>
                    <div class="social-row">
                        @if($primaryWebsite)<a href="{{ $primaryWebsite }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-globe2"></i>{{ parse_url($primaryWebsite, PHP_URL_HOST) ?: $primaryWebsite }}</a>@endif
                        @foreach($version->links->where('is_visible', true)->where('link_type', '!=', 'website')->take(5) as $link)
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"><i class="bi {{ $link->icon ?: 'bi-link-45deg' }}"></i>{{ $link->label ?: \Illuminate\Support\Str::headline($link->link_type) }}</a>
                        @endforeach
                    </div>
                </div>
                @if($details->profile_quote)
                    <blockquote class="quote-card"><span class="quote-mark">“</span>{{ $details->profile_quote }}<small>— {{ $personName }}</small></blockquote>
                @endif
            </div>
        </div>
    </section>

    <div class="container-xl">
        <nav class="person-tabs" aria-label="Profile sections">
            <a class="active" href="#overview"><i class="bi bi-house-door"></i> Overview</a>
            <a href="#career"><i class="bi bi-signpost-split"></i> Career Timeline</a>
            <a href="#companies"><i class="bi bi-buildings"></i> Companies</a>
            <a href="#education"><i class="bi bi-mortarboard"></i> Education</a>
            <a href="#achievements"><i class="bi bi-star"></i> Achievements</a>
            <a href="#media"><i class="bi bi-camera-video"></i> Media &amp; Interviews</a>
            <a href="#references"><i class="bi bi-link-45deg"></i> References</a>
        </nav>
    </div>

    <div class="container-xl person-layout">
        <div class="person-content">
            <section class="content-section" id="overview">
                <h2>Overview</h2>
                @if($details->about)
                    <p class="person-overview">{!! nl2br(e($details->about)) !!}</p>
                @elseif($details->short_bio)
                    <p class="person-overview">{{ $details->short_bio }}</p>
                @else
                    <p class="profile-empty-state">A professional overview will appear here when profile information is added.</p>
                @endif
            </section>

            <section class="content-section" id="career">
                <div class="section-heading mb-1">
                    <div><h2>Career Timeline</h2></div>
                    <a class="view-link" href="{{ route('site.entrepreneurs.index') }}">View All <i class="bi bi-arrow-right"></i></a>
                </div>
                @if($timelineItems->isNotEmpty())
                    <div class="timeline">
                        @foreach($timelineItems as $item)
                            <div class="timeline-item">
                                <strong>{{ $item['year'] ?? '' }}</strong>
                                <span>{{ $item['title'] ?? '' }}@if(!empty($item['organization']))<br>{{ $item['organization'] }}@endif @if(!empty($item['location']))<small class="d-block">{{ $item['location'] }}</small>@endif</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($careerSummary)<p class="mt-3">{{ $careerSummary }}</p>@elseif($timelineItems->isEmpty())<p class="profile-empty-state">Career milestones will appear here when they are added to this profile.</p>@endif
            </section>

            <section class="content-section" id="companies">
                <div class="section-heading mb-2"><div><h2>Companies</h2></div><a class="view-link" href="{{ route('site.companies.index') }}">View All <i class="bi bi-arrow-right"></i></a></div>
                @foreach($affiliations as $affiliation)
                    <article class="soft-card company-highlight">
                        <div class="growbig-logo-small">@if(!empty($affiliation['logo_url']))<img src="{{ $affiliation['logo_url'] }}" alt="{{ $affiliation['company'] ?? 'Company logo' }}">@else<i class="bi bi-buildings"></i>@endif</div>
                        <div>
                            <h3>{{ $affiliation['company'] ?? '' }} @if(!empty($affiliation['position']))<span class="pill ms-1">{{ $affiliation['position'] }}</span>@endif</h3>
                            @if(!empty($affiliation['period']))<small>{{ $affiliation['period'] }}</small>@endif
                            @if(!empty($affiliation['description']))<p>{{ $affiliation['description'] }}</p>@endif
                            @if(!empty($affiliation['company_url']))<a class="btn btn-outline-blue btn-sm" href="{{ $affiliation['company_url'] }}" target="_blank" rel="noopener noreferrer">View Company <i class="bi bi-arrow-right"></i></a>@endif
                        </div>
                    </article>
                @endforeach
                @foreach($companyRelationships as $relationship)
                    @php
                        $related = $relationship->relatedProfile;
                        $company = $related?->publishedVersion?->companyDetails;
                        $logo = $related?->publishedVersion?->mediaLinks?->firstWhere('role','logo')?->media?->url;
                    @endphp
                    <article class="soft-card company-highlight">
                        <div class="growbig-logo-small">@if($logo)<img src="{{ $logo }}" alt="{{ $company?->company_name }}">@else<i class="bi bi-buildings"></i>@endif</div>
                        <div>
                            <h3>{{ $company?->company_name ?: $related?->slug }} @if($relationship->position)<span class="pill ms-1">{{ $relationship->position }}</span>@endif</h3>
                            @if($relationship->description)<p>{{ $relationship->description }}</p>@endif
                            <a class="btn btn-outline-blue btn-sm" href="{{ route('site.profile.show', $related) }}">View Company <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>
                @endforeach
                @if($affiliations->isEmpty() && $companyRelationships->isEmpty())
                    <p class="profile-empty-state">Company affiliations and leadership roles will appear here when they are published.</p>
                @endif
            </section>

            <section class="content-section" id="education">
                <h2>Education</h2>
                @foreach($educationItems as $education)
                    <article class="soft-card education-card mt-2">
                        @if(!empty($education['image_url']))
                            <img src="{{ $education['image_url'] }}" alt="{{ $education['institution'] ?? 'Education institution' }}" loading="lazy">
                        @else
                            <div class="education-crest-fallback"><i class="bi bi-mortarboard-fill"></i></div>
                        @endif
                        <div>
                            <h3>{{ $education['institution'] ?? '' }}</h3>
                            <p>{{ trim(($education['degree'] ?? '') . (!empty($education['field']) ? ' · '.$education['field'] : '')) }}</p>
                            @if(!empty($education['year']))<small>{{ $education['year'] }}</small>@endif
                            @if(!empty($education['details']))<p>{{ $education['details'] }}</p>@endif
                        </div>
                    </article>
                @endforeach
                @if($details->education_summary && $educationItems->isNotEmpty())
                    <p class="mt-2">{{ $details->education_summary }}</p>
                @elseif($details->education_summary && $educationItems->isEmpty())
                    <article class="soft-card education-card mt-2">
                        <div class="education-crest-fallback"><i class="bi bi-mortarboard-fill"></i></div>
                        <div><h3>Education &amp; Qualifications</h3><p>{{ $details->education_summary }}</p></div>
                    </article>
                @elseif($educationItems->isEmpty())
                    <p class="profile-empty-state">Education and qualifications will appear here when they are added to this profile.</p>
                @endif
            </section>

            <section class="content-section" id="achievements">
                <h2>Achievements &amp; Contributions</h2>
                @if($personAchievementItems->isNotEmpty())
                    <div class="contribution-grid mt-2">
                        @foreach($personAchievementItems as $achievement)
                            <article class="soft-card contribution-card">
                                <i class="bi {{ $achievement['icon'] ?? 'bi-trophy-fill' }}"></i>
                                <div>
                                    <strong>{{ $achievement['title'] ?? '' }}</strong>
                                    @if(!empty($achievement['issuer']) || !empty($achievement['year']))<small>{{ trim(($achievement['issuer'] ?? '') . (!empty($achievement['year']) ? ' · '.$achievement['year'] : '')) }}</small>@endif
                                    @if(!empty($achievement['description']))<small>{{ $achievement['description'] }}</small>@endif
                                    @if(!empty($achievement['url']))<a href="{{ $achievement['url'] }}" target="_blank" rel="noopener noreferrer">Source <i class="bi bi-box-arrow-up-right"></i></a>@endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
                @if($details->awards_publications_recognition)
                    <p class="mt-3">{{ $details->awards_publications_recognition }}</p>
                @elseif($personAchievementItems->isEmpty())
                    <p class="profile-empty-state">Achievements, publications and contributions will appear here when they are added.</p>
                @endif
            </section>

            <section class="content-section" id="media">
                <div class="section-heading mb-2"><div><h2><i class="bi bi-play-btn-fill text-warning me-1"></i> Media &amp; Interviews</h2></div><a class="view-link" href="#references">View All <i class="bi bi-arrow-right"></i></a></div>
                <div class="person-media-grid">
                    @foreach($mediaItems as $mediaItem)
                        <article class="person-media-card">
                            @php($mediaThumbnail = $mediaItem['thumbnail_url'] ?? $mediaItem['image_url'] ?? $mediaItem['image'] ?? null)
                            @if($mediaThumbnail)
                                <div class="person-media-image"><img src="{{ $mediaThumbnail }}" alt="{{ $mediaItem['title'] ?? $personName }}" loading="lazy"><span class="media-play-overlay"><i class="bi bi-play-fill"></i></span></div>
                            @else
                                <div class="media-placeholder"><i class="bi bi-play-circle"></i></div>
                            @endif
                            <div class="media-body">
                                <h4>@if(!empty($mediaItem['url']))<a href="{{ $mediaItem['url'] }}" target="_blank" rel="noopener noreferrer">{{ $mediaItem['title'] ?? '' }}</a>@else{{ $mediaItem['title'] ?? '' }}@endif</h4>
                                <small>{{ $mediaItem['publisher'] ?? '' }} @if(!empty($mediaItem['date']))· {{ \Illuminate\Support\Carbon::parse($mediaItem['date'])->format('M d, Y') }}@endif</small>
                            </div>
                        </article>
                    @endforeach
                    @foreach($galleryItems as $gallery)
                        <article class="person-media-card">
                            <img src="{{ $gallery->media?->url }}" alt="{{ $gallery->media?->alt_text ?: $personName }}" loading="lazy">
                            <div class="media-body"><h4>{{ $gallery->media?->caption ?: 'Profile image' }}</h4></div>
                        </article>
                    @endforeach
                </div>
                @if($mediaItems->isEmpty() && $galleryItems->isEmpty())
                    <p class="profile-empty-state">Interviews, articles and media coverage will appear here when they are published.</p>
                @endif
            </section>

            <section class="content-section" id="references">
                <h2>References &amp; Sources</h2>
                <div class="reference-wrap">
                    <ol class="references">
                        @forelse($version->references as $reference)
                            <li>
                                @if($reference->url)<a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer">{{ $reference->title }} <i class="bi bi-box-arrow-up-right"></i></a>@else{{ $reference->title }}@endif
                                @if($reference->publisher)<small> · {{ $reference->publisher }}</small>@endif
                            </li>
                        @empty
                            <li class="profile-empty-state">No public references have been added to this profile yet.</li>
                        @endforelse
                    </ol>
                    <div class="reference-note">This profile is based on information and sources submitted for editorial review.<br>Last updated: {{ optional($profile->published_at)->format('M d, Y') }}</div>
                </div>
            </section>

            @foreach($customSections as $customSection)
                <section class="content-section" id="custom-{{ $customSection->id }}">
                    <h2>{{ $customSection->title }}</h2>
                    @if($customSection->intro)<p>{{ $customSection->intro }}</p>@endif
                    @foreach($customSection->items->where('is_visible', true) as $item)
                        <article class="soft-card contribution-card mb-2">
                            @if($item->image_path)<img src="{{ asset('public/'.ltrim($item->image_path,'/')) }}" alt="{{ $item->title }}">@endif
                            <div><strong>{{ $item->title }}</strong>@if($item->subtitle)<small>{{ $item->subtitle }}</small>@endif @if($item->description)<p>{{ $item->description }}</p>@endif @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">Learn more <i class="bi bi-arrow-up-right"></i></a>@endif</div>
                        </article>
                    @endforeach
                </section>
            @endforeach
        </div>

        <aside class="person-sidebar">
            <section class="side-card">
                <div class="side-card-head"><h3><i class="bi bi-person-fill"></i> Quick Facts</h3></div>
                <table class="facts-table">
                    <tr><td>Full Name</td><td>{{ $personName }}</td></tr>
                    @if($details->current_position || $details->professional_title)<tr><td>Current Position</td><td>{{ $details->current_position ?: $details->professional_title }}</td></tr>@endif
                    @if($details->company_organisation)<tr><td>Organization</td><td>{{ $details->company_organisation }}</td></tr>@endif
                    @if($details->nationality)<tr><td>Nationality</td><td>{{ $details->nationality }}</td></tr>@endif
                    @if($details->show_date_of_birth && $details->date_of_birth)<tr><td>Date of Birth</td><td>{{ $details->date_of_birth->format('F d, Y') }}</td></tr>@endif
                    @if($details->hometown)<tr><td>Hometown</td><td>{{ $details->hometown }}</td></tr>@endif
                    @if($firstEducation)<tr><td>Education</td><td>{{ $firstEducation['institution'] ?? '' }}</td></tr>@elseif($details->education_summary)<tr><td>Education</td><td>{{ \Illuminate\Support\Str::limit($details->education_summary, 100) }}</td></tr>@endif
                    @if($details->occupation)<tr><td>Occupation</td><td>{{ $details->occupation }}</td></tr>@endif
                    @if($details->industry)<tr><td>Industries</td><td>{{ $details->industry->name }}</td></tr>@endif
                    @if($details->location)<tr><td>Location</td><td>{{ $details->location }}</td></tr>@endif
                    @if($primaryWebsite)<tr><td>Website</td><td><a href="{{ $primaryWebsite }}" target="_blank" rel="noopener noreferrer">{{ parse_url($primaryWebsite, PHP_URL_HOST) ?: $primaryWebsite }} <i class="bi bi-box-arrow-up-right"></i></a></td></tr>@endif
                    @if($details->show_public_email && $details->public_contact_email)<tr><td>Email</td><td><a href="mailto:{{ $details->public_contact_email }}">{{ $details->public_contact_email }}</a></td></tr>@endif
                    @if($details->show_public_phone && $details->public_phone)<tr><td>Phone</td><td><a href="tel:{{ $details->public_phone }}">{{ $details->public_phone }}</a></td></tr>@endif
                </table>
            </section>

            <section class="side-card">
                <div class="side-card-head"><h3><i class="bi bi-person-lines-fill"></i> Connect</h3></div>
                @if($primaryWebsite)<div class="connect-row"><span class="connect-icon"><i class="bi bi-globe2"></i></span>Website<a href="{{ $primaryWebsite }}" target="_blank" rel="noopener noreferrer">{{ parse_url($primaryWebsite, PHP_URL_HOST) ?: $primaryWebsite }} <i class="bi bi-box-arrow-up-right"></i></a></div>@endif
                @foreach($version->links->where('is_visible', true)->where('link_type', '!=', 'website') as $link)
                    <div class="connect-row"><span class="connect-icon"><i class="bi {{ $link->icon ?: 'bi-link-45deg' }}"></i></span>{{ $link->label ?: \Illuminate\Support\Str::headline($link->link_type) }}<a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">{{ parse_url($link->url, PHP_URL_HOST) ?: $link->url }} <i class="bi bi-box-arrow-up-right"></i></a></div>
                @endforeach
                @if(!$primaryWebsite && $version->links->where('is_visible', true)->isEmpty())
                    <div class="connect-row profile-empty-state">Public contact links will appear here when they are added.</div>
                @endif
            </section>

            <section class="side-card">
                <div class="side-card-head"><h3><i class="bi bi-buildings-fill"></i> Related Companies</h3><a class="view-link" href="{{ route('site.companies.index') }}">View All <i class="bi bi-arrow-right"></i></a></div>
                @foreach($companyRelationships as $relationship)
                    @php($relatedCompany = $relationship->relatedProfile)
                    <a class="mini-company" href="{{ route('site.profile.show', $relatedCompany) }}">
                        @php($relatedLogo = $relatedCompany->publishedVersion?->mediaLinks?->firstWhere('role','logo')?->media?->url)
                        <span class="mini-logo">@if($relatedLogo)<img src="{{ $relatedLogo }}" alt="">@else<i class="bi bi-buildings"></i>@endif</span>
                        <span><strong>{{ $relatedCompany->publishedVersion?->companyDetails?->company_name ?: $relatedCompany->slug }}</strong><small>{{ $relationship->position ?: $relationship->relation_type }}</small></span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                @endforeach
                @if($companyRelationships->isEmpty())<div class="connect-row profile-empty-state">No related companies have been listed yet.</div>@endif
            </section>

            <section class="side-card">
                <div class="side-card-head"><h3><i class="bi bi-people-fill"></i> Related People</h3><a class="view-link" href="{{ route('site.entrepreneurs.index') }}">View All <i class="bi bi-arrow-right"></i></a></div>
                @foreach($peopleRelationships as $relationship)
                    @php
                        $relatedPerson = $relationship->relatedProfile;
                        $relatedPersonName = $relatedPerson?->publishedVersion?->personalDetails?->full_name ?: $relatedPerson?->slug;
                        $relatedPortrait = $relatedPerson?->publishedVersion?->mediaLinks?->firstWhere('role','portrait')?->media?->url;
                    @endphp
                    <a class="mini-person" href="{{ route('site.profile.show', $relatedPerson) }}">
                        @if($relatedPortrait)<img src="{{ $relatedPortrait }}" alt="{{ $relatedPersonName }}">@else<span class="mini-person-avatar"><i class="bi bi-person"></i></span>@endif
                        <span><strong>{{ $relatedPersonName }}</strong><small>{{ $relationship->position ?: $relationship->relation_type }}</small></span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                @endforeach
                @if($peopleRelationships->isEmpty())<div class="connect-row profile-empty-state">No related people have been listed yet.</div>@endif
            </section>

            <div class="person-side-cta">
                <i class="bi bi-person-circle text-primary fs-2"></i>
                <h3>Are You an Entrepreneur?</h3>
                <p>Request your profile to be published on {{ $siteSetting->site_name }}.</p>
                <a class="btn btn-gold btn-sm" href="{{ route('customer.register') }}">Request a Profile <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </aside>
    </div>
</main>
@endsection
