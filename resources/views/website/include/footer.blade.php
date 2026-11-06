<footer class="site-footer page-shell container-fluid">
  <div class="footer-brand">
    <a class="brand-lockup" href="{{ $siteSetting->site_url ?: route('site.home') }}">
      <span class="brand-mark">@if($siteSetting->publicUrl('logo'))<img src="{{ $siteSetting->publicUrl('logo') }}" alt="{{ $siteSetting->site_name }}" width="28" height="28" style="max-width:100%;max-height:100%;object-fit:contain">@else<i class="bi bi-book-half"></i>@endif</span>
      <span><strong>{{ $siteSetting->site_name }}</strong><small>{{ $siteSetting->tagline ?: 'The Encyclopedia of Entrepreneurs' }}</small></span>
    </a>
    <p class="footer-brand-copy">Explore the people, companies and ideas shaping the world of business.</p>
    <div class="social-links">
      @if($siteSetting->facebook_url)<a href="{{ $siteSetting->facebook_url }}" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i></a>@endif
      @if($siteSetting->linkedin_url)<a href="{{ $siteSetting->linkedin_url }}" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer"><i class="bi bi-linkedin"></i></a>@endif
      @if($siteSetting->youtube_url)<a href="{{ $siteSetting->youtube_url }}" aria-label="YouTube" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i></a>@endif
      @if($siteSetting->instagram_url)<a href="{{ $siteSetting->instagram_url }}" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i></a>@endif
    </div>
  </div>
  <div class="footer-column"><h3>Explore</h3><a href="{{ route('site.profiles.index') }}">All Profiles</a><a href="{{ route('site.entrepreneurs.index') }}">Entrepreneurs</a><a href="{{ route('site.companies.index') }}">Companies</a><a href="{{ route('blog.index') }}">Blog &amp; insights</a></div>
  <div class="footer-column"><h3>Company</h3><a href="{{ route('site.about-us') }}">About Us</a><a href="{{ route('site.contact-us') }}">Contact Us</a><a href="{{ route('site.contact-us') }}">Corrections</a></div>
  <div class="footer-column"><h3>Information</h3><a href="{{ route('site.privacy') }}">Privacy Policy</a><a href="{{ route('site.terms') }}">Terms &amp; Conditions</a><a href="{{ route('blog.index') }}">Latest articles</a></div>
  @if($newsletterSettings['newsletter_enabled'] ?? true)
    <div class="footer-newsletter">
      <span class="footer-newsletter-label">{{ $newsletterSettings['newsletter_label'] ?? 'STAY IN THE LOOP' }}</span>
      <h3>{{ $newsletterSettings['newsletter_title'] ?? 'Ideas worth exploring.' }}</h3>
      <p>{{ $newsletterSettings['newsletter_description'] ?? ('Get the latest stories and updates from '.$siteSetting->site_name.'.') }}</p>

      @if(session('newsletter_success'))
        <p class="newsletter-feedback newsletter-success" role="status">{{ session('newsletter_success') }}</p>
      @endif
      <form method="post" action="{{ route('newsletter.subscribe') }}">
        @csrf
        <label class="visually-hidden" for="footer-newsletter-email">Email address</label>
        <input id="footer-newsletter-email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ $newsletterSettings['newsletter_placeholder'] ?? 'Your email address' }}" aria-label="{{ $newsletterSettings['newsletter_placeholder'] ?? 'Your email address' }}" autocomplete="email" required>
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px">
        <button type="submit" class="btn btn-primary">{{ $newsletterSettings['newsletter_button_text'] ?? 'Subscribe' }}</button>
      </form>
      @error('email')
        <p class="newsletter-feedback newsletter-error" role="alert">{{ $message }}</p>
      @enderror
    </div>
  @endif
  <div class="footer-bottom"><span>© {{ date('Y') }} {{ $siteSetting->site_name }}. All rights reserved.</span><span>{{ $siteSetting->footer_text ?: 'An independent encyclopedia of entrepreneurs.' }}</span></div>
</footer>
