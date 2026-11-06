<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use App\Models\HomepageSection;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HomepageManagerController extends Controller
{
    public function index()
    {
        $heroSettings = $this->settingsForGroup('homepage_hero', [
            'hero_enabled' => true,
            'hero_eyebrow' => 'PEOPLE • BUSINESSES • IDEAS • IMPACT',
            'hero_title' => "Discover Entrepreneurs\nWho Shape a Brighter\nTomorrow.",
            'hero_description' => 'Explore the stories, careers and achievements of entrepreneurs, founders and business leaders from around the world.',
            'hero_search_button_text' => 'Search',
            'hero_popular_label' => 'Popular searches:',
            'hero_popular_searches' => ['Elon Musk', 'Jeff Bezos', 'Satya Nadella', 'Startups', 'Fintech'],
            'hero_primary_button_text' => 'Explore Entrepreneurs',
            'hero_primary_button_url' => '#featured',
            'hero_secondary_button_text' => 'Create Your Profile',
            'hero_secondary_button_url' => '/customer-register',
        ]);
        $popularSearches = $heroSettings['hero_popular_searches'] ?? [];
        $heroSettings['hero_popular_searches'] = is_array($popularSearches)
            ? implode("\n", $popularSearches)
            : (string) $popularSearches;

        $searchSettings = $this->settingsForGroup('website_search', [
            'site_search_placeholder' => 'Search entrepreneurs, companies, or industries...',
        ]);

        $statisticsSettings = $this->settingsForGroup('homepage_statistics', [
            'stats_enabled' => true,
            'stat_1_value' => '12,480+', 'stat_1_label' => 'Entrepreneurs', 'stat_1_caption' => 'Published profiles', 'stat_1_icon' => 'bi-people-fill', 'stat_1_color' => 'blue',
            'stat_2_value' => '8,230+', 'stat_2_label' => 'Companies', 'stat_2_caption' => 'With founder information', 'stat_2_icon' => 'bi-buildings-fill', 'stat_2_color' => 'blue',
            'stat_3_value' => '126', 'stat_3_label' => 'Industries', 'stat_3_caption' => 'Business categories', 'stat_3_icon' => 'bi-diagram-3-fill', 'stat_3_color' => 'green',
            'stat_4_value' => '10,540+', 'stat_4_label' => 'Published Profiles', 'stat_4_caption' => 'Verified and reviewed', 'stat_4_icon' => 'bi-file-earmark-text-fill', 'stat_4_color' => 'blue',
        ]);

        $whySettings = $this->settingsForGroup('homepage_why', [
            'why_enabled' => true,
            'why_title' => 'Why :site_name?',
            'why_description' => 'An independent platform documenting entrepreneurs, business leaders, companies and their professional achievements with accuracy and neutrality.',
            'why_icon' => 'bi-book-half',
            'why_point_1_icon' => 'bi-shield-check', 'why_point_1_title' => 'Reliable Information', 'why_point_1_caption' => 'Fact-based and sourced',
            'why_point_2_icon' => 'bi-people', 'why_point_2_title' => 'Community Contributions', 'why_point_2_caption' => 'Help improve profiles',
            'why_point_3_icon' => 'bi-globe2', 'why_point_3_title' => 'Global Coverage', 'why_point_3_caption' => 'Entrepreneurs worldwide',
        ]);

        $submitSettings = $this->settingsForGroup('homepage_submit', [
            'submit_enabled' => true,
            'submit_icon' => 'bi-people-fill',
            'submit_title' => 'Are You an Entrepreneur?',
            'submit_description' => 'Submit your professional information for editorial review and become part of a trusted global resource.',
            'submit_button_text' => 'Request Your Profile',
            'submit_button_url' => '/customer-register',
        ]);

        $newsletterSettings = $this->settingsForGroup('footer_newsletter', [
            'newsletter_enabled' => true,
            'newsletter_label' => 'STAY IN THE LOOP',
            'newsletter_title' => 'Ideas worth exploring.',
            'newsletter_description' => 'Get the latest stories and updates from :site_name.',
            'newsletter_placeholder' => 'Your email address',
            'newsletter_button_text' => 'Subscribe',
            'newsletter_success_message' => 'Thank you for subscribing to our newsletter.',
        ]);

        $homepageProfileSections = HomepageSection::query()->where('section_type', 'profiles')->with('items')->orderBy('sort_order')->get();
        $publishedProfiles = Profile::published()->with(['publishedVersion.personalDetails', 'publishedVersion.companyDetails'])->orderByDesc('published_at')->get();

        return view('admin.homepage-manager.index', compact(
            'heroSettings', 'searchSettings',
            'statisticsSettings', 'whySettings', 'submitSettings',
            'homepageProfileSections', 'publishedProfiles', 'newsletterSettings'
        ));
    }

    public function update(Request $request)
    {
        $rules = [
            'hero_enabled' => 'nullable|boolean',
            'hero_eyebrow' => 'nullable|string|max:180',
            'hero_title' => 'nullable|string|max:220',
            'hero_description' => 'nullable|string|max:1000',
            'hero_search_button_text' => 'nullable|string|max:40',
            'hero_popular_label' => 'nullable|string|max:60',
            'hero_popular_searches' => 'nullable|string|max:1000',
            'hero_primary_button_text' => 'nullable|string|max:80',
            'hero_primary_button_url' => 'nullable|string|max:500',
            'hero_secondary_button_text' => 'nullable|string|max:80',
            'hero_secondary_button_url' => 'nullable|string|max:500',
            'site_search_placeholder' => 'nullable|string|max:150',
            'stats_enabled' => 'nullable|boolean',
            'why_enabled' => 'nullable|boolean',
            'why_title' => 'nullable|string|max:220',
            'why_description' => 'nullable|string|max:1000',
            'why_icon' => ['nullable', 'regex:/^bi-[a-z0-9-]+$/'],
            'submit_enabled' => 'nullable|boolean',
            'submit_icon' => ['nullable', 'regex:/^bi-[a-z0-9-]+$/'],
            'submit_title' => 'nullable|string|max:220',
            'submit_description' => 'nullable|string|max:1000',
            'submit_button_text' => 'nullable|string|max:80',
            'submit_button_url' => 'nullable|string|max:500',
            'newsletter_enabled' => 'nullable|boolean',
            'newsletter_label' => 'nullable|string|max:100',
            'newsletter_title' => 'nullable|string|max:180',
            'newsletter_description' => 'nullable|string|max:500',
            'newsletter_placeholder' => 'nullable|string|max:120',
            'newsletter_button_text' => 'nullable|string|max:50',
            'newsletter_success_message' => 'nullable|string|max:250',
        ];

        for ($i = 1; $i <= 4; $i++) {
            $rules["stat_{$i}_value"] = 'nullable|string|max:60';
            $rules["stat_{$i}_label"] = 'nullable|string|max:100';
            $rules["stat_{$i}_caption"] = 'nullable|string|max:160';
            $rules["stat_{$i}_icon"] = ['nullable', 'regex:/^bi-[a-z0-9-]+$/'];
            $rules["stat_{$i}_color"] = ['nullable', Rule::in(['blue', 'green'])];
        }
        for ($i = 1; $i <= 3; $i++) {
            $rules["why_point_{$i}_icon"] = ['nullable', 'regex:/^bi-[a-z0-9-]+$/'];
            $rules["why_point_{$i}_title"] = 'nullable|string|max:120';
            $rules["why_point_{$i}_caption"] = 'nullable|string|max:180';
        }

        $data = $request->validate($rules);

        $hero = [
            'hero_enabled' => $request->boolean('hero_enabled'),
            'hero_eyebrow' => trim($data['hero_eyebrow'] ?? ''),
            'hero_title' => trim($data['hero_title'] ?? ''),
            'hero_description' => trim($data['hero_description'] ?? ''),
            'hero_search_button_text' => trim($data['hero_search_button_text'] ?? 'Search'),
            'hero_popular_label' => trim($data['hero_popular_label'] ?? 'Popular searches:'),
            'hero_popular_searches' => collect(preg_split('/\r\n|\r|\n|,/', $data['hero_popular_searches'] ?? ''))
                ->map(fn ($term) => trim($term))
                ->filter()
                ->unique(fn ($term) => mb_strtolower($term))
                ->take(12)
                ->values()
                ->all(),
            'hero_primary_button_text' => trim($data['hero_primary_button_text'] ?? ''),
            'hero_primary_button_url' => trim($data['hero_primary_button_url'] ?? '#featured'),
            'hero_secondary_button_text' => trim($data['hero_secondary_button_text'] ?? ''),
            'hero_secondary_button_url' => trim($data['hero_secondary_button_url'] ?? '/customer-register'),
        ];
        $this->validateUrl($hero['hero_primary_button_url'], 'hero_primary_button_url', true);
        $this->validateUrl($hero['hero_secondary_button_url'], 'hero_secondary_button_url', true);
        $this->validateUrl(trim($data['submit_button_url'] ?? '/customer-register'), 'submit_button_url', true);

        $siteSearchPlaceholder = trim($data['site_search_placeholder'] ?? '') ?: 'Search entrepreneurs, companies, or industries...';

        $statistics = ['stats_enabled' => $request->boolean('stats_enabled')];
        for ($i = 1; $i <= 4; $i++) {
            $statistics["stat_{$i}_value"] = trim($data["stat_{$i}_value"] ?? '');
            $statistics["stat_{$i}_label"] = trim($data["stat_{$i}_label"] ?? '');
            $statistics["stat_{$i}_caption"] = trim($data["stat_{$i}_caption"] ?? '');
            $statistics["stat_{$i}_icon"] = trim($data["stat_{$i}_icon"] ?? 'bi-people-fill');
            $statistics["stat_{$i}_color"] = $data["stat_{$i}_color"] ?? 'blue';
        }

        $why = [
            'why_enabled' => $request->boolean('why_enabled'),
            'why_title' => trim($data['why_title'] ?? ''),
            'why_description' => trim($data['why_description'] ?? ''),
            'why_icon' => trim($data['why_icon'] ?? 'bi-book-half'),
        ];
        for ($i = 1; $i <= 3; $i++) {
            $why["why_point_{$i}_icon"] = trim($data["why_point_{$i}_icon"] ?? 'bi-shield-check');
            $why["why_point_{$i}_title"] = trim($data["why_point_{$i}_title"] ?? '');
            $why["why_point_{$i}_caption"] = trim($data["why_point_{$i}_caption"] ?? '');
        }

        $submit = [
            'submit_enabled' => $request->boolean('submit_enabled'),
            'submit_icon' => trim($data['submit_icon'] ?? 'bi-people-fill'),
            'submit_title' => trim($data['submit_title'] ?? ''),
            'submit_description' => trim($data['submit_description'] ?? ''),
            'submit_button_text' => trim($data['submit_button_text'] ?? ''),
            'submit_button_url' => trim($data['submit_button_url'] ?? '') ?: '/customer-register',
        ];

        $newsletter = [
            'newsletter_enabled' => $request->boolean('newsletter_enabled'),
            'newsletter_label' => trim($data['newsletter_label'] ?? 'STAY IN THE LOOP'),
            'newsletter_title' => trim($data['newsletter_title'] ?? 'Ideas worth exploring.'),
            'newsletter_description' => trim($data['newsletter_description'] ?? ''),
            'newsletter_placeholder' => trim($data['newsletter_placeholder'] ?? 'Your email address'),
            'newsletter_button_text' => trim($data['newsletter_button_text'] ?? 'Subscribe'),
            'newsletter_success_message' => trim($data['newsletter_success_message'] ?? 'Thank you for subscribing.'),
        ];

        DB::transaction(function () use ($hero, $siteSearchPlaceholder, $statistics, $why, $submit, $newsletter): void {
            $heroLabels = [
                'hero_enabled' => 'Show homepage hero section',
                'hero_eyebrow' => 'Hero eyebrow',
                'hero_title' => 'Hero headline',
                'hero_description' => 'Hero description',
                'hero_search_button_text' => 'Hero search button text',
                'hero_popular_label' => 'Popular searches label',
                'hero_popular_searches' => 'Popular search terms',
                'hero_primary_button_text' => 'Primary CTA text',
                'hero_primary_button_url' => 'Primary CTA URL',
                'hero_secondary_button_text' => 'Secondary CTA text',
                'hero_secondary_button_url' => 'Secondary CTA URL',
            ];
            $this->saveGroup('homepage_hero', $hero, $heroLabels);
            $this->saveGroup('website_search', [
                'site_search_placeholder' => $siteSearchPlaceholder,
            ], [
                'site_search_placeholder' => 'Search placeholder (header, hero and mobile)',
            ]);
            $this->saveGroup('homepage_statistics', $statistics);
            $this->saveGroup('homepage_why', $why);
            $this->saveGroup('homepage_submit', $submit);
            $this->saveGroup('footer_newsletter', $newsletter, [
                'newsletter_enabled' => 'Show footer newsletter form',
                'newsletter_label' => 'Newsletter eyebrow label',
                'newsletter_title' => 'Newsletter heading',
                'newsletter_description' => 'Newsletter description',
                'newsletter_placeholder' => 'Email input placeholder',
                'newsletter_button_text' => 'Subscribe button text',
                'newsletter_success_message' => 'Success message',
            ]);
        });

        return redirect()->route('admin.homepage-manager.index')
            ->with('success', 'Homepage content updated successfully.');
    }

    public function storeProfileSection(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_enabled' => ['nullable', 'boolean'],
            'profile_ids' => ['nullable', 'array', 'max:30'],
            'profile_ids.*' => ['integer', 'exists:profiles,id'],
        ]);

        $section = HomepageSection::create([
            'section_key' => $this->uniqueSectionKey($data['title']),
            'section_type' => 'profiles',
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'settings' => ['card_style' => 'profile'],
            'is_enabled' => $request->boolean('is_enabled'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->syncProfileItems($section, $data['profile_ids'] ?? []);
        return back()->with('success', 'Homepage profile section created successfully.');
    }

    public function updateProfileSection(Request $request, HomepageSection $section)
    {
        abort_unless($section->section_type === 'profiles', 404);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_enabled' => ['nullable', 'boolean'],
            'profile_ids' => ['nullable', 'array', 'max:30'],
            'profile_ids.*' => ['integer', 'exists:profiles,id'],
        ]);

        $section->update([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);
        $this->syncProfileItems($section, $data['profile_ids'] ?? []);

        return back()->with('success', 'Homepage profile section updated successfully.');
    }

    public function destroyProfileSection(HomepageSection $section)
    {
        abort_unless($section->section_type === 'profiles', 404);
        $section->delete();
        return back()->with('success', 'Homepage profile section removed.');
    }

    private function syncProfileItems(HomepageSection $section, array $profileIds): void
    {
        $validIds = Profile::published()->whereIn('id', $profileIds)->pluck('id')->all();
        $section->items()->delete();
        foreach (array_values(array_unique($validIds)) as $index => $profileId) {
            $section->items()->create([
                'profile_id' => $profileId,
                'item_type' => 'profile',
                'is_enabled' => true,
                'sort_order' => $index,
            ]);
        }
    }

    private function uniqueSectionKey(string $title): string
    {
        $base = Str::limit(Str::slug($title, '_') ?: 'profile_section', 90, '');
        $key = $base;
        $suffix = 2;
        while (HomepageSection::where('section_key', $key)->exists()) {
            $key = Str::limit($base, 96, '') . '_' . $suffix++;
        }
        return $key;
    }

    private function settingsForGroup(string $group, array $defaults): array
    {
        $stored = HomepageSetting::query()
            ->where('group_key', $group)
            ->get()
            ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])
            ->all();

        foreach ($stored as $key => $value) {
            if ($value !== null) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    private function saveGroup(string $group, array $settings, array $labels = []): void
    {
        foreach ($settings as $key => $value) {
            HomepageSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'group_key' => $group,
                    'label' => $labels[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                    'field_type' => is_bool($value) ? 'toggle' : (is_array($value) ? 'list' : 'text'),
                    'value' => $value,
                ]
            );
        }
    }

    private function validateUrl(string $url, string $field, bool $allowAnchor): void
    {
        $isAnchor = $allowAnchor && preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $url) === 1;
        $isPath = Str::startsWith($url, '/') && !Str::startsWith($url, '//');
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $isExternal = filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) $scheme), ['http', 'https'], true);

        if ($url !== '' && !$isAnchor && !$isPath && !$isExternal) {
            throw ValidationException::withMessages([
                $field => $allowAnchor
                    ? 'Enter an anchor, an internal path beginning with /, or a valid https:// URL.'
                    : 'Enter a valid https:// URL or an internal path beginning with /.',
            ]);
        }
    }
}
