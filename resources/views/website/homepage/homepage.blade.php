@extends('website.master.master')

@section('title')
    CEOWiki — Discover the World's Business Leaders
@endsection

@section('body_class', 'home-page')

@section('body')
@php
    $heroProfile = $homepageHeroProfiles->first();
    $heroIsPerson = $heroProfile && $heroProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL;
    $heroDetails = $heroProfile
        ? ($heroIsPerson ? $heroProfile->publishedVersion?->personalDetails : $heroProfile->publishedVersion?->companyDetails)
        : null;
    $heroName = $heroDetails
        ? ($heroIsPerson ? $heroDetails->full_name : $heroDetails->company_name)
        : null;
    $heroRole = $heroDetails
        ? ($heroIsPerson
            ? ($heroDetails->professional_title ?: ($heroDetails->headline ?: $heroDetails->current_position))
            : ($heroDetails->tagline ?: $heroDetails->business_type))
        : null;
    $heroDescription = $heroDetails
        ? ($heroIsPerson ? ($heroDetails->short_bio ?: $heroDetails->about) : ($heroDetails->short_description ?: $heroDetails->overview))
        : '';
    $statDefaults = [
        ['value' => ($homepageStats['ceos'] ?? 0) > 0 ? number_format($homepageStats['ceos']) . '+' : '0', 'label' => 'CEOs', 'caption' => 'Published profiles'],
        ['value' => ($homepageStats['companies'] ?? 0) > 0 ? number_format($homepageStats['companies']) . '+' : '0', 'label' => 'Companies', 'caption' => 'With CEO information'],
        ['value' => ($homepageStats['countries'] ?? 0) > 0 ? number_format($homepageStats['countries']) . '+' : '0', 'label' => 'Countries', 'caption' => 'Global coverage'],
        ['value' => ($homepageStats['industries'] ?? 0) > 0 ? number_format($homepageStats['industries']) . '+' : '0', 'label' => 'Industries', 'caption' => 'Business sectors'],
    ];
    $countryRegionMap = [
        'united states' => 'north-america', 'united states of america' => 'north-america', 'usa' => 'north-america', 'canada' => 'north-america', 'mexico' => 'north-america',
        'united kingdom' => 'europe', 'uk' => 'europe', 'germany' => 'europe', 'france' => 'europe', 'italy' => 'europe', 'spain' => 'europe', 'netherlands' => 'europe', 'switzerland' => 'europe', 'sweden' => 'europe',
        'bangladesh' => 'asia', 'india' => 'asia', 'pakistan' => 'asia', 'china' => 'asia', 'japan' => 'asia', 'south korea' => 'asia', 'singapore' => 'asia', 'malaysia' => 'asia', 'indonesia' => 'asia', 'united arab emirates' => 'asia', 'uae' => 'asia', 'saudi arabia' => 'asia',
        'brazil' => 'south-america', 'argentina' => 'south-america', 'chile' => 'south-america', 'colombia' => 'south-america', 'peru' => 'south-america',
        'nigeria' => 'africa', 'south africa' => 'africa', 'egypt' => 'africa', 'kenya' => 'africa', 'ghana' => 'africa', 'morocco' => 'africa',
        'australia' => 'oceania', 'new zealand' => 'oceania', 'fiji' => 'oceania',
    ];
    $regionLabels = [
        'north-america' => 'North America',
        'europe' => 'Europe',
        'asia' => 'Asia',
        'south-america' => 'South America',
        'africa' => 'Africa',
        'oceania' => 'Oceania',
    ];
    $mapCountryStats = collect(array_fill_keys(array_keys($regionLabels), 0));
    foreach ($homepageCountryStats as $country => $count) {
        $region = $countryRegionMap[strtolower(trim($country))] ?? 'asia';
        $mapCountryStats->put($region, (int) $mapCountryStats->get($region, 0) + (int) $count);
    }
@endphp

<main>
    @if($heroSettings['hero_enabled'] ?? true)
    <section class="home-hero">
        <div class="home-hero-inner">
            <div class="home-hero-copy">
                <div class="eyebrow">{{ $heroSettings['hero_eyebrow'] ?? 'People • Companies • Leadership • Global Impact' }}</div>
                @php
                    $heroTitle = (string) ($heroSettings['hero_title'] ?? "Discover the\nWorld’s Business\nLeaders.");
                    $heroHighlightOffset = stripos($heroTitle, 'Business');
                @endphp
                <h1>
                    @if($heroHighlightOffset !== false)
                        {!! nl2br(e(substr($heroTitle, 0, $heroHighlightOffset))) !!}<span>{!! nl2br(e(substr($heroTitle, $heroHighlightOffset))) !!}</span>
                    @else
                        {!! nl2br(e($heroTitle)) !!}
                    @endif
                </h1>
                <p class="hero-lede">{{ $heroSettings['hero_description'] ?? 'Search and explore detailed profiles of CEOs, founders and the companies they lead. Accurate. Neutral. Global.' }}</p>

                <form class="hero-search site-search-box" role="search" aria-label="Search profiles" data-ajax-search data-search-url="{{ route('site.search') }}" data-results-id="home-search-results">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input aria-label="Search profiles" name="q" autocomplete="off" placeholder="{{ $searchSettings['site_search_placeholder'] ?? 'Search for a CEO, company, or keyword...' }}" type="search">
                    <button class="btn btn-gold" type="submit">{{ $heroSettings['hero_search_button_text'] ?? 'Search' }}</button>
                    <div id="home-search-results" class="search-results-dropdown" role="listbox" aria-label="Search results" hidden></div>
                </form>

                <div class="hero-filters" aria-label="Profile type shortcuts">
                    <a class="hero-filter active" href="{{ route('site.profiles.index') }}">All</a>
                    <a class="hero-filter" href="{{ route('site.entrepreneurs.index') }}"><i class="bi bi-people-fill me-1"></i> CEOs</a>
                    <a class="hero-filter" href="{{ route('site.companies.index') }}"><i class="bi bi-buildings me-1"></i> Companies</a>
                </div>

                @php
                    $popularSearches = $heroSettings['hero_popular_searches'] ?? [];
                    if (is_string($popularSearches)) {
                        $popularSearches = preg_split('/\r\n|\r|\n|,/', $popularSearches);
                    }
                @endphp
                @if(is_array($popularSearches) && count($popularSearches))
                <div class="popular-searches">
                    <strong>{{ $heroSettings['hero_popular_label'] ?? 'Popular searches:' }}</strong>
                    @foreach($popularSearches as $popularTerm)
                        @if(trim((string) $popularTerm) !== '')
                            <a href="{{ route('site.profiles.index', ['q' => trim($popularTerm)]) }}">{{ trim($popularTerm) }}</a>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>

            <div class="home-hero-visual" aria-label="Featured CEO and global business innovation">
                <div class="home-hero-art">
                @if($heroProfile && $heroDetails)
                <article class="hero-feature-card" id="hero-feature-card" aria-label="Featured profile">
                    <div class="hero-feature-eyebrow">FEATURED {{ $heroIsPerson ? 'CEO' : 'COMPANY' }}</div>
                    <h2><span>{{ $heroName }}</span> @if($heroProfile->is_verified)<i class="bi bi-patch-check-fill" aria-label="Verified"></i>@endif</h2>
                    <p class="hero-feature-role">{{ $heroRole ?: ($heroIsPerson ? 'Business Leader' : 'Company Profile') }}</p>
                    <p class="hero-feature-description">{{ \Illuminate\Support\Str::limit(strip_tags((string) $heroDescription), 105) }}</p>
                    <a class="hero-feature-button" href="{{ route('site.profile.show', $heroProfile) }}">View Full Profile <i class="bi bi-arrow-right"></i></a>
                </article>
                @else
                <article class="hero-feature-card" id="hero-feature-card">
                    <div class="hero-feature-eyebrow">FEATURED PROFILE</div>
                    <h2><span>{{ $siteSetting->site_name }}</span></h2>
                    <p class="hero-feature-role">People • Companies • Leadership</p>
                    <p class="hero-feature-description">Explore verified profiles and the stories behind the world's business leaders.</p>
                    <a class="hero-feature-button" href="{{ route('site.profiles.index') }}">Explore Profiles <i class="bi bi-arrow-right"></i></a>
                </article>
                @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    @if($statisticsSettings['stats_enabled'] ?? true)
    <section class="stats-strip">
        <div class="container-xl">
            <div class="stats-grid">
                @for($i = 1; $i <= 4; $i++)
                    @php
                        $fallback = $statDefaults[$i - 1];
                        $value = filled($statisticsSettings['stat_'.$i.'_value'] ?? null) ? $statisticsSettings['stat_'.$i.'_value'] : $fallback['value'];
                        $label = filled($statisticsSettings['stat_'.$i.'_label'] ?? null) ? $statisticsSettings['stat_'.$i.'_label'] : $fallback['label'];
                        $caption = filled($statisticsSettings['stat_'.$i.'_caption'] ?? null) ? $statisticsSettings['stat_'.$i.'_caption'] : $fallback['caption'];
                    @endphp
                    <div class="stat-item">
                        <div class="stat-icon">
                            @if(!empty($statisticsSettings['stat_'.$i.'_icon']))
                                <i class="bi {{ $statisticsSettings['stat_'.$i.'_icon'] }}" aria-hidden="true"></i>
                            @else
                                <img src="{{ asset('public/website/assets/images/' . ['stat-ceos-reference.png','stat-companies-reference.png','stat-countries-reference.png','stat-industries-reference.png'][$i - 1]) }}" alt="" loading="lazy">
                            @endif
                        </div>
                        <div>
                            <div class="stat-value">{{ $value }}</div>
                            <div class="stat-label">{{ $label }}</div>
                            <div class="stat-note">{{ $caption }}</div>
                        </div>
                    </div>
                @endfor
                <div class="stats-quote">Great leaders build companies. Their ideas build a better tomorrow.</div>
            </div>
        </div>
    </section>
    @endif

    @if($showFeaturedEntrepreneurs && $homepageFeaturedEntrepreneurs->isNotEmpty())
    <section class="section-block" id="featured-ceos">
        <div class="container-xl">
            <div class="section-heading">
                <div class="section-heading-main">
                    <span class="section-accent"></span>
                    <div>
                        <h2>{{ $homepageProfileSections->firstWhere('section_key', 'featured_entrepreneurs')?->title ?: 'Featured CEOs' }}</h2>
                        <p>{{ $homepageProfileSections->firstWhere('section_key', 'featured_entrepreneurs')?->subtitle ?: 'Influential business leaders making a global impact.' }}</p>
                    </div>
                </div>
                <div class="home-section-actions">
                    <a class="view-link" href="{{ route('site.entrepreneurs.index') }}">View All CEOs <i class="bi bi-arrow-right"></i></a>
                    <div class="home-carousel-controls">
                        <button aria-label="Show previous CEOs" data-carousel-prev="ceo-grid" type="button"><i class="bi bi-chevron-left"></i></button>
                        <button aria-label="Show next CEOs" data-carousel-next="ceo-grid" type="button"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <div class="ceo-grid" data-auto-carousel="true" id="ceo-grid" aria-label="Featured CEOs carousel">
                @foreach($homepageFeaturedEntrepreneurs as $featuredProfile)
                    @php
                        $person = $featuredProfile->publishedVersion?->personalDetails;
                        $name = $person?->full_name ?: $featuredProfile->slug;
                        $portrait = $featuredProfile->publishedVersion?->mediaLinks?->firstWhere('role', 'portrait')?->media?->url;
                    @endphp
                    <a class="ceo-card" href="{{ route('site.profile.show', $featuredProfile) }}">
                        @if($portrait)
                            <img alt="{{ $name }}" src="{{ $portrait }}" loading="lazy">
                        @else
                            <span class="ceo-image-placeholder" aria-hidden="true"><i class="bi bi-person-fill"></i></span>
                        @endif
                        <div class="ceo-card-body">
                            <div class="ceo-name">{{ $name }} @if($featuredProfile->is_verified)<i class="bi bi-patch-check-fill verified"></i>@endif</div>
                            <div class="ceo-role">{{ $person?->professional_title ?: ($person?->headline ?: $person?->current_position ?: 'Business Leader') }}</div>
                            <div class="ceo-tags">
                                @if($person?->industry)<span>{{ $person->industry->name }}</span>@endif
                                @if($person?->location)<span>{{ \Illuminate\Support\Str::afterLast($person->location, ',') }}</span>@endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($showFeaturedCompanies && $homepageFeaturedCompanies->isNotEmpty())
    <section class="section-block">
        <div class="container-xl">
            <div class="section-heading">
                <div class="section-heading-main">
                    <span class="section-accent"></span>
                    <i class="bi bi-buildings-fill section-heading-icon"></i>
                    <div>
                        <h2>{{ $homepageProfileSections->firstWhere('section_key', 'featured_companies')?->title ?: 'Featured Companies' }}</h2>
                        <p>{{ $homepageProfileSections->firstWhere('section_key', 'featured_companies')?->subtitle ?: 'Leading organisations and the people behind them.' }}</p>
                    </div>
                </div>
                <div class="home-section-actions">
                    <a class="view-link" href="{{ route('site.companies.index') }}">View All Companies <i class="bi bi-arrow-right"></i></a>
                    <div class="home-carousel-controls">
                        <button aria-label="Show previous companies" data-carousel-prev="company-grid" type="button"><i class="bi bi-chevron-left"></i></button>
                        <button aria-label="Show next companies" data-carousel-next="company-grid" type="button"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <div class="company-grid" data-auto-carousel="true" id="company-grid" aria-label="Featured companies carousel">
                @foreach($homepageFeaturedCompanies as $companyProfile)
                    @php
                        $company = $companyProfile->publishedVersion?->companyDetails;
                        $logo = $companyProfile->publishedVersion?->mediaLinks?->firstWhere('role', 'logo')?->media?->url ?: asset('public/website/assets/images/brand-mark-reference.png');
                        $leaders = collect($company?->leadership_items ?? []);
                        $leaderLabel = $leaders->first()['name'] ?? null;
                    @endphp
                    <a class="company-tile" href="{{ route('site.profile.show', $companyProfile) }}">
                        <div class="brand-logo"><img alt="{{ $company?->company_name ?: $companyProfile->slug }}" class="brand-logo-image" src="{{ $logo }}"></div>
                        <strong>{{ $company?->company_name ?: $companyProfile->slug }}</strong>
                        <small>{{ $company?->industry?->name ?: ($company?->business_type ?: 'Company') }}</small>
                        @if($leaderLabel)<span class="ceo-tag">Key person: {{ $leaderLabel }}</span>@elseif($company?->founded_year)<span class="ceo-tag">Founded {{ $company->founded_year }}</span>@endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="home-lower container-xl {{ $showRecentProfiles ? '' : 'home-lower-single' }}" id="updates">
        @if($showRecentProfiles)
        <div class="lower-panel">
            <div class="section-heading">
                <div class="section-heading-main">
                    <span class="section-accent"></span>
                    <i class="bi bi-clock-history section-heading-icon blue-icon"></i>
                    <div><h2>Recently Added / Updated</h2><p>Latest published people and company profiles.</p></div>
                </div>
                <a class="view-link" href="{{ route('site.profiles.index') }}">View All Updates <i class="bi bi-arrow-right"></i></a>
            </div>
            <ul class="update-list">
                @forelse($homepageRecentProfiles as $recentProfile)
                    @php
                        $isPerson = $recentProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL;
                        $recentDetails = $isPerson ? $recentProfile->publishedVersion?->personalDetails : $recentProfile->publishedVersion?->companyDetails;
                        $recentName = $isPerson ? ($recentDetails?->full_name ?: $recentProfile->slug) : ($recentDetails?->company_name ?: $recentProfile->slug);
                        $recentSubtitle = $isPerson
                            ? ($recentDetails?->professional_title ?: ($recentDetails?->headline ?: $recentDetails?->current_position))
                            : ($recentDetails?->tagline ?: $recentDetails?->short_description);
                        $recentImage = $recentProfile->publishedVersion?->mediaLinks?->firstWhere('role', $isPerson ? 'portrait' : 'logo')?->media?->url;
                    @endphp
                    <li>
                        @if($recentImage)
                            <img alt="{{ $recentName }}" class="update-thumb {{ $isPerson ? '' : 'update-logo-thumb' }}" src="{{ $recentImage }}" loading="lazy">
                        @else
                            <span class="update-thumb update-thumb-placeholder" aria-hidden="true"><i class="bi bi-{{ $isPerson ? 'person-fill' : 'buildings' }}"></i></span>
                        @endif
                        <div><div class="update-title">{{ $recentName }}</div><div class="update-sub">{{ $recentSubtitle }}</div></div>
                        <span class="pill">{{ $isPerson ? 'CEO' : 'Company' }}</span>
                        <span class="update-date">{{ optional($recentProfile->published_at)->format('M d, Y') }}</span>
                        <a class="muted" href="{{ route('site.profile.show', $recentProfile) }}" aria-label="View {{ $recentName }}">›</a>
                    </li>
                @empty
                    <li><div class="update-sub">New profiles will appear here after publication.</div></li>
                @endforelse
            </ul>
        </div>
        @endif

        <div class="lower-panel country-panel">
            <div class="section-heading">
                <div class="section-heading-main country-heading-main">
                    <i class="bi bi-globe section-heading-icon blue-icon"></i>
                    <div><h2>Explore by Country</h2><p>Discover published profiles from around the world.</p></div>
                </div>
                <a class="view-link" href="{{ route('site.profiles.index') }}">View All Profiles <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="country-map-stage" aria-label="Countries represented in the directory">
                <img alt="" class="country-map-base" src="{{ asset('public/website/assets/images/hero-map-dots.svg') }}">
                @foreach($regionLabels as $countryClass => $regionLabel)
                    @php($count = $mapCountryStats->get($countryClass, 0))
                    <a class="country-region {{ $countryClass }}" href="{{ route('site.profiles.index', ['q' => $regionLabel]) }}">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><strong>{{ $regionLabel }}</strong><small>{{ number_format($count) }} {{ \Illuminate\Support\Str::plural('profile', $count) }}</small></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if($submitSettings['submit_enabled'] ?? true)
    <section class="container-xl" id="request">
        <div class="cta-banner">
            <img alt="" class="cta-earth" src="{{ asset('public/website/assets/images/earth-globe.jpg') }}">
            <div class="cta-copy">
                <h3>{{ $submitSettings['submit_title'] ?? 'Be Part of CEOWiki' }}</h3>
                <p>{{ $submitSettings['submit_description'] ?? 'Help us build a reliable and comprehensive encyclopedia of CEOs and the companies they lead.' }}</p>
            </div>
            <a class="btn btn-gold" href="{{ $submitSettings['submit_button_url'] ?? route('customer.register') }}">{{ $submitSettings['submit_button_text'] ?? 'Request a Profile' }} <i class="bi bi-arrow-right ms-2"></i></a>
            <img alt="" class="cta-hand" src="{{ asset('public/website/assets/images/writing-hand.jpg') }}">
        </div>
    </section>
    @endif
</main>
@endsection
