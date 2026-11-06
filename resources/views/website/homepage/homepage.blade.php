@extends('website.master.master')

@section('title')
    {{ $siteSetting->seo_title ?: $siteSetting->site_name }}
@endsection


@section('css')

@endsection


@section('body')
<main id="top">
    @php
        $featuredEntrepreneursSection = $homepageProfileSections->firstWhere('section_key', 'featured_entrepreneurs');
        $spotlightsSection = $homepageProfileSections->firstWhere('section_key', 'entrepreneur_spotlights');
        $featuredStorySection = $homepageProfileSections->firstWhere('section_key', 'featured_story');
        $recentProfilesSection = $homepageProfileSections->firstWhere('section_key', 'recently_published_profiles');
    @endphp
<!-- Dynamic Hero -->
    @if($heroSettings['hero_enabled'] ?? true)
    <section class="hero-section page-shell container-fluid">
      <div class="hero-copy">
        @if(!empty($heroSettings['hero_eyebrow']))<div class="eyebrow">{{ $heroSettings['hero_eyebrow'] }}</div>@endif
        @if(!empty($heroSettings['hero_title']))<h1>{!! nl2br(e($heroSettings['hero_title'])) !!}</h1>@endif
        @if(!empty($heroSettings['hero_description']))<p class="hero-description">{{ $heroSettings['hero_description'] }}</p>@endif
        <div class="site-search-box hero-search-box">
          <form class="hero-search" role="search" aria-label="Search the website" data-ajax-search data-search-url="{{ route('site.search') }}" data-results-id="hero-search-results">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" autocomplete="off" aria-label="Search entrepreneurs, companies, or industries" placeholder="{{ $searchSettings['site_search_placeholder'] ?? 'Search entrepreneurs, companies, or industries...' }}">
            <button type="submit" class="btn btn-primary">{{ $heroSettings['hero_search_button_text'] ?? 'Search' }}</button>
          </form>
          <div id="hero-search-results" class="search-results-dropdown" role="listbox" aria-label="Search results" hidden></div>
        </div>
        @php
          $popularSearches = $heroSettings['hero_popular_searches'] ?? [];
          if (is_string($popularSearches)) $popularSearches = preg_split('/\r\n|\r|\n|,/', $popularSearches);
        @endphp
        @if(!empty($popularSearches))
          <div class="popular-searches"><strong>{{ $heroSettings['hero_popular_label'] ?? 'Popular searches:' }}</strong>
            @foreach($popularSearches as $popularTerm)
              @if(trim((string) $popularTerm) !== '')<button type="button" class="popular-search-chip" data-popular-search="{{ trim($popularTerm) }}">{{ trim($popularTerm) }}</button>@endif
            @endforeach
          </div>
        @endif
        <div class="hero-actions">
          @if(!empty($heroSettings['hero_primary_button_text']))<a href="{{ $heroSettings['hero_primary_button_url'] ?? '#featured' }}" class="btn btn-primary btn-lg">{{ $heroSettings['hero_primary_button_text'] }} <i class="bi bi-arrow-right"></i></a>@endif
          @if(!empty($heroSettings['hero_secondary_button_text']))<a href="{{ route('customer.register') }}" class="btn btn-light btn-lg">{{ $heroSettings['hero_secondary_button_text'] }}</a>@endif
        </div>
      </div>

      <div class="hero-visual" aria-label="Featured entrepreneur profile cards">
        <div class="map-dots"></div>

        @php
            $heroCardClasses = [
                'hero-card-elon',
                'hero-card-jeff',
                'hero-card-melinda',
                'hero-card-mark',
                'hero-card-sundar',
                'hero-card-satya',
            ];
        @endphp

        @foreach($homepageHeroProfiles->take(6) as $heroProfile)
            @php
                $heroPersonal = $heroProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL;
                $heroDetails = $heroPersonal
                    ? $heroProfile->publishedVersion?->personalDetails
                    : $heroProfile->publishedVersion?->companyDetails;

                $heroName = $heroPersonal
                    ? ($heroDetails?->full_name ?? $heroProfile->slug)
                    : ($heroDetails?->company_name ?? $heroProfile->slug);

                $heroRole = $heroPersonal
                    ? ($heroDetails?->professional_title ?: ($heroDetails?->headline ?: $heroDetails?->current_position))
                    : ($heroDetails?->tagline ?: $heroDetails?->business_type);

                $heroImage = $heroProfile->publishedVersion?->mediaLinks
                    ?->firstWhere('role', $heroPersonal ? 'portrait' : 'logo')
                    ?->media?->url;

                $heroImage = $heroImage ?: (
                    $heroPersonal
                        ? asset('public/website/assets/nazim-founder.jpg')
                        : asset('public/website/assets/company-logo.jpg')
                );

                $heroLocation = $heroPersonal
                    ? ($heroDetails?->location ?? '')
                    : ($heroDetails?->headquarters ?? '');
            @endphp

            <a
                class="hero-card {{ $heroCardClasses[$loop->index] ?? 'hero-card-satya' }}"
                href="{{ route('site.profile.show', $heroProfile) }}"
            >
                <img src="{{ $heroImage }}" alt="{{ $heroName }}" loading="lazy">

                <div class="hero-card-caption">
                    <strong>
                        {{ $heroName }}
                        @if($heroProfile->is_verified)
                            <i class="bi bi-patch-check-fill verified"></i>
                        @endif
                    </strong>
                    <span>{{ $heroRole ?: ucfirst($heroProfile->profile_type) . ' profile' }}</span>
                    <span>{{ $heroLocation }}</span>
                </div>
            </a>
        @endforeach

        <div class="hero-note">
            Real People.
            <br>
            Real Stories.
            <br>
            <b>Real Impact.</b>
            <i class="bi bi-arrow-down-right"></i>
        </div>
      </div>
    </section>
    @endif

    <!-- Dynamic Statistics -->
    @if($statisticsSettings['stats_enabled'] ?? true)
      <section class="stats-grid page-shell container-fluid" id="companies" aria-label="Platform statistics">
        @for($i = 1; $i <= 4; $i++)
          @php
            $statValue = $statisticsSettings['stat_'.$i.'_value'] ?? '';
            $statLabel = $statisticsSettings['stat_'.$i.'_label'] ?? '';
            $statCaption = $statisticsSettings['stat_'.$i.'_caption'] ?? '';
            $statIcon = $statisticsSettings['stat_'.$i.'_icon'] ?? 'bi-people-fill';
            $statColor = in_array(($statisticsSettings['stat_'.$i.'_color'] ?? 'blue'), ['blue', 'green'], true) ? $statisticsSettings['stat_'.$i.'_color'] : 'blue';
          @endphp
          @if($statValue !== '' || $statLabel !== '' || $statCaption !== '')
            <div class="stat-card">
              <span class="stat-icon {{ $statColor }}"><i class="bi {{ $statIcon }}"></i></span>
              <div><strong>{{ $statValue }}</strong><span>{{ $statLabel }}</span><small>{{ $statCaption }}</small></div>
            </div>
          @endif
        @endfor
      </section>
    @endif

    <!-- Dynamic Why Choose Us -->
    @if($whySettings['why_enabled'] ?? true)
      @php
        $whyTitle = str_replace(':site_name', $siteSetting->site_name, $whySettings['why_title'] ?? 'Why '.$siteSetting->site_name.'?');
      @endphp
      <section class="why-banner page-shell container-fluid" id="why-us">
        <div class="why-intro">
          <span class="why-book"><i class="bi {{ $whySettings['why_icon'] ?? 'bi-book-half' }}"></i></span>
          <div><h2>{{ $whyTitle }}</h2><p>{{ $whySettings['why_description'] ?? '' }}</p></div>
        </div>
        @for($i = 1; $i <= 3; $i++)
          @php
            $whyIcon = $whySettings['why_point_'.$i.'_icon'] ?? 'bi-shield-check';
            $whyPointTitle = $whySettings['why_point_'.$i.'_title'] ?? '';
            $whyPointCaption = $whySettings['why_point_'.$i.'_caption'] ?? '';
          @endphp
          @if($whyPointTitle !== '' || $whyPointCaption !== '')
            <div class="why-point"><span><i class="bi {{ $whyIcon }}"></i></span><strong>{{ $whyPointTitle }}</strong><small>{{ $whyPointCaption }}</small></div>
          @endif
        @endfor
      </section>
    @endif

    <!-- Featured Entrepreneurs -->
    @if($showFeaturedEntrepreneurs && $homepageFeaturedEntrepreneurs->isNotEmpty())
    <section class="content-section page-shell container-fluid" id="featured">
      <div class="section-heading">
        <div>
          <h2><i class="bi bi-star-fill section-icon star"></i>{{ $featuredEntrepreneursSection?->title ?: 'Featured Entrepreneurs' }}</h2>
          <p>{{ $featuredEntrepreneursSection?->subtitle ?: 'Explore notable entrepreneurs, founders and business leaders.' }}</p>
        </div>

        <div class="section-controls">
          <a href="{{ route('site.profiles.index') }}">
            View all featured profiles <i class="bi bi-arrow-right"></i>
          </a>
          <button type="button" aria-label="Previous featured profiles" data-profile-slider-prev="featured-entrepreneurs">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button type="button" aria-label="Next featured profiles" data-profile-slider-next="featured-entrepreneurs">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>

      <div class="featured-grid featured-carousel" id="featured-entrepreneurs-carousel" data-profile-carousel="featured-entrepreneurs">
        @include('website.profile._directory-cards', [
            'profiles' => $homepageFeaturedEntrepreneurs,
        ])
      </div>
    </section>
    @endif

    <!-- Entrepreneur Spotlights -->
    @php
        $storyProfile = $homepageStoryProfile;
        $storyPersonal = $storyProfile
            && $storyProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL;

        $storyDetails = $storyProfile
            ? ($storyPersonal
                ? $storyProfile->publishedVersion?->personalDetails
                : $storyProfile->publishedVersion?->companyDetails)
            : null;

        $storyName = $storyDetails
            ? ($storyPersonal ? $storyDetails->full_name : $storyDetails->company_name)
            : '';

        $storyTitle = $storyDetails
            ? ($storyPersonal
                ? ($storyDetails->professional_title ?: $storyDetails->headline)
                : ($storyDetails->tagline ?: $storyDetails->business_type))
            : '';

        $storyDescription = $storyDetails
            ? ($storyPersonal
                ? ($storyDetails->short_bio ?: $storyDetails->about)
                : ($storyDetails->short_description ?: $storyDetails->overview))
            : '';

        $storyImage = $storyProfile?->publishedVersion?->mediaLinks
            ?->firstWhere('role', $storyPersonal ? 'portrait' : 'logo')
            ?->media?->url;

        $storyImage = $storyImage ?: (
            $storyPersonal
                ? asset('public/website/assets/nazim-founder.jpg')
                : asset('public/website/assets/company-logo.jpg')
        );
    @endphp

    @if(($showFeaturedStory && $storyProfile) || ($showSpotlights && $homepageSpotlightProfiles->isNotEmpty()))
      <section class="content-section spotlight-section page-shell container-fluid">
        <div class="section-heading">
          <div>
            <h2><i class="bi bi-file-earmark-text-fill section-icon document"></i>{{ $spotlightsSection?->title ?: 'Entrepreneur Spotlights' }}</h2>
            <p>{{ $spotlightsSection?->subtitle ?: 'In-depth stories on influential entrepreneurs and their journeys.' }}</p>
          </div>

          <div class="section-controls">
            <a href="{{ route('site.profiles.index') }}">
              View all stories <i class="bi bi-arrow-right"></i>
            </a>
            <button type="button" aria-label="Previous stories" data-spotlight-prev>
              <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" aria-label="Next stories" data-spotlight-next>
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>

        <div class="spotlight-layout" id="spotlights">
          @if($storyProfile)
            <article class="spotlight-feature">
              <img src="{{ $storyImage }}" alt="{{ $storyName }}" loading="lazy">

              <div class="spotlight-copy">
                <span class="featured-label">{{ $featuredStorySection?->title ?: 'FEATURED STORY' }}</span>
                <h3>{{ $storyName }}</h3>
                @if($storyTitle)<strong>{{ $storyTitle }}</strong>@endif
                <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $storyDescription), 240) }}</p>
                <a href="{{ route('site.profile.show', $storyProfile) }}">
                  Read Full Story <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </article>
          @endif

          @if($showSpotlights && $homepageSpotlightProfiles->isNotEmpty())
          <div class="story-list">
            @foreach($homepageSpotlightProfiles->take(3) as $spotlightProfile)
              @php
                $spotlightPersonal = $spotlightProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL;
                $spotlightDetails = $spotlightPersonal
                    ? $spotlightProfile->publishedVersion?->personalDetails
                    : $spotlightProfile->publishedVersion?->companyDetails;

                $spotlightName = $spotlightPersonal
                    ? ($spotlightDetails?->full_name ?? $spotlightProfile->slug)
                    : ($spotlightDetails?->company_name ?? $spotlightProfile->slug);

                $spotlightTitle = $spotlightPersonal
                    ? ($spotlightDetails?->professional_title ?: $spotlightDetails?->headline)
                    : ($spotlightDetails?->tagline ?: $spotlightDetails?->business_type);

                $spotlightDescription = $spotlightPersonal
                    ? $spotlightDetails?->short_bio
                    : $spotlightDetails?->short_description;

                $spotlightImage = $spotlightProfile->publishedVersion?->mediaLinks
                    ?->firstWhere('role', $spotlightPersonal ? 'portrait' : 'logo')
                    ?->media?->url;

                $spotlightImage = $spotlightImage ?: (
                    $spotlightPersonal
                        ? asset('public/website/assets/nazim-founder.jpg')
                        : asset('public/website/assets/company-logo.jpg')
                );
              @endphp

              <article class="story-mini">
                <img src="{{ $spotlightImage }}" alt="{{ $spotlightName }}" loading="lazy">

                <div>
                  <h3>{{ $spotlightName }}</h3>
                  <p>{{ $spotlightTitle }}</p>
                  @if($spotlightDescription)
                    <small>{{ \Illuminate\Support\Str::limit(strip_tags((string) $spotlightDescription), 100) }}</small>
                  @endif
                  <a href="{{ route('site.profile.show', $spotlightProfile) }}">
                    Read Story <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </article>
            @endforeach
          </div>
          @endif
        </div>
      </section>
    @endif

    <!-- Recently published profiles -->
    @if($showRecentProfiles && $homepageRecentProfiles->isNotEmpty())
    <section class="content-section recent-section page-shell container-fluid">
      <div class="section-heading">
        <div>
          <h2><i class="bi bi-clock section-icon clock"></i>{{ $recentProfilesSection?->title ?: 'Recently Published Profiles' }}</h2>
          <p>{{ $recentProfilesSection?->subtitle ?: 'Discover newly added and updated entrepreneur profiles.' }}</p>
        </div>

        <div class="section-controls">
          <a href="{{ route('site.profiles.index') }}">
            View all profiles <i class="bi bi-arrow-right"></i>
          </a>
          <button type="button" aria-label="Previous profiles" data-recent-prev>
            <i class="bi bi-chevron-left"></i>
          </button>
          <button type="button" aria-label="Next profiles" data-recent-next>
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>

      <div class="recent-grid" id="recent">
        @foreach($homepageRecentProfiles as $recentProfile)
          @php
            $recentPersonal = $recentProfile->profile_type === \App\Models\Profile::TYPE_PERSONAL;
            $recentDetails = $recentPersonal
                ? $recentProfile->publishedVersion?->personalDetails
                : $recentProfile->publishedVersion?->companyDetails;

            $recentName = $recentPersonal
                ? ($recentDetails?->full_name ?? $recentProfile->slug)
                : ($recentDetails?->company_name ?? $recentProfile->slug);

            $recentTitle = $recentPersonal
                ? ($recentDetails?->professional_title ?: ($recentDetails?->headline ?: $recentDetails?->current_position))
                : ($recentDetails?->tagline ?: $recentDetails?->business_type);

            $recentOrganization = $recentPersonal
                ? ($recentDetails?->company_organisation ?: $recentDetails?->industry?->name)
                : ($recentDetails?->industry?->name ?: $recentDetails?->headquarters);

            $recentImage = $recentProfile->publishedVersion?->mediaLinks
                ?->firstWhere('role', $recentPersonal ? 'portrait' : 'logo')
                ?->media?->url;

            $recentImage = $recentImage ?: (
                $recentPersonal
                    ? asset('public/website/assets/nazim-founder.jpg')
                    : asset('public/website/assets/company-logo.jpg')
            );
          @endphp

          <article class="recent-card">
            <img src="{{ $recentImage }}" alt="{{ $recentName }}" loading="lazy">

            <div>
              <h3>
                <a href="{{ route('site.profile.show', $recentProfile) }}">{{ $recentName }}</a>
                @if($recentProfile->is_verified)
                  <i class="bi bi-patch-check-fill verified"></i>
                @endif
              </h3>
              @if($recentTitle)<p>{{ $recentTitle }}</p>@endif
              @if($recentOrganization)<small>{{ $recentOrganization }}</small>@endif
            </div>
          </article>
        @endforeach
      </div>
    </section>
    @endif

    {{-- Any additional profile sections created by an administrator --}}
    @php
        $fixedHomepageSectionKeys = [
            'hero_profiles',
            'featured_entrepreneurs',
            'entrepreneur_spotlights',
            'featured_story',
            'recently_published_profiles',
        ];

        $customHomepageSections = $homepageProfileSections->filter(function ($section) use ($fixedHomepageSectionKeys) {
            return $section->is_enabled
                && !in_array($section->section_key, $fixedHomepageSectionKeys, true)
                && $section->items->isNotEmpty();
        });
    @endphp

    @foreach($customHomepageSections as $customSection)
        <section class="content-section page-shell container-fluid" id="homepage-section-{{ $customSection->id }}">
            <div class="section-heading">
                <div>
                    <h2><i class="bi bi-grid section-icon star"></i>{{ $customSection->title }}</h2>
                    @if($customSection->subtitle)
                        <p>{{ $customSection->subtitle }}</p>
                    @endif
                </div>

                <div class="section-controls">
                    <a href="{{ route('site.profiles.index') }}">
                        Explore all profiles <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="featured-grid">
                @include('website.profile._directory-cards', [
                    'profiles' => $customSection->items->pluck('profile')->filter(),
                ])
            </div>
        </section>
    @endforeach

    <!-- Dynamic Profile Submission CTA -->
    @if($submitSettings['submit_enabled'] ?? true)
      <section class="submit-banner page-shell container-fluid" id="submit-profile">
        <div class="submit-icon"><i class="bi {{ $submitSettings['submit_icon'] ?? 'bi-people-fill' }}"></i></div>
        <div class="submit-copy">
          <h2>{{ $submitSettings['submit_title'] ?? 'Are You an Entrepreneur?' }}</h2>
          <p>{!! nl2br(e($submitSettings['submit_description'] ?? 'Submit your professional information for editorial review and become part of a trusted global resource.')) !!}</p>
        </div>
        @if(!empty($submitSettings['submit_button_text']))
          <a href="{{ route('customer.register') }}" class="btn btn-light btn-lg">{{ $submitSettings['submit_button_text'] }} <i class="bi bi-arrow-right"></i></a>
        @endif
      </section>
    @endif
</main>
@endsection


@section('scripts')
<script src="{{ asset('/') }}public/website/homepage-profile-slider.js"></script>
@endsection
