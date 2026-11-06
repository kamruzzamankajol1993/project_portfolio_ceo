@php
    $seo = $siteSetting;
    $viewTitle = trim($__env->yieldContent('title'));
    $path = '/' . ltrim(request()->path(), '/');
    $page = \Illuminate\Support\Facades\Schema::hasTable('seo_pages')
        ? \App\Models\SeoPage::where('path', $path)->where('enabled', true)->first()
        : null;
    $contentRecord = $post ?? $extraPage ?? null;
    $profileSeo = isset($version) ? $version->seo : null;
    $profileImage = isset($version) ? ($version->mediaLinks->firstWhere('role', 'portrait')?->media?->url ?: $version->mediaLinks->firstWhere('role', 'logo')?->media?->url ?: $version->mediaLinks->firstWhere('role', 'banner')?->media?->url) : null;
    $contentTitle = $contentRecord?->meta_title ?: $contentRecord?->title;
    $routeTitle = request()->routeIs('blog.index') ? 'Blog | ' . $seo->site_name : null;
    $title = $contentTitle ?: ($profileSeo?->meta_title ?: ($page?->title ?: ($routeTitle ?: ($viewTitle ?: ($seo->seo_title ?: $seo->site_name)))));
    $routeDescription = request()->routeIs('blog.index') ? 'Articles, practical guides and stories for entrepreneurs and growing businesses.' : null;
    $description = $contentRecord?->meta_description ?: ($profileSeo?->meta_description ?: ((isset($post) ? $post->excerpt : null) ?: ($page?->description ?: ($routeDescription ?: ($seo->seo_description ?: $seo->tagline)))));
    $keywords = $contentRecord?->meta_keywords ?: ($profileSeo?->meta_keywords ?: ($page?->keywords ?: $seo->seo_keywords));
    $robots = $contentRecord?->meta_robots ?: ($profileSeo?->robots ?: ($page?->robots ?: $seo->seo_robots));
    $canonical = $contentRecord?->canonical_url ?: ($profileSeo?->canonical_url ?: ($page?->canonical ?: ($path === '/' ? ($seo->seo_canonical ?: ($seo->site_url ?: url('/'))) : url()->current())));
    $ogTitle = $contentRecord?->title ?: ($profileSeo?->og_title ?: ($profileSeo?->meta_title ?: ($page?->og_title ?: $title)));
    $ogDescription = $contentRecord?->meta_description ?: ($profileSeo?->og_description ?: ($profileSeo?->meta_description ?: ((isset($post) ? $post->excerpt : null) ?: ($page?->og_description ?: $description))));
    $ogImage = (isset($post) ? $post->featured_image_url : null) ?: ($profileSeo?->og_image ?: ($profileImage ?: ($page?->imageUrl() ?: $seo->publicUrl('seo_og_image'))));
    $ogType = isset($post) ? 'article' : $seo->seo_og_type;
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
@if($keywords)<meta name="keywords" content="{{ $keywords }}">@endif
<meta name="robots" content="{{ $robots }}">
@if($canonical)<link rel="canonical" href="{{ $canonical }}">@endif
<meta property="og:site_name" content="{{ $seo->site_name }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
@if($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endif
@if(isset($post) && $post->published_at)<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">@endif
<meta name="twitter:card" content="{{ $seo->seo_twitter_card }}">
<meta name="twitter:title" content="{{ $profileSeo?->twitter_title ?: $ogTitle }}">
<meta name="twitter:description" content="{{ $profileSeo?->twitter_description ?: $ogDescription }}">
@if($profileSeo?->twitter_image)<meta name="twitter:image" content="{{ $profileSeo->twitter_image }}">@endif
@if($seo->google_site_verification)<meta name="google-site-verification" content="{{ $seo->google_site_verification }}">@endif
@if($seo->publicUrl('favicon'))<link rel="icon" href="{{ $seo->publicUrl('favicon') }}">@endif
@if($seo->google_analytics_id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($seo->google_analytics_id) }}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($seo->google_analytics_id));</script>
@endif
{!! $seo->head_scripts !!}
