<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GlobalNoticeController extends Controller
{
    public function index()
    {
        $siteName = app('site.settings')->site_name ?: 'EntrepreneurWiki';
        $noticeSettings = $this->settingsForGroup('website_header', [
            'announcement_enabled' => true,
            'announcement_label' => 'ANNOUNCEMENT',
            'announcement_badge' => 'New',
            'announcement_title' => $siteName . ' is now accepting profile submissions!',
            'announcement_description' => 'Entrepreneurs, founders and business leaders can request their profile to be published on ' . $siteName . '.',
            'announcement_button_text' => 'Submit Your Profile',
            'announcement_button_url' => '/customer-register',
        ]);

        return view('admin.global-notice.index', compact('noticeSettings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'announcement_enabled' => 'nullable|boolean',
            'announcement_label' => 'nullable|string|max:40',
            'announcement_badge' => 'nullable|string|max:30',
            'announcement_title' => 'nullable|string|max:220',
            'announcement_description' => 'nullable|string|max:500',
            'announcement_button_text' => 'nullable|string|max:80',
            'announcement_button_url' => 'nullable|string|max:500',
        ]);

        $notice = [
            'announcement_enabled' => $request->boolean('announcement_enabled'),
            'announcement_label' => trim($data['announcement_label'] ?? 'ANNOUNCEMENT'),
            'announcement_badge' => trim($data['announcement_badge'] ?? ''),
            'announcement_title' => trim($data['announcement_title'] ?? ''),
            'announcement_description' => trim($data['announcement_description'] ?? ''),
            'announcement_button_text' => trim($data['announcement_button_text'] ?? ''),
            'announcement_button_url' => trim($data['announcement_button_url'] ?? '/customer-register'),
        ];

        $this->validateUrl($notice['announcement_button_url']);

        DB::transaction(function () use ($notice): void {
            $labels = [
                'announcement_enabled' => 'Show global notice on public website',
                'announcement_label' => 'Global notice label',
                'announcement_badge' => 'Global notice badge',
                'announcement_title' => 'Global notice title',
                'announcement_description' => 'Global notice description',
                'announcement_button_text' => 'Global notice button text',
                'announcement_button_url' => 'Global notice button URL',
            ];

            foreach ($notice as $key => $value) {
                HomepageSetting::updateOrCreate(
                    ['setting_key' => $key],
                    [
                        'group_key' => 'website_header',
                        'label' => $labels[$key],
                        'field_type' => is_bool($value) ? 'toggle' : 'text',
                        'value' => $value,
                    ]
                );
            }
        });

        return redirect()->route('admin.global-notice.index')
            ->with('success', 'Global notice updated successfully.');
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

    private function validateUrl(string $url): void
    {
        $isPath = Str::startsWith($url, '/') && !Str::startsWith($url, '//');
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $isExternal = filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) $scheme), ['http', 'https'], true);

        if ($url !== '' && !$isPath && !$isExternal) {
            throw ValidationException::withMessages([
                'announcement_button_url' => 'Enter a valid https:// URL or an internal path beginning with /.',
            ]);
        }
    }
}
