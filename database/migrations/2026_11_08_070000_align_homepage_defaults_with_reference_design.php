<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $changes = [
        'homepage_hero' => [
            'hero_eyebrow' => ['PEOPLE • BUSINESSES • IDEAS • IMPACT', 'People • Companies • Leadership • Global Impact'],
            'hero_title' => ["Discover Entrepreneurs\nWho Shape a Brighter\nTomorrow.", "Discover the\nWorld’s Business\nLeaders."],
            'hero_description' => [
                'Explore the stories, careers and achievements of entrepreneurs, founders and business leaders from around the world.',
                'Search and explore detailed profiles of CEOs, founders and the companies they lead. Accurate. Neutral. Global.',
            ],
            'hero_popular_searches' => [
                ['Elon Musk', 'Jeff Bezos', 'Satya Nadella', 'Startups', 'Fintech'],
                ['Elon Musk', 'Satya Nadella', 'Jeff Bezos', 'Tim Cook', 'Google', 'Tesla'],
            ],
        ],
        'website_search' => [
            'site_search_placeholder' => ['Search entrepreneurs, companies, or industries...', 'Search CEO, company, or keyword...'],
        ],
        'homepage_statistics' => [
            'stat_1_value' => ['12,480+', null],
            'stat_1_label' => ['Entrepreneurs', 'CEOs'],
            'stat_2_value' => ['8,230+', null],
            'stat_2_caption' => ['With founder information', 'With CEO information'],
            'stat_3_value' => ['126', null],
            'stat_3_label' => ['Industries', 'Countries'],
            'stat_3_caption' => ['Business categories', 'Global coverage'],
            'stat_3_icon' => ['bi-diagram-3-fill', 'bi-globe'],
            'stat_4_value' => ['10,540+', null],
            'stat_4_label' => ['Published Profiles', 'Industries'],
            'stat_4_caption' => ['Verified and reviewed', 'Business sectors'],
            'stat_4_icon' => ['bi-file-earmark-text-fill', 'bi-diagram-3-fill'],
        ],
    ];

    public function up(): void
    {
        $this->applyChanges(forward: true);
        $this->alignFeaturedSection(forward: true);
    }

    public function down(): void
    {
        $this->alignFeaturedSection(forward: false);
        $this->applyChanges(forward: false);
    }

    private function alignFeaturedSection(bool $forward): void
    {
        if (!Schema::hasTable('homepage_sections')) {
            return;
        }

        $oldTitle = $forward ? 'Featured Entrepreneurs' : 'Featured CEOs';
        $newTitle = $forward ? 'Featured CEOs' : 'Featured Entrepreneurs';
        $oldSubtitle = $forward ? 'Selected entrepreneurs and business leaders' : 'Influential business leaders making a global impact.';
        $newSubtitle = $forward ? 'Influential business leaders making a global impact.' : 'Selected entrepreneurs and business leaders';

        DB::table('homepage_sections')
            ->where('section_key', 'featured_entrepreneurs')
            ->where('title', $oldTitle)
            ->update(['title' => $newTitle, 'updated_at' => now()]);

        DB::table('homepage_sections')
            ->where('section_key', 'featured_entrepreneurs')
            ->where('subtitle', $oldSubtitle)
            ->update(['subtitle' => $newSubtitle, 'updated_at' => now()]);
    }

    private function applyChanges(bool $forward): void
    {
        if (!Schema::hasTable('homepage_settings')) {
            return;
        }

        foreach ($this->changes as $group => $settings) {
            foreach ($settings as $key => [$oldValue, $newValue]) {
                $from = $forward ? $oldValue : $newValue;
                $to = $forward ? $newValue : $oldValue;

                $rows = DB::table('homepage_settings')
                    ->where('group_key', $group)
                    ->where('setting_key', $key)
                    ->get();

                foreach ($rows as $row) {
                    $storedValue = $row->value === null ? null : json_decode($row->value, true);
                    if ($storedValue !== $from) {
                        continue;
                    }

                    DB::table('homepage_settings')->where('id', $row->id)->update([
                        'value' => $to === null ? null : json_encode($to, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
