@extends('website.master.master')

@php
    $isPersonal = $profile->profile_type === \App\Models\Profile::TYPE_PERSONAL;
    $displayName = $isPersonal ? $details->full_name : $details->company_name;
    $displaySubtitle = $isPersonal
        ? ($details->professional_title ?: ($details->headline ?: $details->current_position))
        : ($details->tagline ?: $details->business_type);
    $displayDescription = $isPersonal ? $details->short_bio : $details->short_description;
    $media = $version->mediaLinks->keyBy('role');
    $portraitUrl = $media->get('portrait')?->media?->url ?: asset('public/website/assets/nazim-founder.jpg');
    $logoUrl = $media->get('logo')?->media?->url ?: asset('public/website/assets/company-logo.jpg');
    $bannerUrl = $media->get('banner')?->media?->url ?: asset('public/website/assets/company-banner.jpg');
    $gallery = $version->mediaLinks->where('role', 'gallery');
    $website = $isPersonal ? $details->personal_website : $details->official_website;
    $publicEmail = $isPersonal ? $details->public_contact_email : $details->public_email;
    $showEmail = $isPersonal ? $details->show_public_email : $details->show_public_email;
    $showPhone = $details->show_public_phone;
@endphp

@section('title'){{ $displayName }} | {{ $siteSetting->site_name }}@endsection

@section('css')
<link href="{{ asset('/') }}public/website/profile-pages.css" rel="stylesheet">
@endsection

@section('body')
<main class="profile-page">
    @if(!$isPersonal)
        <section class="company-hero" style="background-image:linear-gradient(90deg,rgba(246,250,255,.99) 0%,rgba(246,250,255,.97) 48%,rgba(246,250,255,.76) 64%,rgba(246,250,255,.05) 100%),url('{{ $bannerUrl }}')">
            <div class="company-hero-inner">
                <div class="company-logo-card"><img src="{{ $logoUrl }}" alt="{{ $displayName }} logo"></div>
                <div class="company-hero-copy">
                    <div class="company-title-row"><h1>{{ $displayName }}</h1>@if($profile->is_verified)<span class="verified-large" title="Verified company"><i class="bi bi-patch-check-fill"></i></span>@endif</div>
                    @if($displaySubtitle)<p class="company-tagline">{{ $displaySubtitle }}</p>@endif
                    @if($displayDescription)<p class="company-summary">{{ $displayDescription }}</p>@endif
                    <div class="company-meta">
                        @if($details->headquarters)<span><i class="bi bi-geo-alt"></i> {{ $details->headquarters }}</span>@endif
                        @if($website)<a href="{{ \Illuminate\Support\Str::startsWith($website, ['http://','https://']) ? $website : 'https://'.$website }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-globe2"></i> Official Website <i class="bi bi-box-arrow-up-right external-icon"></i></a>@endif
                        @if($showEmail && $publicEmail)<a href="mailto:{{ $publicEmail }}"><i class="bi bi-envelope"></i> {{ $publicEmail }}</a>@endif
                        @if($showPhone && $details->public_phone)<a href="tel:{{ $details->public_phone }}"><i class="bi bi-telephone"></i> {{ $details->public_phone }}</a>@endif
                    </div>
                </div>
            </div>
        </section>

        <div class="profile-main-shell company-content-layout">
            <div class="company-main-column">
                @if($details->overview)<section class="profile-section"><h2 class="profile-section-title">About</h2><p class="section-paragraph">{!! nl2br(e($details->overview)) !!}</p></section>@endif
                @if($details->history_background)<section class="profile-section"><h2 class="profile-section-title">History &amp; Background</h2><p class="section-paragraph">{!! nl2br(e($details->history_background)) !!}</p></section>@endif
                @if($details->mission)<section class="profile-section"><h2 class="profile-section-title">Mission</h2><p class="section-paragraph">{!! nl2br(e($details->mission)) !!}</p></section>@endif
                @if($details->vision)<section class="profile-section"><h2 class="profile-section-title">Vision</h2><p class="section-paragraph">{!! nl2br(e($details->vision)) !!}</p></section>@endif

                @foreach($version->sections->where('is_visible', true) as $section)
                    <section class="profile-section"><h2 class="profile-section-title">{{ $section->title }}</h2>@if($section->intro)<p class="section-paragraph">{!! nl2br(e($section->intro)) !!}</p>@endif
                        @if($section->items->where('is_visible', true)->isNotEmpty())
                            <div class="operation-grid">
                                @foreach($section->items->where('is_visible', true) as $item)
                                    <article class="operation-card"><span class="operation-icon"><i class="bi {{ $item->icon ?: 'bi-check-circle' }}"></i></span><div>@if($item->title)<h3>{{ $item->title }}</h3>@endif @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif @if($item->description)<p>{!! nl2br(e($item->description)) !!}</p>@endif @if($item->url)<a class="inline-link" href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">Learn more <i class="bi bi-arrow-up-right"></i></a>@endif</div></article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach

                @if($gallery->isNotEmpty())<section class="profile-section"><h2 class="profile-section-title">Gallery</h2><div class="company-impact-grid">@foreach($gallery as $imageLink)<img src="{{ $imageLink->media?->url }}" alt="{{ $imageLink->media?->alt_text ?: $displayName }}" loading="lazy" style="width:100%;height:150px;object-fit:cover;border-radius:9px">@endforeach</div></section>@endif

                @if($version->references->isNotEmpty())<section class="profile-section references-section"><h2 class="profile-section-title">References &amp; Sources</h2><ul class="reference-list">@foreach($version->references as $reference)<li><i class="bi bi-file-earmark-text"></i><span>@if($reference->url)<a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer">{{ $reference->title }} <i class="bi bi-box-arrow-up-right"></i></a>@else{{ $reference->title }}@endif @if($reference->publisher)<small> — {{ $reference->publisher }}</small>@endif</span></li>@endforeach</ul></section>@endif
            </div>

            <aside class="company-sidebar">
                <section class="sidebar-card company-details-card"><h2 class="sidebar-title"><i class="bi bi-buildings-fill"></i>Company Details</h2><dl class="details-list">
                    <div><dt>Name</dt><dd>{{ $details->company_name }}</dd></div>
                    @if($details->business_type)<div><dt>Type</dt><dd>{{ $details->business_type }}</dd></div>@endif
                    @if($details->founded_year)<div><dt>Founded</dt><dd>{{ $details->founded_year }}</dd></div>@endif
                    @if($details->company_size)<div><dt>Company size</dt><dd>{{ $details->company_size }}</dd></div>@endif
                    @if($details->industry)<div><dt>Industry</dt><dd>{{ $details->industry->name }}</dd></div>@endif
                    @if($details->headquarters)<div><dt>Headquarters</dt><dd>{{ $details->headquarters }}</dd></div>@endif
                    @if($details->area_served)<div><dt>Area served</dt><dd>{{ $details->area_served }}</dd></div>@endif
                    @if($details->parent_company)<div><dt>Parent company</dt><dd>{{ $details->parent_company }}</dd></div>@endif
                    @if($website)<div><dt>Website</dt><dd><a href="{{ \Illuminate\Support\Str::startsWith($website, ['http://','https://']) ? $website : 'https://'.$website }}" target="_blank" rel="noopener noreferrer">{{ $website }} <i class="bi bi-box-arrow-up-right"></i></a></dd></div>@endif
                </dl></section>
                @if($version->relationships->where('is_visible', true)->isNotEmpty())<section class="sidebar-card"><h2 class="sidebar-title"><i class="bi bi-people-fill"></i>Leadership &amp; Relationships</h2><ul class="connect-list">@foreach($version->relationships->where('is_visible', true) as $relationship)@php($related = $relationship->relatedProfile)@if($related && $related->status === 'published')@php($relatedDetails = $related->profile_type === 'personal' ? $related->publishedVersion?->personalDetails : $related->publishedVersion?->companyDetails)@php($relatedName = $related->profile_type === 'personal' ? ($relatedDetails?->full_name ?? $related->slug) : ($relatedDetails?->company_name ?? $related->slug))<li><i class="bi bi-person-badge"></i><span>{{ str($relationship->relation_type)->replace('_',' ')->title() }}</span><a href="{{ route('site.profile.show',$related) }}">{{ $relatedName }}</a>@if($relationship->position)<small class="d-block text-muted">{{ $relationship->position }}</small>@endif @if($relationship->description)<small class="d-block text-muted">{{ $relationship->description }}</small>@endif</li>@endif @endforeach</ul></section>@endif
                @if($version->links->where('is_visible', true)->isNotEmpty())<section class="sidebar-card connect-card"><h2 class="sidebar-title"><i class="bi bi-share-fill"></i>Connect</h2><ul class="connect-list">@foreach($version->links->where('is_visible', true) as $link)<li><i class="bi bi-link-45deg"></i><span>{{ $link->label ?: str($link->link_type)->title() }}</span><a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">Visit</a><i class="bi bi-box-arrow-up-right external-icon"></i></li>@endforeach</ul></section>@endif
            </aside>
        </div>
    @else
        <div class="profile-main-shell individual-profile-layout">
            <section class="individual-intro" aria-labelledby="person-name">
                <img class="individual-portrait" src="{{ $portraitUrl }}" alt="{{ $displayName }}">
                <div class="individual-intro-copy">
                    <div class="individual-name-row"><h1 id="person-name">{{ $displayName }}</h1>@if($profile->is_verified)<span class="verified-large" title="Verified profile"><i class="bi bi-patch-check-fill"></i></span>@endif</div>
                    @if($displaySubtitle)<p class="individual-title">{{ $displaySubtitle }}</p>@endif
                    <div class="individual-roles">@if($details->occupation)<span>{{ $details->occupation }}</span>@endif @if($details->industry)<i>•</i><span>{{ $details->industry->name }}</span>@endif @if($details->company_organisation)<i>•</i><span>{{ $details->company_organisation }}</span>@endif</div>
                    @if($displayDescription)<p class="individual-bio">{{ $displayDescription }}</p>@endif
                    <div class="individual-meta">
                        @if($details->location)<span><i class="bi bi-geo-alt"></i> {{ $details->location }}</span>@endif
                        @if($details->company_organisation)<span><i class="bi bi-buildings"></i> {{ $details->company_organisation }}</span>@endif
                        @if($website)<a href="{{ \Illuminate\Support\Str::startsWith($website, ['http://','https://']) ? $website : 'https://'.$website }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-link-45deg"></i> {{ $website }}</a>@endif
                        @if($showEmail && $publicEmail)<a href="mailto:{{ $publicEmail }}"><i class="bi bi-envelope"></i> {{ $publicEmail }}</a>@endif
                        @if($showPhone && $details->public_phone)<a href="tel:{{ $details->public_phone }}"><i class="bi bi-telephone"></i> {{ $details->public_phone }}</a>@endif
                    </div>
                </div>
            </section>

            @if($details->profile_quote)<aside class="quote-card"><span class="quote-mark">“</span><blockquote>{{ $details->profile_quote }}</blockquote><p>— {{ $displayName }}</p></aside>@endif

            <div class="individual-main">
                @if($details->about)<section class="profile-section"><h2 class="profile-section-title">About</h2><p class="section-paragraph">{!! nl2br(e($details->about)) !!}</p></section>@endif
                @if($details->early_life)<section class="profile-section"><h2 class="profile-section-title">Early Life</h2><p class="section-paragraph">{!! nl2br(e($details->early_life)) !!}</p></section>@endif
                @if($details->career_entrepreneurship)<section class="profile-section"><h2 class="profile-section-title">Career &amp; Entrepreneurship</h2><p class="section-paragraph">{!! nl2br(e($details->career_entrepreneurship)) !!}</p></section>@endif
                @if($details->education_summary)<section class="profile-section"><h2 class="profile-section-title">Education</h2><p class="section-paragraph">{!! nl2br(e($details->education_summary)) !!}</p></section>@endif
                @if($details->awards_publications_recognition)<section class="profile-section"><h2 class="profile-section-title">Awards, Publications &amp; Recognition</h2><p class="section-paragraph">{!! nl2br(e($details->awards_publications_recognition)) !!}</p></section>@endif

                @foreach($version->sections->where('is_visible', true) as $section)
                    <section class="profile-section"><h2 class="profile-section-title">{{ $section->title }}</h2>@if($section->intro)<p class="section-paragraph">{!! nl2br(e($section->intro)) !!}</p>@endif
                        @foreach($section->items->where('is_visible', true) as $item)<article class="individual-impact-card">@if($item->icon)<span><i class="bi {{ $item->icon }}"></i></span>@endif @if($item->title)<h3>{{ $item->title }}</h3>@endif @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif @if($item->description)<p>{!! nl2br(e($item->description)) !!}</p>@endif @if($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer">Learn more <i class="bi bi-box-arrow-up-right"></i></a>@endif</article>@endforeach
                    </section>
                @endforeach

                @if($gallery->isNotEmpty())<section class="profile-section"><h2 class="profile-section-title">Gallery</h2><div class="individual-impact-grid">@foreach($gallery as $imageLink)<img src="{{ $imageLink->media?->url }}" alt="{{ $imageLink->media?->alt_text ?: $displayName }}" loading="lazy" style="width:100%;height:150px;object-fit:cover;border-radius:9px">@endforeach</div></section>@endif
                @if($version->references->isNotEmpty())<section class="profile-section individual-references"><h2 class="profile-section-title">References</h2><ol class="individual-reference-list">@foreach($version->references as $reference)<li>@if($reference->url)<a href="{{ $reference->url }}" target="_blank" rel="noopener noreferrer">{{ $reference->title }} <i class="bi bi-box-arrow-up-right"></i></a>@else{{ $reference->title }}@endif @if($reference->publisher)<small> — {{ $reference->publisher }}</small>@endif</li>@endforeach</ol></section>@endif
            </div>

            <aside class="individual-sidebar">
                <section class="sidebar-card"><h2 class="sidebar-title sidebar-title-bar">Profile Summary</h2><dl class="summary-list">
                    @if($details->show_date_of_birth && $details->date_of_birth)<div><dt><i class="bi bi-calendar-event"></i> Date of Birth</dt><dd>{{ $details->date_of_birth->format('d F Y') }}</dd></div>@endif
                    @if($details->nationality)<div><dt><i class="bi bi-flag-fill"></i> Nationality</dt><dd>{{ $details->nationality }}</dd></div>@endif
                    @if($details->occupation)<div><dt><i class="bi bi-briefcase"></i> Occupation</dt><dd>{{ $details->occupation }}</dd></div>@endif
                    @if($details->company_organisation)<div><dt><i class="bi bi-buildings"></i> Organisation</dt><dd>{{ $details->company_organisation }}</dd></div>@endif
                    @if($details->hometown)<div><dt><i class="bi bi-house"></i> Hometown</dt><dd>{{ $details->hometown }}</dd></div>@endif
                    @if($details->education_summary)<div><dt><i class="bi bi-mortarboard"></i> Education</dt><dd>{{ \Illuminate\Support\Str::limit(strip_tags($details->education_summary), 150) }}</dd></div>@endif
                    @if($website)<div><dt><i class="bi bi-globe2"></i> Website</dt><dd><a href="{{ \Illuminate\Support\Str::startsWith($website, ['http://','https://']) ? $website : 'https://'.$website }}" target="_blank" rel="noopener noreferrer">{{ $website }} <i class="bi bi-box-arrow-up-right"></i></a></dd></div>@endif
                </dl></section>
                @if($version->links->where('is_visible', true)->isNotEmpty())<section class="sidebar-card"><h2 class="sidebar-title sidebar-title-bar">External Links</h2><ul class="external-links-list">@foreach($version->links->where('is_visible', true) as $link)<li><i class="bi bi-link-45deg"></i><span>{{ $link->label ?: str($link->link_type)->title() }}</span><a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">Visit <i class="bi bi-box-arrow-up-right"></i></a></li>@endforeach</ul></section>@endif
                @if($version->relationships->where('is_visible', true)->isNotEmpty())<section class="sidebar-card"><h2 class="sidebar-title sidebar-title-bar">Related Profiles</h2><ul class="external-links-list">@foreach($version->relationships->where('is_visible', true) as $relationship)@php($related = $relationship->relatedProfile)@if($related && $related->status === 'published')@php($relatedDetails = $related->profile_type === 'personal' ? $related->publishedVersion?->personalDetails : $related->publishedVersion?->companyDetails)@php($relatedName = $related->profile_type === 'personal' ? ($relatedDetails?->full_name ?? $related->slug) : ($relatedDetails?->company_name ?? $related->slug))<li><i class="bi bi-diagram-3"></i><span>{{ str($relationship->relation_type)->replace('_',' ')->title() }}</span><a href="{{ route('site.profile.show',$related) }}">{{ $relatedName }}</a>@if($relationship->position)<small class="d-block text-muted">{{ $relationship->position }}</small>@endif @if($relationship->description)<small class="d-block text-muted">{{ $relationship->description }}</small>@endif</li>@endif @endforeach</ul></section>@endif
            </aside>
        </div>
    @endif
</main>
@endsection
