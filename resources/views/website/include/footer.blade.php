@php
    $brandName = trim((string) ($siteSetting->site_name ?: 'CEOWiki'));
    $brandPrefix = \Illuminate\Support\Str::startsWith(strtolower($brandName), 'ceo') ? 'CEO' : null;
    $brandSuffix = $brandPrefix ? trim(substr($brandName, 3)) : null;
@endphp
<footer class="site-footer {{ request()->routeIs('customer.*') ? 'account-footer' : '' }}" id="about">
    <div class="container-xl">
        <div class="footer-grid">
            <div class="footer-brand">
                <a class="brand" href="{{ route('site.home') }}">
                    <span class="brand-mark home-brand-mark">
                        @if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}">@else<img src="{{ asset('public/website/assets/images/brand-mark-reference.png') }}" alt="">@endif
                    </span>
                    <span class="brand-copy"><span class="brand-name">@if($brandPrefix)CEO<span>{{ $brandSuffix ?: 'Wiki' }}</span>@else{{ $brandName }}@endif</span><span class="brand-tag">{{ $siteSetting->tagline ?: 'The Encyclopedia of CEOs and Companies' }}</span></span>
                </a>
                @if($siteSetting->footer_text)<p class="footer-note">{{ $siteSetting->footer_text }}</p>@endif
            </div>
            <div class="footer-col"><h6>Explore</h6><a href="{{ route('site.entrepreneurs.index') }}">CEOs</a><a href="{{ route('site.companies.index') }}">Companies</a><a href="{{ route('site.profiles.index') }}">All Profiles</a></div>
            <div class="footer-col"><h6>About</h6><a href="{{ route('site.about-us') }}">About {{ $siteSetting->site_name }}</a><a href="{{ route('site.privacy') }}">Privacy Policy</a><a href="{{ route('site.terms') }}">Terms &amp; Conditions</a></div>
            <div class="footer-col"><h6>Support</h6><a href="{{ route('site.contact-us') }}">Contact</a><a href="{{ route('customer.register') }}">Request a Profile</a><a href="{{ route('site.contact-us') }}">Suggest an Edit</a></div>
            <div class="footer-col footer-subscribe-col">
                    <h6>{{ $newsletterSettings['newsletter_label'] ?? 'Stay Updated' }}</h6>
                    <p>{{ $newsletterSettings['newsletter_description'] ?? 'Get the latest profiles and updates.' }}</p>
                    @if($newsletterSettings['newsletter_enabled'] ?? true)
                        @if(session('newsletter_success'))<p class="small text-success" role="status">{{ session('newsletter_success') }}</p>@endif
                        <form class="footer-subscribe" data-server-form method="post" action="{{ route('newsletter.subscribe') }}">
                            @csrf
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ $newsletterSettings['newsletter_placeholder'] ?? 'Your email address' }}" aria-label="Your email address" required>
                            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px">
                            <button class="btn btn-blue" type="submit">{{ $newsletterSettings['newsletter_button_text'] ?? 'Subscribe' }}</button>
                        </form>
                        @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                    @else
                        <span class="footer-note">Newsletter subscriptions are currently paused.</span>
                    @endif
                </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ date('Y') }} {{ $siteSetting->site_name }}. All rights reserved.</span>
            <div class="footer-bottom-right">
                <span class="footer-note">{{ $siteSetting->footer_text ?: 'An independent encyclopedia of CEOs and companies.' }}</span>
                <span class="social-links">
                    @if($siteSetting->facebook_url)<a href="{{ $siteSetting->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>@endif
                    @if($siteSetting->instagram_url)<a href="{{ $siteSetting->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>@endif
                    @if($siteSetting->linkedin_url)<a href="{{ $siteSetting->linkedin_url }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>@endif
                    @if($siteSetting->youtube_url)<a href="{{ $siteSetting->youtube_url }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="bi bi-youtube"></i></a>@endif
                </span>
            </div>
        </div>
    </div>
</footer>
