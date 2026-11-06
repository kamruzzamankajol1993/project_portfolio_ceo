@extends('website.master.master')

@php
    $version = $profile->publishedVersion;
    $visibleSections = $version->sections->where('is_visible', true)->values();
    $visibleLinks = $version->links->where('is_visible', true)->values();
    $companySocialLinks = $visibleLinks->whereIn('link_type', ['facebook', 'linkedin', 'youtube', 'instagram'])->values();
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
    $historySection = $findSection(['history', 'background']);
    $servicesSection = $findSection(['services', 'operations']);
    $leadershipSection = $findSection(['leadership', 'founder']);
    $impactSection = $findSection(['impact', 'achievement']);

    $companyName = $details->company_name;
    $accuracyCtaEnabled = $details->accuracy_cta_enabled ?? true;
    $accuracyCtaTitle = $details->accuracy_cta_title ?: 'Know This Company?';
    $accuracyCtaDescription = $details->accuracy_cta_description ?: 'Help us keep this information accurate. You can suggest an edit or request a company profile.';
    $accuracyCtaPrimaryLabel = $details->accuracy_cta_button_label ?: 'Suggest an Edit';
    $accuracyCtaPrimaryUrl = route('site.contact-us');
    $accuracyCtaSecondaryLabel = $details->accuracy_cta_secondary_label ?: 'Request a Company Profile';
    $accuracyCtaSecondaryUrl = $details->accuracy_cta_secondary_url ?: route('customer.register');
    $companyLogo = $version->mediaLinks->firstWhere('role', 'logo')?->media?->url
        ?: asset('public/website/assets/company-logo.jpg');
    $companyBanner = $version->mediaLinks->firstWhere('role', 'banner')?->media?->url;
    $companyAbout = $aboutSection?->intro ?: $details->overview ?: $details->short_description;
    $companyHistory = $historySection?->intro ?: $details->history_background;

    $companyWebsite = $details->official_website;
    $companyWebsiteUrl = $companyWebsite
        ? (\Illuminate\Support\Str::startsWith($companyWebsite, ['http://', 'https://']) ? $companyWebsite : 'https://' . $companyWebsite)
        : null;

    $founderRelationships = $version->relationships
        ->where('is_visible', true)
        ->filter(function ($relationship) {
            $type = \Illuminate\Support\Str::lower(str_replace(['-', ' '], '_', $relationship->relation_type));

            return \Illuminate\Support\Str::contains($type, ['founder', 'co_founder', 'ceo', 'director', 'leader', 'president']);
        })
        ->values();

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
            'about', 'history', 'background', 'service', 'operation',
            'leadership', 'founder', 'impact', 'achievement',
        ]);
    });
@endphp

@section('title')
    {{ $version->seo?->meta_title ?: ($companyName . ' | ' . $siteSetting->site_name) }}
@endsection

@section('css')
    <link href="{{ asset('/') }}public/website/profile-pages.css" rel="stylesheet">
@endsection

@section('body')
<main id="top">
    <section
        class="company-hero"
        aria-labelledby="company-title"
        @if($companyBanner)
            style="background-image: linear-gradient(90deg, rgba(246,250,255,.99) 0%, rgba(246,250,255,.97) 48%, rgba(246,250,255,.76) 64%, rgba(246,250,255,.05) 100%), url('{{ $companyBanner }}');"
        @endif
    >
        <div class="company-hero-inner">
            <div class="company-logo-card">
                <img src="{{ $companyLogo }}" alt="{{ $companyName }} logo">
            </div>

            <div class="company-hero-copy">
                <div class="company-title-row">
                    <h1 id="company-title">{{ $companyName }}</h1>

                    @if($profile->is_verified)
                        <span class="verified-large" title="Verified company" aria-label="Verified company">
                            <i class="bi bi-patch-check-fill"></i>
                        </span>
                    @endif
                </div>

                @if($details->tagline)
                    <p class="company-tagline">{{ $details->tagline }}</p>
                @endif

                @if($details->short_description)
                    <p class="company-summary">{{ $details->short_description }}</p>
                @endif

                <div class="company-meta">
                    @if($details->headquarters)
                        <span><i class="bi bi-geo-alt"></i> {{ $details->headquarters }}</span>
                    @endif

                    @if($companyWebsiteUrl)
                        <a href="{{ $companyWebsiteUrl }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-globe2"></i> Official Website
                            <i class="bi bi-box-arrow-up-right external-icon"></i>
                        </a>
                    @endif

                    @if($companySocialLinks->isNotEmpty())
                        <div class="company-socials" aria-label="Company social links">
                            @foreach($companySocialLinks as $link)
                                @php
                                    $linkType = \Illuminate\Support\Str::lower($link->link_type);
                                    $icon = $link->icon ?: ($socialIcons[$linkType] ?? 'bi-link-45deg');
                                    $socialClass = $socialClasses[$linkType] ?? '';
                                    $linkUrl = \Illuminate\Support\Str::startsWith($link->url, ['http://', 'https://', 'mailto:', 'tel:'])
                                        ? $link->url
                                        : 'https://' . $link->url;
                                @endphp

                                <a
                                    class="{{ $socialClass }}"
                                    href="{{ $linkUrl }}"
                                    aria-label="{{ $link->label ?: ucfirst($linkType) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <i class="bi {{ $icon }}"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="profile-main-shell company-content-layout">
        <div class="company-main-column">
            @if($companyAbout || $aboutSection?->items?->where('is_visible', true)->isNotEmpty())
                <section class="profile-section" id="company-about">
                    <h2 class="profile-section-title">About</h2>
                    @if($companyAbout)
                        <p class="section-paragraph">{!! nl2br(e($companyAbout)) !!}</p>
                    @endif

                    @if($aboutSection?->items?->where('is_visible', true)->isNotEmpty())
                        <div class="service-pills">
                            @foreach($aboutSection->items->where('is_visible', true) as $item)
                                <div class="service-pill">
                                    @if($item->image_path)
                                        <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $companyName }}">
                                    @endif
                                    <span><i class="bi {{ $item->icon ?: 'bi-check-circle' }}"></i></span>
                                    <div>
                                        @if($item->title)<strong>{{ $item->title }}</strong>@endif
                                        @if($item->subtitle ?: $item->description)
                                            <small>{{ $item->subtitle ?: $item->description }}</small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            @if($companyHistory || $historySection?->items?->where('is_visible', true)->isNotEmpty())
                <section class="profile-section">
                    <h2 class="profile-section-title">History &amp; Background</h2>

                    <div class="history-layout">
                        @if($companyHistory)
                            <p class="section-paragraph">{!! nl2br(e($companyHistory)) !!}</p>
                        @endif

                        @php
                            $historyImagePath = $historySection?->items
                                ->where('is_visible', true)
                                ->firstWhere('image_path')?->image_path;

                            $historyImage = $historyImagePath
                                ? (\Illuminate\Support\Str::startsWith($historyImagePath, ['http://', 'https://'])
                                    ? $historyImagePath
                                    : asset('public/' . ltrim($historyImagePath, '/')))
                                : ($companyBanner ?: asset('public/website/assets/company-history.jpg'));
                        @endphp

                        @if($historyImage)
                            <img src="{{ $historyImage }}" alt="{{ $companyName }} history and background">
                        @endif
                    </div>

                    @foreach($historySection?->items?->where('is_visible', true) ?? collect() as $item)
                        <article class="public-profile-item">
                            @if($item->image_path && $item->image_path !== $historyImagePath)
                                <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $companyName }}">
                            @endif
                            @if($item->title)<h3>{{ $item->title }}</h3>@endif
                            @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif
                            @if($item->description)<p>{!! nl2br(e($item->description)) !!}</p>@endif
                        </article>
                    @endforeach
                </section>
            @endif

            @if($servicesSection && ($servicesSection->intro || $servicesSection->items->where('is_visible', true)->isNotEmpty()))
                <section class="profile-section">
                    <h2 class="profile-section-title">{{ $servicesSection->title ?: 'Services & Operations' }}</h2>

                    @if($servicesSection->intro)
                        <p class="section-paragraph section-intro">{!! nl2br(e($servicesSection->intro)) !!}</p>
                    @endif

                    <div class="operation-grid">
                        @foreach($servicesSection->items->where('is_visible', true) as $item)
                            <article class="operation-card">
                                @if($item->image_path)
                                    <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $companyName }}">
                                @endif
                                <span class="operation-icon">
                                    <i class="bi {{ $item->icon ?: 'bi-check2-circle' }}"></i>
                                </span>

                                <div>
                                    @if($item->title)
                                        <h3>{{ $item->title }}</h3>
                                    @endif

                                    @if($item->description)
                                        <p>{!! nl2br(e($item->description)) !!}</p>
                                    @endif

                                    @if($item->url)
                                        <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">
                                            Learn more <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($founderRelationships->isNotEmpty() || ($leadershipSection && ($leadershipSection->intro || $leadershipSection->items->where('is_visible', true)->isNotEmpty())))
                <section class="profile-section">
                    <h2 class="profile-section-title">
                        {{ $leadershipSection?->title ?: 'Leadership & Founders' }}
                    </h2>

                    @if($leadershipSection?->intro)
                        <p class="section-paragraph">{{ $leadershipSection->intro }}</p>
                    @endif

                    @foreach($founderRelationships as $relationship)
                        @php
                            $leader = $relationship->relatedProfile;
                            $leaderVersion = $leader?->publishedVersion;
                            $leaderDetails = $leader?->profile_type === \App\Models\Profile::TYPE_PERSONAL
                                ? $leaderVersion?->personalDetails
                                : $leaderVersion?->companyDetails;
                            $leaderName = $leaderDetails
                                ? ($leader?->profile_type === \App\Models\Profile::TYPE_PERSONAL
                                    ? $leaderDetails->full_name
                                    : $leaderDetails->company_name)
                                : null;
                            $leaderImage = $leaderVersion?->mediaLinks
                                ?->firstWhere('role', $leader?->profile_type === \App\Models\Profile::TYPE_PERSONAL ? 'portrait' : 'logo')
                                ?->media?->url;
                        @endphp

                        @if($leader && $leaderName)
                            <article class="founder-card">
                                @if($leaderImage)
                                    <img src="{{ $leaderImage }}" alt="{{ $leaderName }}">
                                @endif

                                <div class="founder-copy">
                                    <h3>
                                        {{ $leaderName }}

                                        @if($leader->is_verified)
                                            <i class="bi bi-patch-check-fill verified"></i>
                                        @endif
                                    </h3>

                                    <p class="founder-role">
                                        {{ $relationship->position ?: ucfirst(str_replace('_', ' ', $relationship->relation_type)) }}
                                    </p>

                                    @if($relationship->description)
                                        <p>{{ $relationship->description }}</p>
                                    @endif

                                    <a href="{{ route('site.profile.show', $leader) }}" class="inline-link">
                                        View Profile <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        @endif
                    @endforeach

                    @if($founderRelationships->isEmpty() && $leadershipSection)
                        @foreach($leadershipSection->items->where('is_visible', true) as $item)
                            <article class="founder-card">
                                @if($item->image_path)
                                    <img
                                        src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://', 'https://'])
                                            ? $item->image_path
                                            : asset('public/' . ltrim($item->image_path, '/')) }}"
                                        alt="{{ $item->title }}"
                                    >
                                @endif

                                <div class="founder-copy">
                                    <h3>{{ $item->title }}</h3>
                                    @if($item->subtitle)<p class="founder-role">{{ $item->subtitle }}</p>@endif
                                    @if($item->description)<p>{{ $item->description }}</p>@endif
                                    @if($item->url)
                                        <a href="{{ $item->url }}" class="inline-link" target="_blank" rel="noopener noreferrer">
                                            View Profile <i class="bi bi-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    @endif
                </section>
            @endif

            @if($impactSection && ($impactSection->intro || $impactSection->items->where('is_visible', true)->isNotEmpty()))
                <section class="profile-section">
                    <h2 class="profile-section-title">{{ $impactSection->title ?: 'Impact & Achievements' }}</h2>

                    @if($impactSection->intro)
                        <p class="section-paragraph">{{ $impactSection->intro }}</p>
                    @endif

                    <div class="company-impact-grid">
                        @foreach($impactSection->items->where('is_visible', true) as $item)
                            <article class="company-impact-card">
                                @if($item->image_path)
                                    <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $companyName }}">
                                @endif
                                @if($item->icon)<i class="bi {{ $item->icon }}"></i>@endif
                                @if($item->title)<strong>{{ $item->title }}</strong>@endif
                                @if($item->subtitle)<span>{{ $item->subtitle }}</span>@endif
                                @if($item->description)<small>{{ $item->description }}</small>@endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($visibleReferences->isNotEmpty() || $visibleLinks->isNotEmpty())
                <section class="profile-section references-section">
                    <h2 class="profile-section-title">References &amp; External Links</h2>

                    <ul class="reference-list">
                        @foreach($visibleReferences as $reference)
                            <li>
                                <i class="bi bi-file-earmark-text"></i>
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

                        @foreach($visibleLinks as $link)
                            @php
                                $linkType = \Illuminate\Support\Str::lower($link->link_type);
                                $icon = $link->icon ?: ($socialIcons[$linkType] ?? 'bi-link-45deg');
                                $linkUrl = \Illuminate\Support\Str::startsWith($link->url, ['http://', 'https://', 'mailto:', 'tel:'])
                                    ? $link->url
                                    : 'https://' . $link->url;
                            @endphp

                            <li>
                                <i class="bi {{ $icon }}"></i>
                                <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer">
                                    {{ $link->label ?: ucfirst($linkType) }}
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
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
                                    <img class="profile-section-item-image" src="{{ \Illuminate\Support\Str::startsWith($item->image_path, ['http://','https://']) ? $item->image_path : asset('public/' . ltrim($item->image_path, '/')) }}" alt="{{ $item->title ?: $companyName }}">
                                @endif
                                @if($item->title)<h3>{{ $item->title }}</h3>@endif
                                @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif
                                @if($item->description)<p>{!! nl2br(e($item->description)) !!}</p>@endif
                                @if($item->url)
                                    <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">
                                        Learn more <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </section>
                @endif
            @endforeach
        </div>

        <aside class="company-sidebar">
            <section class="sidebar-card company-details-card">
                <h2 class="sidebar-title"><i class="bi bi-buildings-fill"></i>Company Details</h2>

                <dl class="details-list">
                    <div><dt>Name</dt><dd>{{ $companyName }}</dd></div>
                    @if($details->business_type)<div><dt>Type</dt><dd>{{ $details->business_type }}</dd></div>@endif
                    @if($details->founded_year)<div><dt>Founded</dt><dd>{{ $details->founded_year }}</dd></div>@endif

                    @foreach($founderRelationships as $relationship)
                        @if($relationship->relatedProfile)
                            @php
                                $founderProfile = $relationship->relatedProfile;
                                $founderDetails = $founderProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL
                                    ? $founderProfile->publishedVersion?->personalDetails
                                    : $founderProfile->publishedVersion?->companyDetails;
                                $founderName = $founderProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL
                                    ? $founderDetails?->full_name
                                    : $founderDetails?->company_name;
                            @endphp

                            @if($founderName)
                                <div>
                                    <dt>{{ \Illuminate\Support\Str::contains($relationship->relation_type, 'founder') ? 'Founders' : ucfirst(str_replace('_', ' ', $relationship->relation_type)) }}</dt>
                                    <dd><a href="{{ route('site.profile.show', $founderProfile) }}">{{ $founderName }}</a></dd>
                                </div>
                            @endif
                        @endif
                    @endforeach

                    @if($details->headquarters)<div><dt>Headquarters</dt><dd>{{ $details->headquarters }}</dd></div>@endif
                    @if($details->industry)<div><dt>Industry</dt><dd>{{ $details->industry->name }}</dd></div>@endif
                    @if($details->area_served)<div><dt>Area Served</dt><dd>{{ $details->area_served }}</dd></div>@endif
                    @if($details->parent_company)<div><dt>Parent Company</dt><dd>{{ $details->parent_company }}</dd></div>@endif

                    @if($companyWebsiteUrl)
                        <div>
                            <dt>Website</dt>
                            <dd>
                                <a href="{{ $companyWebsiteUrl }}" target="_blank" rel="noopener noreferrer">
                                    {{ parse_url($companyWebsiteUrl, PHP_URL_HOST) ?: $companyWebsite }}
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>

            @if($visibleLinks->isNotEmpty() || $companyWebsiteUrl || ($details->show_public_email && $details->public_email) || ($details->show_public_phone && $details->public_phone))
                <section class="sidebar-card connect-card">
                    <h2 class="sidebar-title"><i class="bi bi-share-fill"></i>Connect</h2>

                    <ul class="connect-list">
                        @if($companyWebsiteUrl && !$visibleLinks->contains('link_type', 'website'))
                            <li>
                                <i class="bi bi-globe2 connect-icon"></i>
                                <span>Official Website</span>
                                <a href="{{ $companyWebsiteUrl }}" target="_blank" rel="noopener noreferrer">{{ parse_url($companyWebsiteUrl, PHP_URL_HOST) ?: $companyWebsite }}</a>
                                <i class="bi bi-box-arrow-up-right external-icon"></i>
                            </li>
                        @endif

                        @if($details->show_public_email && $details->public_email)
                            <li>
                                <i class="bi bi-envelope connect-icon"></i>
                                <span>Email</span>
                                <a href="mailto:{{ $details->public_email }}">{{ $details->public_email }}</a>
                            </li>
                        @endif

                        @if($details->show_public_phone && $details->public_phone)
                            <li>
                                <i class="bi bi-telephone connect-icon"></i>
                                <span>Phone</span>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $details->public_phone) }}">{{ $details->public_phone }}</a>
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
                                <i class="bi {{ $icon }} {{ $socialClasses[$linkType] ?? 'connect-icon' }}"></i>
                                <span>{{ $link->label ?: ucfirst($linkType) }}</span>
                                <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer">{{ $link->label ?: parse_url($linkUrl, PHP_URL_HOST) ?: $link->url }}</a>
                                <i class="bi bi-box-arrow-up-right external-icon"></i>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if($accuracyCtaEnabled)
                <section class="sidebar-suggest-card">
                    <span class="suggest-icon"><i class="bi bi-person-plus-fill"></i></span>
                    <h2>{{ $accuracyCtaTitle }}</h2>
                    <p>{{ $accuracyCtaDescription }}</p>
                    @if($accuracyCtaPrimaryLabel)
                        <a href="{{ $accuracyCtaPrimaryUrl }}" class="btn btn-primary">
                            {{ $accuracyCtaPrimaryLabel }} <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
                    @if($accuracyCtaSecondaryLabel)
                        <a href="{{ $accuracyCtaSecondaryUrl }}" class="btn btn-outline-primary mt-2">
                            {{ $accuracyCtaSecondaryLabel }} <i class="bi bi-arrow-right"></i>
                        </a>
                    @endif
                </section>
            @endif
        </aside>
    </div>
</main>
@endsection

@section('scripts')
@endsection
