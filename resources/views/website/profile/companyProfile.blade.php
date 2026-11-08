@extends('website.master.master')

@section('title')
    {{ $version->seo?->meta_title ?: (($details->company_name ?: $profile->slug) . ' | ' . $siteSetting->site_name) }}
@endsection

@section('body_class', 'company-page')

@section('body')
@php
    $companyName = $details->company_name ?: $profile->slug;
    $logo = $version->mediaLinks->firstWhere('role', 'logo')?->media?->url
        ?: asset('public/website/assets/images/brand-mark-reference.png');
    $banner = $version->mediaLinks->firstWhere('role', 'banner')?->media?->url;
    $relatedPeople = $version->relationships->filter(fn ($item) => $item->is_visible && $item->relatedProfile?->profile_type === 'personal');
    $relatedCompanies = $version->relationships->filter(fn ($item) => $item->is_visible && $item->relatedProfile?->profile_type === 'company');
    $leadershipItems = collect($details->leadership_items ?? [])->filter(fn ($item) => filled($item['name'] ?? null));
    $serviceItems = collect($details->service_items ?? [])->filter(fn ($item) => filled($item['title'] ?? null));
    $milestoneItems = collect($details->milestone_items ?? [])->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['year'] ?? null));
    $mediaItems = collect($details->media_items ?? [])->filter(fn ($item) => filled($item['title'] ?? null));
    $highlights = collect($details->highlights ?? [])->filter(fn ($item) => filled($item['title'] ?? null));
    $galleryItems = $version->mediaLinks->where('role', 'gallery');
    $customSections = $version->sections->filter(fn ($section) => $section->is_visible && !str_starts_with($section->section_key, '_editor_'));
    $legacyAboutSection = $customSections->firstWhere('section_key', 'about');
    $legacyServicesSection = $customSections->firstWhere('section_key', 'services_operations');
    $legacyAchievementsSection = $customSections->firstWhere('section_key', 'impact_achievements');
    $legacyLeadershipSection = $customSections->firstWhere('section_key', 'leadership_founders');
    $legacyAboutItems = $legacyAboutSection?->items?->where('is_visible', true)->values() ?? collect();
    $legacyServiceItems = $legacyServicesSection?->items?->where('is_visible', true)->values() ?? collect();
    $legacyAchievementItems = $legacyAchievementsSection?->items?->where('is_visible', true)->values() ?? collect();
    $legacyLeadershipItems = $legacyLeadershipSection?->items?->where('is_visible', true)->values() ?? collect();
    if ($highlights->isEmpty() && $legacyAboutItems->isNotEmpty()) {
        $highlights = $legacyAboutItems->map(fn ($item) => [
            'title' => $item->title,
            'description' => $item->subtitle ?: $item->description,
            'icon' => $item->icon,
        ]);
    }
    if ($serviceItems->isEmpty() && $legacyServiceItems->isNotEmpty()) {
        $serviceItems = $legacyServiceItems->map(fn ($item) => [
            'title' => $item->title,
            'description' => $item->description ?: $item->subtitle,
            'icon' => $item->icon,
        ]);
    }
    if ($leadershipItems->isEmpty() && $legacyLeadershipItems->isNotEmpty()) {
        $leadershipItems = $legacyLeadershipItems->map(fn ($item) => [
            'name' => $item->title,
            'position' => $item->subtitle,
            'bio' => $item->description,
            'profile_url' => $item->url,
            'photo_url' => $item->image_path ? asset('public/' . ltrim($item->image_path, '/')) : null,
        ]);
    }
    $companyTimelineItems = $milestoneItems->isNotEmpty()
        ? $milestoneItems
        : ($details->founded_year ? collect([[
            'year' => (string) $details->founded_year,
            'title' => 'Founded ' . $companyName,
            'description' => null,
        ]]) : collect());
    $companyAchievementItems = $legacyAchievementItems->isNotEmpty()
        ? $legacyAchievementItems->map(fn ($item) => [
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'description' => $item->description,
            'icon' => $item->icon,
        ])
        : collect();
    $customSections = $customSections->reject(fn ($section) => in_array($section->section_key, [
        'about', 'history_background', 'services_operations', 'leadership_founders', 'impact_achievements',
    ], true));
    $website = $details->official_website ?: $version->links->firstWhere('link_type', 'website')?->url;
    $websiteHost = $website ? (parse_url($website, PHP_URL_HOST) ?: $website) : null;
    $companyHeroTitle = trim((string) ($details->tagline ?: 'Business for a Bigger Tomorrow'));
    $companyHeroWords = preg_split('/\s+/', $companyHeroTitle) ?: [];
    $companyHeroHighlight = count($companyHeroWords) > 1 ? array_pop($companyHeroWords) : $companyHeroTitle;
    $companyHeroPrefix = count($companyHeroWords) ? implode(' ', $companyHeroWords) : '';
@endphp

<main>
    <section class="company-hero" @if($banner) style="background-image:linear-gradient(90deg,rgba(4,19,46,.15),rgba(4,19,46,.35)),url('{{ $banner }}')" @endif>
        <div class="company-hero-title">
            @if($companyHeroPrefix){{ $companyHeroPrefix }} @endif<span>{{ $companyHeroHighlight }}</span>
            <div class="company-hero-sub">
                {{ collect([$details->industry?->name, $details->business_type, $details->area_served])->filter()->implode(' | ') }}
            </div>
        </div>
        <div class="company-profile-card">
            <div class="company-logo-box"><img class="company-logo-img" src="{{ $logo }}" alt="{{ $companyName }} logo"></div>
            <div class="company-profile-text">
                <h1>{{ $companyName }} @if($profile->is_verified)<i class="bi bi-patch-check-fill verified" aria-label="Verified"></i>@endif</h1>
                <p>{{ $details->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $details->overview), 200) }}</p>
                <div class="company-links">
                    @if($details->headquarters)<span><i class="bi bi-geo-alt-fill text-primary"></i> {{ $details->headquarters }}</span>@endif
                    @if($website)<a href="{{ $website }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-link-45deg"></i> {{ $websiteHost }}</a>@endif
                    @foreach($version->links->where('is_visible', true)->take(4) as $link)
                        <a href="{{ $link->url }}" aria-label="{{ $link->label ?: $link->link_type }}" target="_blank" rel="noopener noreferrer"><i class="bi {{ $link->icon ?: 'bi-link-45deg' }}"></i></a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="container-xl">
        <nav class="company-tabs" aria-label="Company sections">
            <a class="active" href="#overview"><i class="bi bi-house-door"></i> Overview</a>
            <a href="#history"><i class="bi bi-journal-bookmark"></i> History</a>
            <a href="#leadership"><i class="bi bi-people"></i> Leadership</a>
            <a href="#services"><i class="bi bi-box-seam"></i> Products &amp; Services</a>
            <a href="#achievements"><i class="bi bi-star"></i> Achievements</a>
            <a href="#media"><i class="bi bi-camera-video"></i> Media &amp; News</a>
            <a href="#references"><i class="bi bi-file-earmark-text"></i> References</a>
            @if($website)<a class="visit-site" href="{{ $website }}" target="_blank" rel="noopener noreferrer">Visit Website <i class="bi bi-box-arrow-up-right ms-1"></i></a>@endif
        </nav>
    </div>

    <div class="container-xl">
        <button class="btn btn-outline-blue btn-sm page-nav-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#pageNavCanvas" aria-controls="pageNavCanvas"><i class="bi bi-list-ul me-2"></i> On This Page</button>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="pageNavCanvas" aria-labelledby="pageNavCanvasLabel">
            <div class="offcanvas-header"><h5 class="offcanvas-title serif" id="pageNavCanvasLabel">On This Page</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
            <div class="offcanvas-body"><div class="d-grid gap-1">
                <a class="mobile-nav-link" href="#overview" data-bs-dismiss="offcanvas">Overview</a>
                <a class="mobile-nav-link" href="#history" data-bs-dismiss="offcanvas">History &amp; Timeline</a>
                <a class="mobile-nav-link" href="#leadership" data-bs-dismiss="offcanvas">Leadership Team</a>
                <a class="mobile-nav-link" href="#services" data-bs-dismiss="offcanvas">Products &amp; Services</a>
                <a class="mobile-nav-link" href="#achievements" data-bs-dismiss="offcanvas">Achievements</a>
                <a class="mobile-nav-link" href="#media" data-bs-dismiss="offcanvas">Media &amp; News</a>
                <a class="mobile-nav-link" href="#related" data-bs-dismiss="offcanvas">Related People</a>
                <a class="mobile-nav-link" href="#related-companies" data-bs-dismiss="offcanvas">Related Companies</a>
                <a class="mobile-nav-link" href="#references" data-bs-dismiss="offcanvas">References &amp; Sources</a>
            </div></div>
        </div>
    </div>

    <div class="container-xl company-layout">
        <aside class="page-nav">
            <h3>On This Page</h3>
            <a class="active" href="#overview"><i class="bi bi-globe2"></i> Overview</a>
            <a href="#history"><i class="bi bi-calendar3"></i> History &amp; Timeline</a>
            <a href="#leadership"><i class="bi bi-person"></i> Leadership Team</a>
            <a href="#services"><i class="bi bi-box-seam"></i> Products &amp; Services</a>
            <a href="#achievements"><i class="bi bi-star"></i> Achievements</a>
            <a href="#media"><i class="bi bi-play-btn"></i> Media &amp; News</a>
            <a href="#related"><i class="bi bi-people"></i> Related People</a>
            <a href="#related-companies"><i class="bi bi-buildings"></i> Related Companies</a>
            <a href="#references"><i class="bi bi-link-45deg"></i> References &amp; Sources</a>
        </aside>

        <div class="company-content">
            <section class="content-section" id="overview">
                <h2>Overview</h2>
                @if($details->overview)
                    <p>{{ $details->overview }}</p>
                @elseif($details->short_description)
                    <p>{{ $details->short_description }}</p>
                @else
                    <p class="profile-empty-state">Company overview information will appear here when it is added.</p>
                @endif
                @if($highlights->isNotEmpty())
                    <div class="feature-mini-grid">
                        @foreach($highlights as $highlight)
                            <div class="soft-card feature-mini">
                                <i class="bi {{ $highlight['icon'] ?? 'bi-stars' }}"></i>
                                <strong>{{ $highlight['title'] }}</strong>
                                @if(!empty($highlight['description']))<small>{{ $highlight['description'] }}</small>@endif
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($highlights->isEmpty() && !$details->mission && !$details->vision && !$details->values_culture)
                    <p class="profile-empty-state">Company highlights will appear here when they are added.</p>
                @endif
                @if($details->mission || $details->vision || $details->values_culture)
                    <div class="feature-mini-grid mt-3">
                        @foreach([['Mission',$details->mission,'bi-bullseye'],['Vision',$details->vision,'bi-lightbulb'],['Values & Culture',$details->values_culture,'bi-people']] as [$label,$value,$icon])
                            @if($value)
                                <div class="soft-card feature-mini"><i class="bi {{ $icon }}"></i><strong>{{ $label }}</strong><small>{{ \Illuminate\Support\Str::limit(strip_tags($value), 100) }}</small></div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="content-section" id="history">
                <div class="section-heading mb-1"><div><h2>History &amp; Timeline</h2></div><a class="view-link" href="#references">View Full Timeline <i class="bi bi-arrow-right"></i></a></div>
                @if($details->history_background)<p>{{ $details->history_background }}</p>@endif
                @if($companyTimelineItems->isNotEmpty())
                    <div class="timeline">
                        @foreach($companyTimelineItems as $milestone)
                            <div class="timeline-item"><strong>{{ $milestone['year'] ?? '' }}</strong><span>{{ $milestone['title'] ?? '' }}@if(!empty($milestone['description']))<br>{{ $milestone['description'] }}@endif</span></div>
                        @endforeach
                    </div>
                @elseif(!$details->history_background)
                    <p class="profile-empty-state">Company history and milestones will appear here when they are added.</p>
                @endif
            </section>

            <section class="content-section" id="leadership">
                <div class="section-heading mb-2"><div><h2>Leadership Team</h2></div><a class="view-link" href="{{ route('site.entrepreneurs.index') }}">View All <i class="bi bi-arrow-right"></i></a></div>
                <div class="leader-grid">
                    @foreach($leadershipItems as $leader)
                        <article class="soft-card leader-card">
                            @if(!empty($leader['photo_url']))<img src="{{ $leader['photo_url'] }}" alt="{{ $leader['name'] }}" loading="lazy">@else<div class="leader-avatar"><i class="bi bi-person-fill"></i></div>@endif
                            <div>
                                <h4>{{ $leader['name'] }}</h4>
                                <small>{{ $leader['position'] ?? '' }}</small>
                                @if(!empty($leader['bio']))<p>{{ $leader['bio'] }}</p>@endif
                                @if(!empty($leader['profile_url']))<a class="btn btn-outline-blue btn-sm" href="{{ $leader['profile_url'] }}" target="_blank" rel="noopener noreferrer">View Profile <i class="bi bi-arrow-up-right"></i></a>@endif
                            </div>
                        </article>
                    @endforeach
                    @foreach($relatedPeople as $relationship)
                        @php
                            $personProfile = $relationship->relatedProfile;
                            $person = $personProfile?->publishedVersion?->personalDetails;
                            $personPhoto = $personProfile?->publishedVersion?->mediaLinks?->firstWhere('role','portrait')?->media?->url;
                        @endphp
                        <article class="soft-card leader-card">
                            @if($personPhoto)<img src="{{ $personPhoto }}" alt="{{ $person?->full_name }}">@else<div class="leader-avatar"><i class="bi bi-person-fill"></i></div>@endif
                            <div>
                                <h4>{{ $person?->full_name ?: $personProfile?->slug }} @if($personProfile?->is_verified)<i class="bi bi-patch-check-fill verified"></i>@endif</h4>
                                <small>{{ $relationship->position ?: $relationship->relation_type }}</small>
                                @if($relationship->description)<p>{{ $relationship->description }}</p>@endif
                                <a class="btn btn-outline-blue btn-sm" href="{{ route('site.profile.show', $personProfile) }}">View Profile <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                @if($leadershipItems->isEmpty() && $relatedPeople->isEmpty())
                    <p class="profile-empty-state">Founders, directors and leadership team members will appear here when they are linked to this company.</p>
                @endif
            </section>

            <section class="content-section" id="services">
                <div class="section-heading mb-2"><div><h2>Products &amp; Services</h2></div><a class="view-link" href="#references">View All Services <i class="bi bi-arrow-right"></i></a></div>
                <div class="service-grid">
                    @foreach($serviceItems as $service)
                        <div class="soft-card service-card">
                            <i class="bi {{ $service['icon'] ?? 'bi-box-seam' }}"></i>
                            <strong>{{ $service['title'] }}</strong>
                            @if(!empty($service['description']))<small>{{ $service['description'] }}</small>@endif
                        </div>
                    @endforeach
                </div>
                @if($serviceItems->isEmpty())
                    <p class="profile-empty-state">Products and services will appear here when the company adds them.</p>
                @endif
            </section>

            <section class="content-section" id="achievements">
                <h2>Achievements &amp; Milestones</h2>
                @if($companyAchievementItems->isNotEmpty())
                    <div class="achievement-grid mt-2">
                        @foreach($companyAchievementItems->take(8) as $achievement)
                            <div class="soft-card achievement-card">
                                <i class="bi {{ $achievement['icon'] ?? 'bi-trophy-fill' }}"></i>
                                <div><small>{{ $achievement['subtitle'] ?? 'Achievement' }}</small><strong>{{ $achievement['title'] ?? '' }}</strong>@if(!empty($achievement['description']))<small>{{ $achievement['description'] }}</small>@endif</div>
                            </div>
                        @endforeach
                    </div>
                @elseif($milestoneItems->isNotEmpty())
                    <div class="achievement-grid mt-2">
                        @foreach($milestoneItems->take(8) as $milestone)
                            <div class="soft-card achievement-card">
                                <i class="bi bi-trophy-fill"></i>
                                <div><small>{{ $milestone['year'] ?? 'Milestone' }}</small><strong>{{ $milestone['title'] ?? '' }}</strong>@if(!empty($milestone['description']))<small>{{ $milestone['description'] }}</small>@endif</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="profile-empty-state">Company achievements and milestones will appear here when they are added.</p>
                @endif
            </section>

            <section class="content-section" id="media">
                <div class="section-heading mb-2"><div><h2>Media &amp; News</h2></div><a class="view-link" href="#references">View All <i class="bi bi-arrow-right"></i></a></div>
                <div class="news-grid">
                    @foreach($mediaItems as $mediaItem)
                        <article class="soft-card news-card">
                            @php
                                $mediaThumbnail = $mediaItem['thumbnail_url'] ?? $mediaItem['image_url'] ?? $mediaItem['image'] ?? null;
                            @endphp
                            @if($mediaThumbnail)<img src="{{ $mediaThumbnail }}" alt="{{ $mediaItem['title'] }}" loading="lazy">@else<div class="news-image-placeholder"><i class="bi bi-newspaper"></i></div>@endif
                            <div class="news-card-body">
                                <h4>@if(!empty($mediaItem['url']))<a href="{{ $mediaItem['url'] }}" target="_blank" rel="noopener noreferrer">{{ $mediaItem['title'] }}</a>@else{{ $mediaItem['title'] }}@endif</h4>
                                <small>{{ $mediaItem['publisher'] ?? '' }} @if(!empty($mediaItem['date']))· {{ \Illuminate\Support\Carbon::parse($mediaItem['date'])->format('M d, Y') }}@endif</small>
                            </div>
                        </article>
                    @endforeach
                    @foreach($galleryItems as $gallery)
                        <article class="soft-card news-card">
                            <img src="{{ $gallery->media?->url }}" alt="{{ $gallery->media?->alt_text ?: $companyName }}" loading="lazy">
                            <div class="news-card-body"><h4>{{ $gallery->media?->caption ?: 'Company gallery' }}</h4></div>
                        </article>
                    @endforeach
                </div>
                @if($mediaItems->isEmpty() && $galleryItems->isEmpty())
                    <p class="profile-empty-state">Company news, interviews and media coverage will appear here when they are published.</p>
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
                                @if($reference->description)<p>{{ $reference->description }}</p>@endif
                            </li>
                        @empty
                            <li class="profile-empty-state">No public references have been added to this company profile yet.</li>
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

        <aside class="company-sidebar">
            <section class="side-card">
                <div class="side-card-head"><h3><i class="bi bi-clipboard-data-fill"></i> Company Facts</h3></div>
                <table class="facts-table">
                    <tr><td>Official Name</td><td>{{ $companyName }}</td></tr>
                    @if($details->founded_year)<tr><td>Founded</td><td>{{ $details->founded_year }}</td></tr>@endif
                    @if($details->headquarters)<tr><td>Headquarters</td><td>{{ $details->headquarters }}</td></tr>@endif
                    @if($details->industry)<tr><td>Industry</td><td>{{ $details->industry->name }}</td></tr>@endif
                    @if($details->business_type)<tr><td>Business Type</td><td>{{ $details->business_type }}</td></tr>@endif
                    @if($details->company_size)<tr><td>Company Size</td><td>{{ $details->company_size }}</td></tr>@endif
                    @if($details->parent_company)<tr><td>Parent Company</td><td>{{ $details->parent_company }}</td></tr>@endif
                    @php
                        $founderNames = $leadershipItems->filter(fn ($leader) => str_contains(strtolower((string) ($leader['position'] ?? '')), 'founder'))->pluck('name');
                        if ($founderNames->isEmpty()) {
                            $founderNames = $relatedPeople->filter(fn ($relationship) => str_contains(strtolower((string) ($relationship->relation_type ?? '')), 'founder'))->map(fn ($relationship) => $relationship->relatedProfile?->publishedVersion?->personalDetails?->full_name ?: $relationship->relatedProfile?->slug);
                        }
                    @endphp
                    @if($founderNames->isNotEmpty())<tr><td>Founder(s)</td><td>{{ $founderNames->implode(', ') }}</td></tr>@endif
                    @if($leadershipItems->isNotEmpty())
                        <tr>
                            <td>Key People</td>
                            <td>
                                @foreach($leadershipItems->take(5) as $leader)
                                    {{ $leader['name'] }}
                                    @if($leader['position'] ?? null)
                                        ({{ $leader['position'] }})
                                    @endif
                                    <br>
                                @endforeach
                            </td>
                        </tr>
                    @elseif($relatedPeople->isNotEmpty())
                        <tr>
                            <td>Key People</td>
                            <td>
                                @foreach($relatedPeople->take(5) as $relationship)
                                    {{ $relationship->relatedProfile?->publishedVersion?->personalDetails?->full_name ?: $relationship->relatedProfile?->slug }}
                                    <br>
                                @endforeach
                            </td>
                        </tr>
                    @endif
                    @if($serviceItems->isNotEmpty())<tr><td>Services</td><td>{{ $serviceItems->pluck('title')->implode(', ') }}</td></tr>@endif
                    @if($website)<tr><td>Website</td><td><a href="{{ $website }}" target="_blank" rel="noopener noreferrer">{{ $websiteHost }} <i class="bi bi-box-arrow-up-right"></i></a></td></tr>@endif
                    @if($details->show_public_email && $details->public_email)<tr><td>Email</td><td><a href="mailto:{{ $details->public_email }}">{{ $details->public_email }}</a></td></tr>@endif
                    @if($details->show_public_phone && $details->public_phone)<tr><td>Phone</td><td><a href="tel:{{ $details->public_phone }}">{{ $details->public_phone }}</a></td></tr>@endif
                    @if($version->links->where('is_visible', true)->where('link_type', '!=', 'website')->isNotEmpty())
                        <tr><td>Social Media</td><td class="company-social-facts">@foreach($version->links->where('is_visible', true)->where('link_type', '!=', 'website')->take(5) as $link)<a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $link->label ?: $link->link_type }}"><i class="bi {{ $link->icon ?: 'bi-link-45deg' }}"></i></a>@endforeach</td></tr>
                    @endif
                </table>
            </section>

            <section class="side-card" id="related">
                <div class="side-card-head"><h3><i class="bi bi-people-fill"></i> Related People</h3><a class="view-link" href="{{ route('site.entrepreneurs.index') }}">View All <i class="bi bi-arrow-right"></i></a></div>
                @foreach($relatedPeople as $relationship)
                    @php
                        $personProfile = $relationship->relatedProfile;
                        $person = $personProfile?->publishedVersion?->personalDetails;
                        $personPhoto = $personProfile?->publishedVersion?->mediaLinks?->firstWhere('role','portrait')?->media?->url;
                    @endphp
                    <a class="mini-person" href="{{ route('site.profile.show', $personProfile) }}">
                        @if($personPhoto)<img src="{{ $personPhoto }}" alt="{{ $person?->full_name }}">@else<span class="mini-person-avatar"><i class="bi bi-person"></i></span>@endif
                        <span><strong>{{ $person?->full_name ?: $personProfile?->slug }}</strong><small>{{ $relationship->position ?: $relationship->relation_type }}</small></span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                @endforeach
                @if($relatedPeople->isEmpty())<div class="connect-row profile-empty-state">No related people have been listed yet.</div>@endif
            </section>

            <section class="side-card" id="related-companies">
                <div class="side-card-head"><h3><i class="bi bi-buildings-fill"></i> Related Companies</h3><a class="view-link" href="{{ route('site.companies.index') }}">View All <i class="bi bi-arrow-right"></i></a></div>
                @foreach($relatedCompanies as $relationship)
                    @php
                        $relatedCompany = $relationship->relatedProfile;
                        $relatedCompanyLogo = $relatedCompany?->publishedVersion?->mediaLinks?->firstWhere('role', 'logo')?->media?->url;
                    @endphp
                    <a class="mini-company" href="{{ route('site.profile.show', $relatedCompany) }}">
                        <span class="mini-logo">@if($relatedCompanyLogo)<img src="{{ $relatedCompanyLogo }}" alt="">@else<i class="bi bi-buildings"></i>@endif</span>
                        <span><strong>{{ $relatedCompany?->publishedVersion?->companyDetails?->company_name ?: $relatedCompany?->slug }}</strong><small>{{ $relationship->position ?: $relationship->relation_type }}</small></span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                @endforeach
                @if($relatedCompanies->isEmpty())<div class="connect-row profile-empty-state">No related companies have been listed yet.</div>@endif
            </section>

            @if($details->accuracy_cta_enabled ?? true)
            <div class="request-card">
                <i class="bi bi-people-fill text-primary fs-3"></i>
                <h3>{{ $details->accuracy_cta_title ?: 'Is This Your Company?' }}</h3>
                <p>{{ $details->accuracy_cta_description ?: 'Request an update to this company profile or help us improve the information.' }}</p>
                @if($details->accuracy_cta_button_label && $details->accuracy_cta_button_url)
                    <a class="btn btn-gold btn-sm" href="{{ $details->accuracy_cta_button_url }}" target="_blank" rel="noopener noreferrer">{{ $details->accuracy_cta_button_label }} <i class="bi bi-arrow-right ms-1"></i></a>
                @else
                    <a class="btn btn-gold btn-sm" href="{{ route('customer.register') }}">Request a Company Profile <i class="bi bi-arrow-right ms-1"></i></a>
                @endif
                @if($details->accuracy_cta_secondary_label && $details->accuracy_cta_secondary_url)
                    <a class="btn btn-outline-blue btn-sm mt-2" href="{{ $details->accuracy_cta_secondary_url }}" target="_blank" rel="noopener noreferrer">{{ $details->accuracy_cta_secondary_label }}</a>
                @endif
            </div>
            @endif
        </aside>
    </div>
</main>
@endsection
