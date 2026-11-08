<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_settings')) {
            $settingsRows = DB::table('site_settings')->get();

            foreach ($settingsRows as $row) {
                $updates = [];

                if (in_array(trim((string) ($row->site_name ?? '')), ['EntrepreneurWiki', 'Entrepreneur Wiki'], true)) {
                    $updates['site_name'] = 'CEOWiki';
                }

                if (!empty($row->seo_title) && stripos($row->seo_title, 'EntrepreneurWiki') !== false) {
                    $updates['seo_title'] = str_ireplace('EntrepreneurWiki', 'CEOWiki', $row->seo_title);
                }

                if (!empty($updates)) {
                    $updates['updated_at'] = now();
                    DB::table('site_settings')->where('id', $row->id)->update($updates);
                }
            }
        }

        if (Schema::hasTable('homepage_settings')) {
            $copyChanges = [
                'submit_title' => [
                    'old' => 'Are You an Entrepreneur?',
                    'new' => 'Be Part of CEOWiki',
                ],
                'submit_description' => [
                    'old' => 'Submit your professional information for editorial review and become part of a trusted global resource.',
                    'new' => 'Help us build a reliable and comprehensive encyclopedia of CEOs and the companies they lead.',
                ],
                'submit_button_text' => [
                    'old' => 'Request Your Profile',
                    'new' => 'Request a Profile',
                ],
            ];

            foreach ($copyChanges as $key => $change) {
                $rows = DB::table('homepage_settings')
                    ->where('group_key', 'homepage_submit')
                    ->where('setting_key', $key)
                    ->get();

                foreach ($rows as $row) {
                    $decoded = json_decode((string) $row->value, true);
                    $storedValue = json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;

                    // Only migrate the known starter copy. Custom Admin content is preserved.
                    if ($storedValue !== $change['old']) {
                        continue;
                    }

                    DB::table('homepage_settings')->where('id', $row->id)->update([
                        'value' => json_encode($change['new'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Preserve any brand or homepage copy subsequently edited by an administrator.
    }
};
