@extends('website.master.master')

@php
    $version = $profile->publishedVersion;
    $visibleSections = $version->sections->where('is_visible', true)->values();
    $visibleLinks = $version->links->where('is_visible', true)->values();
    $visibleReferences = $version->references->values();

    $findSection = function (array $terms) use ($visibleSections) {
        return $visibleSections->first(function ($section) use ($terms) {
            $haystack = \Illuminate\Support\Str::lower($section->title . ' ' . $section->section_key);

            foreach ($terms as $term) {
                if (\Illuminate\Support\Str::contains($haystack, \Illuminate\Support\Str::lower($term))) {
                    return true;
                }
            }

            return false;
        });
    };

    $aboutSection = $findSection(['about']);
    $earlyLifeSection = $findSection(['early life', 'background']);
    $careerSection = $findSection(['career', 'entrepreneurship']);
    $impactSection = $findSection(['impact', 'achievement', 'recognition']);

    $personName = $details->full_name;
    $personTitle = $details->professional_title ?: ($details->headline ?: $details->current_position);
    $personBio = $details->short_bio ?: $details->about;
    $personPortrait = $version->mediaLinks->firstWhere('role', 'portrait')?->media?->url
        ?: asset('public/website/assets/nazim-founder.jpg');

    $personWebsite = $details->personal_website;
    $personWebsiteUrl = $personWebsite
        ? (\Illuminate\Support\Str::startsWith($personWebsite, ['http://', 'https://']) ? $personWebsite : 'https://' . $personWebsite)
        : null;

    $personRoles = collect([
        $details->occupation,
        $details->professional_title,
        $details->current_position,
    ])->filter()->unique()->take(3)->values();

    $organizationRelationship = $version->relationships
        ->where('is_visible', true)
        ->first(function ($relationship) {
            return $relationship->relatedProfile
                && in_array(\Illuminate\Support\Str::lower($relationship->relation_type), [
                    'founder', 'co_founder', 'co-founder', 'ceo', 'works_at',
                    'employee', 'director', 'partner', 'organization',
                ], true);
        });

    $organizationProfile = $organizationRelationship?->relatedProfile;
    $organizationVersion = $organizationProfile?->publishedVersion;
    $organizationDetails = $organizationProfile
        ? ($organizationProfile->profile_type === \App\Models\Profile::TYPE_COMPANY
            ? $organizationVersion?->companyDetails
            : $organizationVersion?->personalDetails)
        : null;
    $organizationName = $organizationDetails
        ? ($organizationProfile->profile_type === \App\Models\Profile::TYPE_COMPANY
            ? $organizationDetails->company_name
            : $organizationDetails->full_name)
        : $details->company_organisation;
    $organizationImage = $organizationVersion?->mediaLinks
        ?->firstWhere('role', $organizationProfile?->profile_type === \App\Models\Profile::TYPE_COMPANY ? 'logo' : 'portrait')
        ?->media?->url;

    $socialIcons = [
        'website' => 'bi-globe2',
        'facebook' => 'bi-facebook',
        'linkedin' => 'bi-linkedin',
        'youtube' => 'bi-youtube',
        'instagram' => 'bi-instagram',
        'twitter' => 'bi-twitter-x',
        'x' => 'bi-twitter-x',
        'email' => 'bi-envelope',
        'phone' => 'bi-telephone',
    ];

    $socialClasses = [
        'facebook' => 'social-facebook',
        'linkedin' => 'social-linkedin',
        'youtube' => 'social-youtube',
        'instagram' => 'social-instagram',
        'twitter' => 'social-x',
        'x' => 'social-x',
    ];

    $genericSections = $visibleSections->reject(function ($section) {
        $title = \Illuminate\Support\Str::lower($section->title . ' ' . $section->section_key);

        return \Illuminate\Support\Str::contains($title, [
            'about', 'early_life', 'early life', 'background',
            'career', 'entrepreneurship',
            'impact', 'achievement', 'recognition',
        ]);
    });
@endphp

@section('title')
    {{ $version->seo?->meta_title ?: ($personName . ' | ' . $siteSetting->site_name) }}
@endsection

@section('css')
    <link href="{{ asset('/') }}public/website/profile-pages.css" rel="stylesheet">
@endsection

@section('body')
<main id="top" class="individual-profile-layout">
    <section class="individual-intro" aria-labelledby="person-name">
        <img class="individual-portrait" src="{{ $personPortrait }}" alt="Portrait of {{ $personName }}">

        <div class="individual-intro-copy">
            <div class="individual-name-row">
                <h1 id="person-name">{{ $personName }}</h1>

                @if($profile->is_verified)
                    <span class="verified-large" title="Verified profile" aria-label="Verified profile">
                        <i class="bi bi-patch-check-fill"></i>
                    </span>
                @endif
            </div>

            @if($personTitle)
                <p class="individual-title">{{ $personTitle }}</p>
            @endif

            @if($personRoles->isNotEmpty())
                <div class="individual-roles">
                    @foreach($personRoles as $role)
                        @if(!$loop->first)<i>•</i>@endif
                        <span>{{ $role }}</span>
                    @endforeach
                </div>
            @endif

            @if($personBio)
                <p class="individual-bio">{{ $personBio }}</p>
            @endif

            <div class="individual-meta">
                @if($details->location)
                    <span><i class="bi bi-geo-alt"></i> {{ $details->location }}</span>
                @endif

                @if($organizationProfile)
                    <a href="{{ route('site.profile.show', $organizationProfile) }}">
                        <i class="bi bi-buildings"></i> {{ $organizationName }}
                    </a>
                @elseif($organizationName)
                    <span><i class="bi bi-buildings"></i> {{ $organizationName }}</span>
                @endif

                @if($personWebsiteUrl)
                    <a href="{{ $personWebsiteUrl }}" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-link-45deg"></i> {{ parse_url($personWebsiteUrl, PHP_URL_HOST) ?: $personWebsite }}
                    </a>
                @endif
            </div>
        </div>
    </section>

    @if($details->profile_quote)
        <aside class="quote-card">
            <span class="quote-mark">“</span>
            <blockquote>{{ $details->profile_quote }}</blockquote>
            <p>— {{ $personName }}</p>
        </aside>
    @endif

    <div class="individual-main">
        @php
            $aboutText = $aboutSection?->intro ?: $details->about;
        @endphp

        @if($aboutText || $aboutSection?->items?->where('is_visible', true)->isNotEmpty())
            <section class="profile-section">
                <h2 class="profile-section-title">About</h2>
                @if($aboutText)
                    <p class="section-paragraph">{!! nl2br(e($aboutText)) !!}</p>
                @endif

                @foreach($aboutSection?->items?->where('is_visible', true) ?? collect() as $item)
                    <article class="public-profile-item">
                        @if($item->image_path)
                            <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $personName }}">
                        @endif
                        @if($item->title)<h3>{{ $item->title }}</h3>@endif
                        @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif
                        @if($item->description)<p>{{ $item->description }}</p>@endif
                        @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">Learn more <i class="bi bi-box-arrow-up-right"></i></a>@endif
                    </article>
                @endforeach
            </section>
        @endif

        @php
            $earlyLifeText = $earlyLifeSection?->intro ?: $details->early_life;
            $earlyLifeItem = $earlyLifeSection?->items?->where('is_visible', true)->first();
            $earlyLifeImage = $earlyLifeItem?->image_path
                ? (\Illuminate\Support\Str::startsWith($earlyLifeItem->image_path, ['http://', 'https://'])
                    ? $earlyLifeItem->image_path
                    : asset('public/' . ltrim($earlyLifeItem->image_path, '/')))
                : asset('public/website/assets/nazim-early-life.jpg');
        @endphp

        @if($earlyLifeText || $earlyLifeSection?->items?->where('is_visible', true)->isNotEmpty())
            <section class="profile-section">
                <h2 class="profile-section-title">{{ $earlyLifeSection?->title ?: 'Early Life' }}</h2>

                <div class="individual-story-layout">
                    @if($earlyLifeText)
                        <p class="section-paragraph">{!! nl2br(e($earlyLifeText)) !!}</p>
                    @endif

                    @if($earlyLifeImage)
                        <img src="{{ $earlyLifeImage }}" alt="{{ $personName }} early life">
                    @endif
                </div>

                @foreach($earlyLifeSection?->items?->where('is_visible', true) ?? collect() as $item)
                    <article class="public-profile-item">
                        @if($item->image_path && $item->image_path !== $earlyLifeItem?->image_path)
                            <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $personName }}">
                        @endif
                        @if($item->title)<h3>{{ $item->title }}</h3>@endif
                        @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif
                        @if($item->description)<p>{!! nl2br(e($item->description)) !!}</p>@endif
                    </article>
                @endforeach
            </section>
        @endif

        @php
            $careerText = $careerSection?->intro ?: $details->career_entrepreneurship;
            $careerParagraphs = $careerText
                ? collect(preg_split('/\\R\\s*\\R/', trim($careerText)))->filter()
                : collect();
        @endphp

        @if($careerText || $careerSection?->items?->where('is_visible', true)->isNotEmpty())
            <section class="profile-section">
                <h2 class="profile-section-title">{{ $careerSection?->title ?: 'Career & Entrepreneurship' }}</h2>

                @foreach($careerParagraphs as $careerParagraph)
                    <p class="section-paragraph">{{ $careerParagraph }}</p>
                @endforeach

                @foreach($careerSection?->items?->where('is_visible', true) ?? collect() as $item)
                    <article class="public-profile-item">
                        @if($item->image_path)
                            <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $personName }}">
                        @endif
                        @if($item->title)<h3>{{ $item->title }}</h3>@endif
                        @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif
                        @if($item->description)<p>{!! nl2br(e($item->description)) !!}</p>@endif
                        @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">Learn more <i class="bi bi-box-arrow-up-right"></i></a>@endif
                    </article>
                @endforeach
            </section>
        @endif

        @if($organizationName)
            <section class="profile-section">
                <h2 class="profile-section-title">{{ $organizationName }}</h2>

                <article class="individual-company-card">
                    @if($organizationImage)
                        <img src="{{ $organizationImage }}" alt="{{ $organizationName }}">
                    @endif

                    <div class="individual-company-copy">
                        <h3>
                            @if($organizationProfile)
                                <a href="{{ route('site.profile.show', $organizationProfile) }}">{{ $organizationName }}</a>
                            @else
                                {{ $organizationName }}
                            @endif
                        </h3>

                        @if($organizationRelationship?->description)
                            <p>{{ $organizationRelationship->description }}</p>
                        @elseif($organizationDetails)
                            <p>
                                {{ $organizationProfile->profile_type === \App\Models\Profile::TYPE_COMPANY
                                    ? ($organizationDetails->short_description ?? $organizationDetails->tagline)
                                    : ($organizationDetails->short_bio ?? $organizationDetails->professional_title) }}
                            </p>
                        @endif
                    </div>

                    @if($organizationProfile && $organizationProfile->profile_type === \App\Models\Profile::TYPE_COMPANY && $organizationDetails?->official_website)
                        @php
                            $organizationWebsite = \Illuminate\Support\Str::startsWith($organizationDetails->official_website, ['http://', 'https://'])
                                ? $organizationDetails->official_website
                                : 'https://' . $organizationDetails->official_website;
                        @endphp

                        <a class="visit-website-btn" href="{{ $organizationWebsite }}" target="_blank" rel="noopener noreferrer">
                            Visit Website <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    @elseif($organizationProfile)
                        <a class="visit-website-btn" href="{{ route('site.profile.show', $organizationProfile) }}">
                            View Profile <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
                </article>
            </section>
        @endif

        @if($impactSection || $details->awards_publications_recognition)
            <section class="profile-section">
                <h2 class="profile-section-title">{{ $impactSection?->title ?: 'Impact' }}</h2>

                @if($impactSection?->intro ?: $details->awards_publications_recognition)
                    <p class="section-paragraph">{!! nl2br(e($impactSection?->intro ?: $details->awards_publications_recognition)) !!}</p>
                @endif

                @if($impactSection?->items?->where('is_visible', true)->isNotEmpty())
                    <div class="individual-impact-grid">
                        @foreach($impactSection->items->where('is_visible', true) as $item)
                            <article class="individual-impact-card">
                                @if($item->image_path)
                                    <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $personName }}">
                                @endif
                                @if($item->icon)<span><i class="bi {{ $item->icon }}"></i></span>@endif
                                @if($item->title)<h3>{{ $item->title }}</h3>@endif
                                @if($item->description)<p>{{ $item->description }}</p>@endif
                                @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">Learn more <i class="bi bi-box-arrow-up-right"></i></a>@endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @foreach($genericSections as $section)
            @if($section->intro || $section->items->where('is_visible', true)->isNotEmpty())
                <section class="profile-section">
                    <h2 class="profile-section-title">{{ $section->title }}</h2>

                    @if($section->intro)
                        <p class="section-paragraph">{!! nl2br(e($section->intro)) !!}</p>
                    @endif

                    @foreach($section->items->where('is_visible', true) as $item)
                        <article class="public-profile-item">
                            @if($item->image_path)
                                <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $personName }}">
                            @endif
                            @if($item->title)<h3>{{ $item->title }}</h3>@endif
                            @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif
                            @if($item->description)<p>{!! nl2br(e($item->description)) !!}</p>@endif
                            @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">Learn more <i class="bi bi-box-arrow-up-right"></i></a>@endif
                        </article>
                    @endforeach
                </section>
            @endif
        @endforeach

        @if($visibleReferences->isNotEmpty())
            <section class="profile-section individual-references">
                <h2 class="profile-section-title">References</h2>

                <ol class="individual-reference-list">
                    @foreach($visibleReferences as $reference)
                        <li>
                            @if($reference->url)
                                <a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer">
                                    {{ $reference->title }}
                                    @if($reference->publisher) — {{ $reference->publisher }} @endif
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @else
                                <span>{{ $reference->title }}@if($reference->publisher) — {{ $reference->publisher }} @endif</span>
                            @endif
                            @if($reference->source_date)<small>{{ $reference->source_date->format('M j, Y') }}</small>@endif
                            @if($reference->description)<p>{{ $reference->description }}</p>@endif
                        </li>
                    @endforeach

                </ol>
                <p class="source-note">References and external links are included to help readers review the information in this profile.</p>
            </section>
        @endif
    </div>

    <aside class="individual-sidebar">
        <section class="sidebar-card">
            <h2 class="sidebar-title sidebar-title-bar">Profile Summary</h2>

            <dl class="summary-list">
                @if($details->show_date_of_birth && $details->date_of_birth)
                    <div>
                        <dt><i class="bi bi-calendar-event"></i> Date of Birth</dt>
                        <dd>{{ $details->date_of_birth->format('F j, Y') }}</dd>
                    </div>
                @endif

                @if($details->nationality)
                    <div><dt><i class="bi bi-flag-fill flag-bangladesh"></i> Nationality</dt><dd>{{ $details->nationality }}</dd></div>
                @endif

                @if($details->occupation || $personTitle)
                    <div><dt><i class="bi bi-briefcase"></i> Occupation</dt><dd>{{ $details->occupation ?: $personTitle }}</dd></div>
                @endif

                @if($organizationName)
                    <div>
                        <dt><i class="bi bi-buildings"></i> Organization</dt>
                        <dd>
                            @if($organizationProfile)
                                <a href="{{ route('site.profile.show', $organizationProfile) }}">{{ $organizationName }}</a>
                            @else
                                {{ $organizationName }}
                            @endif
                        </dd>
                    </div>
                @endif

                @if($details->hometown)
                    <div><dt><i class="bi bi-house"></i> Hometown</dt><dd>{{ $details->hometown }}</dd></div>
                @endif

                @if($details->education_summary)
                    <div><dt><i class="bi bi-mortarboard"></i> Education</dt><dd>{{ \Illuminate\Support\Str::limit(strip_tags($details->education_summary), 140) }}</dd></div>
                @endif

                @if($personWebsiteUrl)
                    <div>
                        <dt><i class="bi bi-globe2"></i> Website</dt>
                        <dd>
                            <a href="{{ $personWebsiteUrl }}" target="_blank" rel="noopener noreferrer">
                                {{ parse_url($personWebsiteUrl, PHP_URL_HOST) ?: $personWebsite }}
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </section>

        @if($visibleLinks->isNotEmpty() || $personWebsiteUrl || ($details->show_public_email && $details->public_contact_email) || ($details->show_public_phone && $details->public_phone))
            <section class="sidebar-card">
                <h2 class="sidebar-title sidebar-title-bar">External Links</h2>

                <ul class="external-links-list">
                    @if($personWebsiteUrl && !$visibleLinks->contains('link_type', 'website'))
                        <li>
                            <i class="bi bi-globe2 link-website"></i>
                            <span>Website</span>
                            <a href="{{ $personWebsiteUrl }}" target="_blank" rel="noopener noreferrer">{{ parse_url($personWebsiteUrl, PHP_URL_HOST) ?: $personWebsite }}</a>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </li>
                    @endif

                    @if($details->show_public_email && $details->public_contact_email)
                        <li>
                            <i class="bi bi-envelope"></i>
                            <span>Email</span>
                            <a href="mailto:{{ $details->public_contact_email }}">{{ $details->public_contact_email }}</a>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </li>
                    @endif

                    @if($details->show_public_phone && $details->public_phone)
                        <li>
                            <i class="bi bi-telephone"></i>
                            <span>Phone</span>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $details->public_phone) }}">{{ $details->public_phone }}</a>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </li>
                    @endif

                    @foreach($visibleLinks as $link)
                        @php
                            $linkType = \Illuminate\Support\Str::lower($link->link_type);
                            $icon = $link->icon ?: ($socialIcons[$linkType] ?? 'bi-link-45deg');
                            $linkUrl = \Illuminate\Support\Str::startsWith($link->url, ['http://', 'https://', 'mailto:', 'tel:'])
                                ? $link->url
                                : 'https://' . $link->url;
                        @endphp

                        <li>
                            <i class="bi {{ $icon }} {{ $socialClasses[$linkType] ?? 'link-website' }}"></i>
                            <span>{{ $link->label ?: ucfirst($linkType) }}</span>
                            <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer">
                                {{ parse_url($linkUrl, PHP_URL_HOST) ?: $link->url }}
                            </a>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="sidebar-suggest-card entrepreneur-cta">
            <span class="suggest-icon"><i class="bi bi-person-plus-fill"></i></span>
            <h2>Are You an Entrepreneur?</h2>
            <p>Submit your professional information for editorial review and become part of {{ $siteSetting->site_name }}.</p>
            <a href="{{ route('site.home') }}#submit-profile" class="btn btn-primary">
                Request Your Profile <i class="bi bi-arrow-right"></i>
            </a>
        </section>
    </aside>
</main>
@endsection

@section('scripts')
@endsection
