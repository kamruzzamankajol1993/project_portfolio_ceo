@extends('website.master.master')

@section('title', $extraPage->meta_title ?: $extraPage->title . ' | ' . $siteSetting->site_name)

@section('css')
<link rel="stylesheet" href="{{ asset('public/site/css/content.css') }}">
@endsection

@section('body')
<main class="site-content-wrap legal-wrap">
  <section class="content-hero legal-hero">
    <span class="content-kicker">WEBSITE INFORMATION</span>
    <h1>{{ $extraPage->title }}</h1>
    <p>Last updated: {{ $extraPage->updated_at?->format('d F Y') ?? now()->format('d F Y') }}</p>
  </section>
  <article class="legal-content">
    {!! $extraPage->content ?: '<p>This page is being prepared. Please check back soon.</p>' !!}
  </article>
</main>
@endsection
