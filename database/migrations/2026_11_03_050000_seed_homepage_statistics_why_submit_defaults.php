<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Seed the initial homepage content into the existing homepage_settings table.
     * insertOrIgnore is intentional: if an administrator has already saved a value,
     * running this migration must never overwrite that customised content.
     */
    public function up(): void
    {
        if (!Schema::hasTable('homepage_settings')) {
            return;
        }

        $settings = [
            // Statistics section
            ['stats_enabled', 'homepage_statistics', 'Show statistics section', 'toggle', true],
            ['stat_1_value', 'homepage_statistics', 'Statistics 1 value', 'text', '12,480+'],
            ['stat_1_label', 'homepage_statistics', 'Statistics 1 label', 'text', 'Entrepreneurs'],
            ['stat_1_caption', 'homepage_statistics', 'Statistics 1 description', 'text', 'Published profiles'],
            ['stat_1_icon', 'homepage_statistics', 'Statistics 1 icon', 'text', 'bi-people-fill'],
            ['stat_1_color', 'homepage_statistics', 'Statistics 1 colour', 'text', 'blue'],
            ['stat_2_value', 'homepage_statistics', 'Statistics 2 value', 'text', '8,230+'],
            ['stat_2_label', 'homepage_statistics', 'Statistics 2 label', 'text', 'Companies'],
            ['stat_2_caption', 'homepage_statistics', 'Statistics 2 description', 'text', 'With founder information'],
            ['stat_2_icon', 'homepage_statistics', 'Statistics 2 icon', 'text', 'bi-buildings-fill'],
            ['stat_2_color', 'homepage_statistics', 'Statistics 2 colour', 'text', 'blue'],
            ['stat_3_value', 'homepage_statistics', 'Statistics 3 value', 'text', '126'],
            ['stat_3_label', 'homepage_statistics', 'Statistics 3 label', 'text', 'Industries'],
            ['stat_3_caption', 'homepage_statistics', 'Statistics 3 description', 'text', 'Business categories'],
            ['stat_3_icon', 'homepage_statistics', 'Statistics 3 icon', 'text', 'bi-diagram-3-fill'],
            ['stat_3_color', 'homepage_statistics', 'Statistics 3 colour', 'text', 'green'],
            ['stat_4_value', 'homepage_statistics', 'Statistics 4 value', 'text', '10,540+'],
            ['stat_4_label', 'homepage_statistics', 'Statistics 4 label', 'text', 'Published Profiles'],
            ['stat_4_caption', 'homepage_statistics', 'Statistics 4 description', 'text', 'Verified and reviewed'],
            ['stat_4_icon', 'homepage_statistics', 'Statistics 4 icon', 'text', 'bi-file-earmark-text-fill'],
            ['stat_4_color', 'homepage_statistics', 'Statistics 4 colour', 'text', 'blue'],

            // Why Choose Us section
            ['why_enabled', 'homepage_why', 'Show Why Choose Us section', 'toggle', true],
            ['why_title', 'homepage_why', 'Why section title', 'text', 'Why :site_name?'],
            ['why_description', 'homepage_why', 'Why section description', 'text', 'An independent platform documenting entrepreneurs, business leaders, companies and their professional achievements with accuracy and neutrality.'],
            ['why_icon', 'homepage_why', 'Why section icon', 'text', 'bi-book-half'],
            ['why_point_1_icon', 'homepage_why', 'Why point 1 icon', 'text', 'bi-shield-check'],
            ['why_point_1_title', 'homepage_why', 'Why point 1 title', 'text', 'Reliable Information'],
            ['why_point_1_caption', 'homepage_why', 'Why point 1 description', 'text', 'Fact-based and sourced'],
            ['why_point_2_icon', 'homepage_why', 'Why point 2 icon', 'text', 'bi-people'],
            ['why_point_2_title', 'homepage_why', 'Why point 2 title', 'text', 'Community Contributions'],
            ['why_point_2_caption', 'homepage_why', 'Why point 2 description', 'text', 'Help improve profiles'],
            ['why_point_3_icon', 'homepage_why', 'Why point 3 icon', 'text', 'bi-globe2'],
            ['why_point_3_title', 'homepage_why', 'Why point 3 title', 'text', 'Global Coverage'],
            ['why_point_3_caption', 'homepage_why', 'Why point 3 description', 'text', 'Entrepreneurs worldwide'],

            // Entrepreneur submission CTA
            ['submit_enabled', 'homepage_submit', 'Show entrepreneur submission banner', 'toggle', true],
            ['submit_icon', 'homepage_submit', 'Submission banner icon', 'text', 'bi-people-fill'],
            ['submit_title', 'homepage_submit', 'Submission banner title', 'text', 'Are You an Entrepreneur?'],
            ['submit_description', 'homepage_submit', 'Submission banner description', 'text', 'Submit your professional information for editorial review and become part of a trusted global resource.'],
            ['submit_button_text', 'homepage_submit', 'Submission button text', 'text', 'Request Your Profile'],
            ['submit_button_url', 'homepage_submit', 'Submission button URL', 'text', '/customer-register'],
        ];

        $timestamp = now();
        $rows = array_map(static function (array $item) use ($timestamp): array {
            [$key, $group, $label, $fieldType, $value] = $item;

            return [
                'setting_key' => $key,
                'group_key' => $group,
                'label' => $label,
                'field_type' => $fieldType,
                'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'sort_order' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $settings);

        DB::table('homepage_settings')->insertOrIgnore($rows);
    }

    /**
     * Keep homepage settings on rollback so administrator-edited content is not lost.
     */
    public function down(): void
    {
        // Intentionally left blank to preserve content managed by administrators.
    }
};
