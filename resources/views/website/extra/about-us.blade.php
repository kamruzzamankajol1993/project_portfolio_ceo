@extends('website.master.master')

@section('title', $extraPage->meta_title ?: $extraPage->title . ' | ' . $siteSetting->site_name)

@section('css')
<link rel="stylesheet" href="{{ asset('public/site/css/content.css') }}">
@endsection

@section('body')
<main class="site-content-wrap about-page-wrap">
  <section class="content-hero about-hero">
    <span class="content-kicker"><i class="bi bi-compass-fill"></i> ABOUT THE PLATFORM</span>
    <h1>{{ $extraPage->title }}</h1>
    <p>{{ $extraPage->meta_description ?: ($siteSetting->tagline ?: 'Discover the people, ideas and organisations shaping business.') }}</p>
    <div class="about-hero-meta"><span><i class="bi bi-journal-check"></i> Thoughtful profiles</span><span><i class="bi bi-diagram-3"></i> Business stories</span><span><i class="bi bi-shield-check"></i> Responsible information</span></div>
  </section>

  <section class="about-intro-grid">
    <article class="about-story-card">
      <div class="about-section-heading"><span class="about-section-icon"><i class="bi bi-book-half"></i></span><div><span class="content-kicker">WHO WE ARE</span><h2>Making business stories easier to discover</h2></div></div>
      <div class="legal-content about-rich-content">
        {!! $extraPage->content ?: '<p>' . e($siteSetting->site_name) . ' is a platform documenting entrepreneurs, founders, business leaders and the organisations they build.</p><p>Our goal is to make useful professional and company information easier to discover. We bring together stories, profile information and sourced achievements in one accessible place.</p><p>We value accuracy, transparency and responsible contributions. Public profile information is reviewed before publication, and readers can contact our team with corrections or suggestions.</p>' !!}
      </div>
    </article>
    <aside class="about-purpose-card">
      <span class="about-purpose-orbit"><i class="bi bi-lightbulb-fill"></i></span>
      <span class="content-kicker">OUR PURPOSE</span>
      <h2>Stories that inform. Profiles that inspire.</h2>
      <p>{{ $siteSetting->tagline ?: 'An independent space to explore entrepreneurs, founders and business leaders.' }}</p>
      <a href="{{ route('site.contact-us') }}" class="about-contact-link">Connect with our team <i class="bi bi-arrow-up-right"></i></a>
    </aside>
  </section>

  <section class="about-principles-section">
    <div class="about-principles-heading"><span class="content-kicker">WHAT GUIDES US</span><h2>Our principles</h2><p>The standards that shape how information is presented across {{ $siteSetting->site_name }}.</p></div>
    <div class="about-values-grid">
      <article class="about-value-card about-value-blue"><span><i class="bi bi-patch-check-fill"></i></span><h3>Accuracy</h3><p>We encourage clear sourcing, careful review and responsible corrections.</p></article>
      <article class="about-value-card about-value-violet"><span><i class="bi bi-universal-access-circle"></i></span><h3>Accessibility</h3><p>We make professional stories and company information easier to explore.</p></article>
      <article class="about-value-card about-value-mint"><span><i class="bi bi-people-fill"></i></span><h3>Contribution</h3><p>We welcome useful suggestions from people who know the stories best.</p></article>
    </div>
  </section>

  <section class="about-bottom-cta"><div><span>HAVE A STORY TO SHARE?</span><h2>Help us make the picture more complete.</h2><p>Send a correction, suggest a profile or get in touch with the team.</p></div><a href="{{ route('site.contact-us') }}">Contact us <i class="bi bi-arrow-right"></i></a></section>
</main>
@endsection
