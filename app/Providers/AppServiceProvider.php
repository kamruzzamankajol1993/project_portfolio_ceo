<?php

namespace App\Providers;

use App\Models\HomepageSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('site.settings', function () {
            $defaults = ['site_name' => 'CEOWiki'];

            try {
                if (!Schema::hasTable('site_settings')) {
                    return new SiteSetting($defaults);
                }

                return SiteSetting::current();
            } catch (Throwable $exception) {
                report($exception);

                return new SiteSetting($defaults);
            }
        });
    }

    public function boot(): void
    {
        Gate::before(fn (User $user, string $ability) => $user->isFullSuperAdmin() ? true : null);

        View::composer('*', function ($view): void {
            $view->with('siteSetting', $this->app->make('site.settings'));
        });

        View::composer('website.*', function ($view): void {
            $siteName = app('site.settings')->site_name ?: 'CEOWiki';
            $defaults = [
                'announcement_enabled' => true,
                'announcement_label' => 'ANNOUNCEMENT',
                'announcement_badge' => 'New',
                'announcement_title' => $siteName . ' is now accepting profile submissions!',
                'announcement_description' => 'Entrepreneurs, founders and business leaders can request their profile to be published on ' . $siteName . '.',
                'announcement_button_text' => 'Submit Your Profile',
                'announcement_button_url' => '/customer-register',
            ];

            try {
                if (Schema::hasTable('homepage_settings')) {
                    $stored = HomepageSetting::query()
                        ->where('group_key', 'website_header')
                        ->get()
                        ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])
                        ->all();
                    $defaults = array_merge($defaults, $stored);
                }
            } catch (Throwable $exception) {
                report($exception);
            }

            foreach ($defaults as $key => $value) {
                if ($value === null) {
                    $defaults[$key] = match ($key) {
                        'announcement_enabled' => true,
                        'announcement_label' => 'ANNOUNCEMENT',
                        'announcement_badge' => 'New',
                        'announcement_title' => $siteName . ' is now accepting profile submissions!',
                        'announcement_description' => 'Entrepreneurs, founders and business leaders can request their profile to be published on ' . $siteName . '.',
                        'announcement_button_text' => 'Submit Your Profile',
                        'announcement_button_url' => '/customer-register',
                        default => null,
                    };
                }
            }

            $view->with('announcementSettings', $defaults);

            $heroDefaults = [
                'hero_enabled' => true,
                'hero_eyebrow' => 'People • Companies • Leadership • Global Impact',
                'hero_title' => "Discover the\nWorld’s Business\nLeaders.",
                'hero_description' => 'Search and explore detailed profiles of CEOs, founders and the companies they lead. Accurate. Neutral. Global.',
                'hero_search_button_text' => 'Search',
                'hero_popular_label' => 'Popular searches:',
                'hero_popular_searches' => ['Elon Musk', 'Satya Nadella', 'Jeff Bezos', 'Tim Cook', 'Google', 'Tesla'],
                'hero_primary_button_text' => 'Explore Entrepreneurs',
                'hero_primary_button_url' => '#featured',
                'hero_secondary_button_text' => 'Create Your Profile',
                'hero_secondary_button_url' => '/customer-register',
            ];
            $searchDefaults = [
                'site_search_placeholder' => 'Search CEO, company, or keyword...',
            ];
            $statisticsDefaults = [
                'stats_enabled' => true,
                'stat_1_value' => null, 'stat_1_label' => 'CEOs', 'stat_1_caption' => 'Published profiles', 'stat_1_icon' => 'bi-people-fill', 'stat_1_color' => 'blue',
                'stat_2_value' => null, 'stat_2_label' => 'Companies', 'stat_2_caption' => 'With CEO information', 'stat_2_icon' => 'bi-buildings-fill', 'stat_2_color' => 'blue',
                'stat_3_value' => null, 'stat_3_label' => 'Countries', 'stat_3_caption' => 'Global coverage', 'stat_3_icon' => 'bi-globe', 'stat_3_color' => 'green',
                'stat_4_value' => null, 'stat_4_label' => 'Industries', 'stat_4_caption' => 'Business sectors', 'stat_4_icon' => 'bi-diagram-3-fill', 'stat_4_color' => 'blue',
            ];
            $whyDefaults = [
                'why_enabled' => true,
                'why_title' => 'Why ' . $siteName . '?',
                'why_description' => 'An independent platform documenting entrepreneurs, business leaders, companies and their professional achievements with accuracy and neutrality.',
                'why_icon' => 'bi-book-half',
                'why_point_1_icon' => 'bi-shield-check', 'why_point_1_title' => 'Reliable Information', 'why_point_1_caption' => 'Fact-based and sourced',
                'why_point_2_icon' => 'bi-people', 'why_point_2_title' => 'Community Contributions', 'why_point_2_caption' => 'Help improve profiles',
                'why_point_3_icon' => 'bi-globe2', 'why_point_3_title' => 'Global Coverage', 'why_point_3_caption' => 'Entrepreneurs worldwide',
            ];
            $submitDefaults = [
                'submit_enabled' => true,
                'submit_icon' => 'bi-people-fill',
                'submit_title' => 'Be Part of ' . $siteName,
                'submit_description' => 'Help us build a reliable and comprehensive encyclopedia of CEOs and the companies they lead.',
                'submit_button_text' => 'Request a Profile',
                'submit_button_url' => '/customer-register',
            ];

            try {
                if (Schema::hasTable('homepage_settings')) {
                    $heroStored = HomepageSetting::query()
                        ->where('group_key', 'homepage_hero')
                        ->get()
                        ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])
                        ->all();
                    $searchStored = HomepageSetting::query()
                        ->where('group_key', 'website_search')
                        ->get()
                        ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])
                        ->all();
                    $statisticsStored = HomepageSetting::query()->where('group_key', 'homepage_statistics')->get()
                        ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])->all();
                    $whyStored = HomepageSetting::query()->where('group_key', 'homepage_why')->get()
                        ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])->all();
                    $submitStored = HomepageSetting::query()->where('group_key', 'homepage_submit')->get()
                        ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])->all();
                    $heroDefaults = array_merge($heroDefaults, $heroStored);
                    $searchDefaults = array_merge($searchDefaults, $searchStored);
                    $statisticsDefaults = array_replace($statisticsDefaults, array_filter($statisticsStored, fn ($value) => $value !== null));
                    $whyDefaults = array_replace($whyDefaults, array_filter($whyStored, fn ($value) => $value !== null));
                    $submitDefaults = array_replace($submitDefaults, array_filter($submitStored, fn ($value) => $value !== null));
                }
            } catch (Throwable $exception) {
                report($exception);
            }

            foreach ($heroDefaults as $key => $value) {
                if ($value === null) {
                    $heroDefaults[$key] = match ($key) {
                        'hero_enabled' => true,
                        'hero_eyebrow' => 'People • Companies • Leadership • Global Impact',
                        'hero_title' => "Discover the\nWorld’s Business\nLeaders.",
                        'hero_description' => 'Search and explore detailed profiles of CEOs, founders and the companies they lead. Accurate. Neutral. Global.',
                        'hero_search_button_text' => 'Search',
                        'hero_popular_label' => 'Popular searches:',
                        'hero_popular_searches' => ['Elon Musk', 'Satya Nadella', 'Jeff Bezos', 'Tim Cook', 'Google', 'Tesla'],
                        'hero_primary_button_text' => 'Explore Entrepreneurs',
                        'hero_primary_button_url' => '#featured',
                        'hero_secondary_button_text' => 'Create Your Profile',
                        'hero_secondary_button_url' => '/customer-register',
                        default => null,
                    };
                }
            }

            if (is_string($heroDefaults['hero_popular_searches'] ?? null)) {
                $heroDefaults['hero_popular_searches'] = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $heroDefaults['hero_popular_searches']))));
            }
            if (!is_array($heroDefaults['hero_popular_searches'] ?? null)) {
                $heroDefaults['hero_popular_searches'] = [];
            }
            foreach ($searchDefaults as $key => $value) {
                if ($value === null || $value === '') {
                    $searchDefaults[$key] = 'Search CEO, company, or keyword...';
                }
            }

            $view->with('heroSettings', $heroDefaults);
            $view->with('searchSettings', $searchDefaults);
            $view->with('statisticsSettings', $statisticsDefaults);
            $view->with('whySettings', $whyDefaults);
            $view->with('submitSettings', $submitDefaults);

            $newsletterDefaults = [
                'newsletter_enabled' => true,
                'newsletter_label' => 'STAY IN THE LOOP',
                'newsletter_title' => 'Ideas worth exploring.',
                'newsletter_description' => 'Get the latest stories and updates from :site_name.',
                'newsletter_placeholder' => 'Your email address',
                'newsletter_button_text' => 'Subscribe',
                'newsletter_success_message' => 'Thank you for subscribing to our newsletter.',
            ];

            try {
                if (Schema::hasTable('homepage_settings')) {
                    $newsletterStored = HomepageSetting::query()
                        ->where('group_key', 'footer_newsletter')
                        ->get()
                        ->mapWithKeys(fn (HomepageSetting $setting) => [$setting->setting_key => $setting->value])
                        ->all();

                    $newsletterDefaults = array_replace($newsletterDefaults, array_filter(
                        $newsletterStored,
                        fn ($value) => $value !== null
                    ));
                }
            } catch (Throwable $exception) {
                report($exception);
            }

            $newsletterDefaults['newsletter_description'] = str_replace(
                ':site_name',
                $siteName,
                (string) $newsletterDefaults['newsletter_description']
            );

            $view->with('newsletterSettings', $newsletterDefaults);
        });
    }
}
