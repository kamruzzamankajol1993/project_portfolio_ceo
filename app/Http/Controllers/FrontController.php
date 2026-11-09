<?php

namespace App\Http\Controllers;

use App\Models\ExtraPage;
use App\Models\HomepageSection;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FrontController extends Controller
{
    public function index(): View|Response
    {
        $settings = app('site.settings');

        if ($settings->maintenance_mode && !(auth()->check() && auth()->user()->can('access.admin'))) {
            return response()->view('maintenance', [], 503);
        }

        $profileRelations = [
            'publishedVersion.personalDetails.industry',
            'publishedVersion.companyDetails.industry',
            'publishedVersion.mediaLinks.media',
            'publishedVersion.sections.items',
            'publishedVersion.links',
            'publishedVersion.references',
            'publishedVersion.relationships.relatedProfile.publishedVersion.personalDetails',
            'publishedVersion.relationships.relatedProfile.publishedVersion.companyDetails',
            'publishedVersion.relationships.relatedProfile.publishedVersion.mediaLinks.media',
        ];

        $homepageProfileSections = HomepageSection::query()
            ->where('section_type', 'profiles')
            ->with(['items' => function ($query) use ($profileRelations): void {
                $query->where('is_enabled', true)
                    ->whereHas('profile', fn ($profileQuery) => $profileQuery->published())
                    ->orderBy('sort_order')
                    ->with(['profile' => function ($profileQuery) use ($profileRelations): void {
                        $profileQuery->published()->with($profileRelations);
                    }]);
            }])
            ->orderBy('sort_order')
            ->get();

        $section = static fn (string $key) => $homepageProfileSections->firstWhere('section_key', $key);

        $sectionIsEnabled = static function (string $key) use ($section): bool {
            $homepageSection = $section($key);

            // A missing section uses the default homepage layout; an existing disabled section stays hidden.
            return $homepageSection ? (bool) $homepageSection->is_enabled : true;
        };

        $sectionProfiles = static function (string $key) use ($section, $sectionIsEnabled) {
            if (!$sectionIsEnabled($key)) {
                return collect();
            }

            $homepageSection = $section($key);

            return $homepageSection
                ? $homepageSection->items->pluck('profile')->filter()->values()
                : collect();
        };

        $publishedProfiles = Profile::published()
            ->with($profileRelations)
            ->orderByDesc('published_at');

        $showHeroProfiles = $sectionIsEnabled('hero_profiles');
        $showFeaturedEntrepreneurs = $sectionIsEnabled('featured_entrepreneurs');
        $showSpotlights = $sectionIsEnabled('entrepreneur_spotlights');
        $showFeaturedStory = $sectionIsEnabled('featured_story');
        $showRecentProfiles = $sectionIsEnabled('recently_published_profiles');

        $homepageHeroProfiles = $sectionProfiles('hero_profiles')->take(6);
        if ($showHeroProfiles && $homepageHeroProfiles->isEmpty() && !$section('hero_profiles')) {
            $homepageHeroProfiles = (clone $publishedProfiles)->limit(6)->get();
        }

        $homepageFeaturedEntrepreneurs = $sectionProfiles('featured_entrepreneurs');
        if ($showFeaturedEntrepreneurs && $homepageFeaturedEntrepreneurs->isEmpty() && !$section('featured_entrepreneurs')) {
            $homepageFeaturedEntrepreneurs = (clone $publishedProfiles)
                ->where('profile_type', Profile::TYPE_PERSONAL)
                ->limit(12)
                ->get();
        }

        $homepageSpotlightProfiles = $sectionProfiles('entrepreneur_spotlights');
        if ($showSpotlights && $homepageSpotlightProfiles->isEmpty() && !$section('entrepreneur_spotlights')) {
            $homepageSpotlightProfiles = (clone $publishedProfiles)
                ->where('profile_type', Profile::TYPE_PERSONAL)
                ->skip(6)
                ->limit(5)
                ->get();
        }

        $homepageStoryProfile = $showFeaturedStory
            ? $sectionProfiles('featured_story')->first()
            : null;

        if ($showFeaturedStory && !$homepageStoryProfile && !$section('featured_story')) {
            $homepageStoryProfile = $homepageSpotlightProfiles->first()
                ?: $homepageFeaturedEntrepreneurs->first();
        }

        $homepageRecentProfiles = $sectionProfiles('recently_published_profiles')->take(4);
        if ($showRecentProfiles && $homepageRecentProfiles->isEmpty() && !$section('recently_published_profiles')) {
            $homepageRecentProfiles = (clone $publishedProfiles)->limit(4)->get();
        }

        // The new homepage uses a dedicated featured-company row. Keep it data-driven
        // from published company profiles without introducing hard-coded profile content.
        $showFeaturedCompanies = $sectionIsEnabled('featured_companies');
        $homepageFeaturedCompanies = $sectionProfiles('featured_companies')->where('profile_type', Profile::TYPE_COMPANY)->values();
        if ($showFeaturedCompanies && $homepageFeaturedCompanies->isEmpty() && !$section('featured_companies')) {
            $homepageFeaturedCompanies = (clone $publishedProfiles)
                ->where('profile_type', Profile::TYPE_COMPANY)
                ->limit(8)
                ->get();
        }

        $personalLocations = DB::table('profiles')
            ->join('profile_versions', 'profiles.published_version_id', '=', 'profile_versions.id')
            ->join('personal_profile_details', 'personal_profile_details.profile_version_id', '=', 'profile_versions.id')
            ->where('profiles.status', 'published')
            ->whereNotNull('profiles.published_version_id')
            ->whereNotNull('profiles.published_at')
            ->where('profiles.published_at', '<=', now())
            ->where('profiles.profile_type', Profile::TYPE_PERSONAL)
            ->pluck('personal_profile_details.location');

        $companyLocations = DB::table('profiles')
            ->join('profile_versions', 'profiles.published_version_id', '=', 'profile_versions.id')
            ->join('company_profile_details', 'company_profile_details.profile_version_id', '=', 'profile_versions.id')
            ->where('profiles.status', 'published')
            ->whereNotNull('profiles.published_version_id')
            ->whereNotNull('profiles.published_at')
            ->where('profiles.published_at', '<=', now())
            ->where('profiles.profile_type', Profile::TYPE_COMPANY)
            ->pluck('company_profile_details.headquarters');

        $allHomepageCountryStats = $personalLocations
            ->merge($companyLocations)
            ->map(function ($location) {
                $location = trim((string) $location);
                $country = $location !== '' ? trim(\Illuminate\Support\Str::afterLast($location, ',')) : '';
                return $country !== '' ? $country : null;
            })
            ->filter()
            ->countBy()
            ->sortDesc();

        $homepageCountryStats = $allHomepageCountryStats;
        $homepageStats = [
            'ceos' => Profile::published()->where('profile_type', Profile::TYPE_PERSONAL)->count(),
            'companies' => Profile::published()->where('profile_type', Profile::TYPE_COMPANY)->count(),
            'countries' => $allHomepageCountryStats->count(),
            'industries' => \App\Models\Industry::query()->count(),
        ];

        return view('website.homepage.homepage', compact(
            'homepageProfileSections',
            'homepageHeroProfiles',
            'homepageFeaturedEntrepreneurs',
            'homepageSpotlightProfiles',
            'homepageStoryProfile',
            'homepageRecentProfiles',
            'showHeroProfiles',
            'showFeaturedEntrepreneurs',
            'showSpotlights',
            'showFeaturedStory',
            'showRecentProfiles',
            'showFeaturedCompanies',
            'homepageFeaturedCompanies',
            'homepageCountryStats',
            'homepageStats'
        ));
    }

    public function showProfile(Profile $profile): View
    {
        abort_unless(Profile::published()->whereKey($profile->getKey())->exists(), 404);

        return $this->renderPublishedProfile($profile);
    }

    public function companyProfile(): View
    {
        $profileRelations = $this->publicProfileRelations();

        $profile = Profile::published()
            ->where('profile_type', Profile::TYPE_COMPANY)
            ->whereIn('slug', ['growbig-digital', 'growbigdigital'])
            ->with($profileRelations)
            ->first();

        $profile ??= Profile::published()
            ->where('profile_type', Profile::TYPE_COMPANY)
            ->with($profileRelations)
            ->orderByDesc('published_at')
            ->firstOrFail();

        return $this->renderPublishedProfile($profile);
    }

    public function personalProfile(): View
    {
        $profileRelations = $this->publicProfileRelations();

        $profile = Profile::published()
            ->where('profile_type', Profile::TYPE_PERSONAL)
            ->whereIn('slug', ['nazim-uddin-hridom', 'nazimuddinhridom'])
            ->with($profileRelations)
            ->first();

        $profile ??= Profile::published()
            ->where('profile_type', Profile::TYPE_PERSONAL)
            ->with($profileRelations)
            ->orderByDesc('published_at')
            ->firstOrFail();

        return $this->renderPublishedProfile($profile);
    }

    private function renderPublishedProfile(Profile $profile): View
    {
        $profile->load($this->publicProfileRelations());

        $version = $profile->publishedVersion;

        $details = $profile->profile_type === Profile::TYPE_PERSONAL
            ? $version?->personalDetails
            : $version?->companyDetails;

        abort_unless($details, 404);

        $view = $profile->profile_type === Profile::TYPE_COMPANY
            ? 'website.profile.companyProfile'
            : 'website.profile.personalProfile';

        return view($view, compact('profile', 'version', 'details'));
    }

    private function publicProfileRelations(): array
    {
        return [
            'publishedVersion.personalDetails.industry',
            'publishedVersion.companyDetails.industry',
            'publishedVersion.sections.items',
            'publishedVersion.links',
            'publishedVersion.references',
            'publishedVersion.mediaLinks.media',
            'publishedVersion.relationships.relatedProfile.publishedVersion.personalDetails',
            'publishedVersion.relationships.relatedProfile.publishedVersion.companyDetails',
            'publishedVersion.relationships.relatedProfile.publishedVersion.mediaLinks.media',
            'publishedVersion.seo',
        ];
    }

    public function allProfiles(Request $request): View|Response|\Illuminate\Http\JsonResponse
    {
        return $this->profileDirectory($request, null);
    }

    public function companyProfilesDirectory(Request $request): View|Response|\Illuminate\Http\JsonResponse
    {
        return $this->profileDirectory($request, Profile::TYPE_COMPANY);
    }

    public function personalProfilesDirectory(Request $request): View|Response|\Illuminate\Http\JsonResponse
    {
        return $this->profileDirectory($request, Profile::TYPE_PERSONAL);
    }

    private function profileDirectory(Request $request, ?string $profileType): View|Response|\Illuminate\Http\JsonResponse
    {
        $filterType = $profileType ?: ($request->filled('type') && in_array($request->string('type')->toString(), [Profile::TYPE_PERSONAL, Profile::TYPE_COMPANY], true)
            ? $request->string('type')->toString()
            : null);

        $query = Profile::published()->when($filterType, fn ($builder) => $builder->where('profile_type', $filterType))->with([
            'publishedVersion.personalDetails.industry',
            'publishedVersion.companyDetails.industry',
            'publishedVersion.mediaLinks.media',
        ]);

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($term, $filterType): void {
                if ($filterType !== Profile::TYPE_COMPANY) {
                    $builder->orWhereHas('publishedVersion.personalDetails', fn ($details) => $details->where('full_name', 'like', "%{$term}%")
                        ->orWhere('headline', 'like', "%{$term}%")
                        ->orWhere('current_position', 'like', "%{$term}%"));
                }
                if ($filterType !== Profile::TYPE_PERSONAL) {
                    $builder->orWhereHas('publishedVersion.companyDetails', fn ($details) => $details->where('company_name', 'like', "%{$term}%")
                        ->orWhere('tagline', 'like', "%{$term}%")
                        ->orWhere('short_description', 'like', "%{$term}%"));
                }
            });
        }

        $profiles = $query->orderByDesc('published_at')->paginate(12)->withQueryString();
        $view = 'website.profile.index';

        if ($request->ajax()) {
            return response()->json([
                'html' => view('website.profile._directory-cards', compact('profiles'))->render(),
                'next_page_url' => $profiles->nextPageUrl(),
            ]);
        }

        return view($view, compact('profiles', 'profileType', 'filterType'));
    }

    public function aboutUs(): View
    {
        $extraPage = $this->resolvePublicPage(
            'about',
            'about-us',
            'About Us',
            '<p>EntrepreneurWiki is an independent platform documenting entrepreneurs, founders, business leaders and the organisations they build.</p><p>Our goal is to make reliable, useful and clearly presented information easier to discover. We bring together professional stories, company information and sourced achievements in one accessible place.</p><p>We value accuracy, transparency and responsible contributions. Public profile information is reviewed before publication, and readers can contact our team with corrections or suggestions.</p>'
        );

        return view('website.extra.about-us', compact('extraPage'));
    }

    public function contactUs(): View
    {
        $extraPage = $this->resolvePublicPage(
            'contact',
            'contact-us',
            'Contact Us',
            '<p>We welcome questions, profile suggestions, corrections and partnership enquiries.</p><p>Use the contact details on this page to reach the EntrepreneurWiki team. For profile submissions, please create an account and follow the review process.</p>'
        );

        return view('website.extra.contact-us', compact('extraPage'));
    }

    private function resolvePublicPage(string $key, string $slug, string $title, string $content): ExtraPage
    {
        $page = ExtraPage::firstOrCreate(
            ['page_key' => $key],
            [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'meta_title' => $title . ' | ' . app('site.settings')->site_name,
                'meta_description' => $title . ' information for ' . app('site.settings')->site_name . '.',
                'meta_robots' => 'index,follow',
                'is_published' => true,
            ]
        );

        abort_unless($page->is_published || (auth()->check() && auth()->user()->can('extra-pages.manage')), 404);

        return $page;
    }
}
