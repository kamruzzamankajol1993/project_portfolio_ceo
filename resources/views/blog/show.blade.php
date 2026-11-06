@extends('website.master.master')

@section('title', $post->meta_title ?: $post->title . ' | ' . $siteSetting->site_name)

@section('css')
<link rel="stylesheet" href="{{ asset('public/site/css/content.css') }}">
@endsection

@section('body')
<main class="site-content-wrap article-wrap">
  <a href="{{ route('blog.index') }}" class="back-to-blog"><i class="bi bi-arrow-left"></i> All articles</a>
  <article class="public-article">
    <header class="article-header">
      <span class="content-kicker">BLOG / ARTICLE</span>
      <h1>{{ $post->title }}</h1>
      <div class="public-post-meta">
        <span><i class="bi bi-calendar3"></i> {{ $post->published_at?->format('d F Y') ?? $post->created_at->format('d F Y') }}</span>
        @if($post->author)<span><i class="bi bi-person"></i> {{ $post->author->name }}</span>@endif
      </div>
      @if($post->excerpt)<p class="article-excerpt">{{ $post->excerpt }}</p>@endif
    </header>
    @if($post->featured_image_url)<img class="article-featured-image" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">@endif
    <div class="article-content">{!! $post->content !!}</div>
    <a href="{{ route('blog.index') }}" class="back-to-blog article-bottom-back"><i class="bi bi-arrow-left"></i> Back to all articles</a>
  </article>
</main>
@endsection
