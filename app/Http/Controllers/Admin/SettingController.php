<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PublicImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request, PublicImageService $images)
    {
        $data = $request->validate([
            'site_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'site_url' => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:2000',
            'footer_text' => 'nullable|string|max:2000',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:1000',
            'seo_robots' => ['required', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'seo_canonical' => 'nullable|url|max:255',
            'seo_og_type' => 'nullable|string|max:80',
            'seo_twitter_card' => 'required|in:summary,summary_large_image',
            'google_analytics_id' => 'nullable|string|max:100',
            'google_site_verification' => 'nullable|string|max:255',
            'head_scripts' => 'nullable|string|max:20000',
            'body_scripts' => 'nullable|string|max:20000',
            'logo' => 'nullable|image|mimes:jpeg,png,webp|max:3072',
            'favicon' => 'nullable|image|mimes:jpeg,png,webp|max:1024',
            'seo_og_image' => 'nullable|image|mimes:jpeg,png,webp|max:3072',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        $setting = app('site.settings');
        $data['maintenance_mode'] = $request->boolean('maintenance_mode');
        $uploadedOld = [];

        foreach (['logo', 'favicon', 'seo_og_image'] as $key) {
            unset($data[$key]);
            if ($request->hasFile($key)) {
                $new = $images->upload($request->file($key), 'settings', $key === 'favicon' ? 128 : 1200);
                $uploadedOld[] = $setting->{$key};
                $data[$key] = $new;
            }
        }

        $setting->update($data);

        foreach ($uploadedOld as $old) {
            $images->delete($old);
        }

        return back()->with('success', 'Site settings updated successfully.');
    }
}
