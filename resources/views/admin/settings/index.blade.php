@extends('admin.master.master')
@section('title','Site Settings & SEO')
@section('body')
<div class="module-heading"><div><div class="eyebrow">SITE CONFIGURATION</div><h1><span class="heading-icon"><i class="bi bi-sliders2-vertical"></i></span>Settings & SEO</h1><p>Manage general website details, contact information, branding, SEO and trusted scripts.</p></div></div>
<form class="module-panel module-form settings-panel" method="post" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
<h2 class="settings-title"><i class="bi bi-globe2"></i> General</h2><div class="form-grid">
<label>Site name<input class="form-control" type="text" name="site_name" value="{{ old('site_name',$siteSetting->site_name) }}" required></label>
<label>Tagline<input class="form-control" type="text" name="tagline" value="{{ old('tagline',$siteSetting->tagline) }}"></label>
<label>Site URL<input class="form-control" type="url" name="site_url" value="{{ old('site_url',$siteSetting->site_url) }}"></label>
<label class="wide">Footer text<textarea class="form-control" name="footer_text" rows="3">{{ old('footer_text',$siteSetting->footer_text) }}</textarea></label>
<label class="checkrow wide"><input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode',$siteSetting->maintenance_mode))> Maintenance mode</label>
</div>
<h2 class="settings-title"><i class="bi bi-headset"></i> Contact Information <small class="settings-subtitle">Displayed dynamically on the public Contact Us page</small></h2>
<div class="form-grid">
<label>Public contact email<input class="form-control" type="email" name="contact_email" value="{{ old('contact_email',$siteSetting->contact_email) }}" placeholder="hello@example.com"></label>
<label>Public contact phone<input class="form-control" type="text" name="contact_phone" value="{{ old('contact_phone',$siteSetting->contact_phone) }}" placeholder="+1 234 567 890"></label>
<label class="wide">Office / business address<textarea class="form-control" name="address" rows="3" placeholder="Enter the address shown on Contact Us">{{ old('address',$siteSetting->address) }}</textarea></label>
<p class="form-hint wide">Save these details here. The public Contact Us page reads them directly from the <code>site_settings</code> database record.</p>
</div>
<h2 class="settings-title"><i class="bi bi-palette"></i> Brand & Social</h2><div class="form-grid">
<label>Site logo<input class="form-control" type="file" name="logo" accept="image/jpeg,image/png,image/webp">@if($siteSetting->publicUrl('logo'))<img class="preview-image" src="{{ $siteSetting->publicUrl('logo') }}" alt="Site logo">@endif</label>
<label>Favicon<input class="form-control" type="file" name="favicon" accept="image/jpeg,image/png,image/webp">@if($siteSetting->publicUrl('favicon'))<img class="preview-image" src="{{ $siteSetting->publicUrl('favicon') }}" alt="Favicon">@endif</label>
<label>Facebook URL<input class="form-control" type="url" name="facebook_url" value="{{ old('facebook_url',$siteSetting->facebook_url) }}"></label>
<label>Instagram URL<input class="form-control" type="url" name="instagram_url" value="{{ old('instagram_url',$siteSetting->instagram_url) }}"></label>
<label>YouTube URL<input class="form-control" type="url" name="youtube_url" value="{{ old('youtube_url',$siteSetting->youtube_url) }}"></label>
<label>LinkedIn URL<input class="form-control" type="url" name="linkedin_url" value="{{ old('linkedin_url',$siteSetting->linkedin_url) }}"></label>
</div>
<h2 class="settings-title" id="seo-settings"><i class="bi bi-google"></i> Dynamic SEO</h2><div class="form-grid">
<label>SEO title<input class="form-control" type="text" name="seo_title" value="{{ old('seo_title',$siteSetting->seo_title) }}"></label>
<label class="wide">Meta description<textarea class="form-control" name="seo_description" rows="3">{{ old('seo_description',$siteSetting->seo_description) }}</textarea></label>
<label class="wide">Meta keywords<textarea class="form-control" name="seo_keywords" rows="3">{{ old('seo_keywords',$siteSetting->seo_keywords) }}</textarea></label>
<label>Robots<select class="form-select js-choice" name="seo_robots"><option value="index,follow" @selected(old('seo_robots',$siteSetting->seo_robots)==='index,follow')>index,follow</option><option value="noindex,follow" @selected(old('seo_robots',$siteSetting->seo_robots)==='noindex,follow')>noindex,follow</option><option value="index,nofollow" @selected(old('seo_robots',$siteSetting->seo_robots)==='index,nofollow')>index,nofollow</option><option value="noindex,nofollow" @selected(old('seo_robots',$siteSetting->seo_robots)==='noindex,nofollow')>noindex,nofollow</option></select></label>
<label>Canonical URL<input class="form-control" type="url" name="seo_canonical" value="{{ old('seo_canonical',$siteSetting->seo_canonical) }}"></label>
<label>Open Graph image<input class="form-control" type="file" name="seo_og_image" accept="image/jpeg,image/png,image/webp">@if($siteSetting->publicUrl('seo_og_image'))<img class="preview-image" src="{{ $siteSetting->publicUrl('seo_og_image') }}" alt="Open Graph image">@endif</label>
<label>Open Graph type<input class="form-control" type="text" name="seo_og_type" value="{{ old('seo_og_type',$siteSetting->seo_og_type) }}"></label>
<label>Twitter card<select class="form-select js-choice" name="seo_twitter_card"><option value="summary_large_image" @selected(old('seo_twitter_card',$siteSetting->seo_twitter_card)==='summary_large_image')>summary_large_image</option><option value="summary" @selected(old('seo_twitter_card',$siteSetting->seo_twitter_card)==='summary')>summary</option></select></label>
<label>Google Analytics ID<input class="form-control" type="text" name="google_analytics_id" value="{{ old('google_analytics_id',$siteSetting->google_analytics_id) }}"></label>
<label>Google site verification<input class="form-control" type="text" name="google_site_verification" value="{{ old('google_site_verification',$siteSetting->google_site_verification) }}"></label>
</div>
<h2 class="settings-title"><i class="bi bi-code-slash"></i> Advanced scripts (trusted admins only)</h2><div class="form-grid">
<label class="wide">Head scripts<textarea class="form-control" name="head_scripts" rows="3">{{ old('head_scripts',$siteSetting->head_scripts) }}</textarea></label>
<label class="wide">Body scripts<textarea class="form-control" name="body_scripts" rows="3">{{ old('body_scripts',$siteSetting->body_scripts) }}</textarea></label>
</div>
<div class="form-actions"><button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save settings</button></div></form>
@endsection
