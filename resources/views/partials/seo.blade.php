@php
    $seo = $siteSetting;
    $viewTitle = trim($__env->yieldContent('title'));
    $path = '/' . trim(request()->path(), '/');
    if ($path === '') {
        $path = '/';
    }

    // A page-level SEO record is the highest-priority admin override for this URL.
    $page = \Illuminate\Support\Facades\Schema::hasTable('seo_pages')
        ? \App\Models\SeoPage::query()->where('path', $path)->where('enabled', true)->first()
        : null;

    $contentRecord = $post ?? $extraPage ?? null;
    $profileSeo = isset($version) ? $version->seo : null;
    $profileImage = isset($version)
        ? ($version->mediaLinks->firstWhere('role', 'portrait')?->media?->url
            ?: $version->mediaLinks->firstWhere('role', 'logo')?->media?->url
            ?: $version->mediaLinks->firstWhere('role', 'banner')?->media?->url)
        : null;

    $contentTitle = $contentRecord?->meta_title ?: $contentRecord?->title;
    $routeTitle = request()->routeIs('blog.index') ? 'Blog | ' . $seo->site_name : null;
    $title = request()->routeIs('site.home')
        ? "CEOWiki — Discover the World's Business Leaders"
        : ($page?->title
            ?: ($contentTitle
                ?: ($profileSeo?->meta_title
                    ?: ($routeTitle ?: ($viewTitle ?: ($seo->seo_title ?: $seo->site_name))))));

    $routeDescription = request()->routeIs('blog.index')
        ? 'Articles, practical guides and stories for entrepreneurs and growing businesses.'
        : null;
    $postDescription = isset($post) ? $post->excerpt : null;
    $description = $page?->description
        ?: ($contentRecord?->meta_description
            ?: ($profileSeo?->meta_description
                ?: ($postDescription ?: ($routeDescription ?: ($seo->seo_description ?: $seo->tagline)))));

    $keywords = $page?->keywords ?: ($contentRecord?->meta_keywords ?: ($profileSeo?->meta_keywords ?: $seo->seo_keywords));
    $robots = $page?->robots ?: ($contentRecord?->meta_robots ?: ($profileSeo?->robots ?: ($seo->seo_robots ?: 'index,follow')));

    $canonical = $page?->canonical
        ?: ($contentRecord?->canonical_url
            ?: ($profileSeo?->canonical_url
                ?: ($path === '/' ? ($seo->seo_canonical ?: ($seo->site_url ?: url('/'))) : url()->current())));

    $ogTitle = $page?->og_title
        ?: ($contentRecord?->meta_title
            ?: ($contentRecord?->title
                ?: ($profileSeo?->og_title ?: ($profileSeo?->meta_title ?: $title))));
    $ogDescription = $page?->og_description
        ?: ($contentRecord?->meta_description
            ?: ($profileSeo?->og_description
                ?: ($profileSeo?->meta_description ?: ($postDescription ?: $description))));
    $ogImage = $page?->imageUrl()
        ?: ((isset($post) ? $post->featured_image_url : null)
            ?: ($profileSeo?->og_image ?: ($profileImage ?: $seo->publicUrl('seo_og_image'))));
    $ogType = isset($post) ? 'article' : ($seo->seo_og_type ?: 'website');

    $twitterTitle = $profileSeo?->twitter_title ?: $ogTitle;
    $twitterDescription = $profileSeo?->twitter_description ?: $ogDescription;
    $twitterImage = $profileSeo?->twitter_image ?: $ogImage;

    $faviconUrl = $seo->publicUrl('favicon');
    $faviconPath = $seo->favicon ? parse_url($seo->favicon, PHP_URL_PATH) : null;
    $faviconExtension = strtolower(pathinfo((string) $faviconPath, PATHINFO_EXTENSION));
    $faviconType = match ($faviconExtension) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        default => 'image/webp',
    };
@endphp

<title>{{ $title }}</title>
@if($description)<meta name="description" content="{{ $description }}">@endif
@if($keywords)<meta name="keywords" content="{{ $keywords }}">@endif
<meta name="robots" content="{{ $robots }}">
@if($canonical)<link rel="canonical" href="{{ $canonical }}">@endif

<meta property="og:site_name" content="{{ $seo->site_name }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical ?: url()->current() }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
@if($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endif
@if(isset($post) && $post->published_at)<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">@endif

<meta name="twitter:card" content="{{ $seo->seo_twitter_card ?: 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $twitterTitle }}">
<meta name="twitter:description" content="{{ $twitterDescription }}">
@if($twitterImage)<meta name="twitter:image" content="{{ $twitterImage }}">@endif

@if($seo->google_site_verification)
    <meta name="google-site-verification" content="{{ $seo->google_site_verification }}">
@endif
@if($faviconUrl)
    <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}?v={{ $seo->updated_at?->timestamp ?? '1' }}">
    <link rel="shortcut icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}?v={{ $seo->updated_at?->timestamp ?? '1' }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}?v={{ $seo->updated_at?->timestamp ?? '1' }}">
@endif

@if($seo->google_analytics_id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($seo->google_analytics_id) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){window.dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @json($seo->google_analytics_id));
    </script>
@endif

{!! $seo->head_scripts !!}
