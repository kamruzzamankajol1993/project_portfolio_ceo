@extends('website.master.master')

@section('title', 'Blog | ' . $siteSetting->site_name)

@section('css')
<link rel="stylesheet" href="{{ asset('public/site/css/content.css') }}">
@endsection

@section('body')
<main class="site-content-wrap blog-page-wrap">
  <section class="content-hero">
    <span class="content-kicker">INSIGHTS & STORIES</span>
    <h1>Our Blog</h1>
    <p>Ideas, practical guides and stories for entrepreneurs, founders and growing businesses.</p>
  </section>

  <div class="blog-public-grid">
    @forelse($posts as $post)
      <article class="public-post-card" data-post-card data-url="{{ route('blog.show', $post->slug) }}" role="link" tabindex="0" aria-label="Read {{ $post->title }}">
        <a class="public-post-image" href="{{ route('blog.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
          @if($post->featured_image_url)
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
          @else
            <span><i class="bi bi-journal-richtext"></i></span>
          @endif
        </a>
        <div class="public-post-copy">
          <div class="public-post-meta">
            <span><i class="bi bi-calendar3"></i> {{ $post->published_at?->format('d M Y') ?? $post->created_at->format('d M Y') }}</span>
            @if($post->author)<span><i class="bi bi-person"></i> {{ $post->author->name }}</span>@endif
          </div>
          <h2><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
          <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 150) }}</p>
          <a class="read-post-link" href="{{ route('blog.show', $post->slug) }}">Read article <i class="bi bi-arrow-right"></i></a>
        </div>
      </article>
    @empty
      <div class="content-empty"><i class="bi bi-journal-x"></i><h2>No articles published yet</h2><p>New stories and resources will appear here soon.</p></div>
    @endforelse
  </div>

  @if($posts->hasPages())
    <nav class="content-pagination" aria-label="Blog pagination">
      <span>Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</span>
      <div>
        @if($posts->onFirstPage())<span class="disabled">Previous</span>@else<a href="{{ $posts->previousPageUrl() }}">Previous</a>@endif
        @if($posts->hasMorePages())<a href="{{ $posts->nextPageUrl() }}">Next</a>@else<span class="disabled">Next</span>@endif
      </div>
    </nav>
  @endif
</main>
@endsection

@section('scripts')
<script>
  document.querySelectorAll('[data-post-card]').forEach(card => {
    const openPost = event => {
      if (event.target.closest('a')) return;
      window.location.assign(card.dataset.url);
    };
    card.addEventListener('click', openPost);
    card.addEventListener('keydown', event => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        window.location.assign(card.dataset.url);
      }
    });
  });
</script>
@endsection
