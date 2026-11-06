@extends('admin.master.master')
@section('title','Blog Management')
@section('body')
<div class="module-heading">
  <div><div class="eyebrow">WEBSITE CONTENT</div><h1><span class="heading-icon"><i class="bi bi-journal-richtext"></i></span>Blog</h1><p>Create SEO-friendly articles and control when they appear publicly.</p></div>
  <a class="module-btn" href="{{ route('admin.blog.create') }}"><i class="bi bi-plus-lg"></i> New post</a>
</div>
<div class="ops-stats blog-stats">
  <div class="ops-stat"><i class="bi bi-journal-text"></i><div><strong>{{ number_format($stats['total']) }}</strong><span>Total posts</span></div></div>
  <div class="ops-stat mint"><i class="bi bi-globe2"></i><div><strong>{{ number_format($stats['published']) }}</strong><span>Published</span></div></div>
  <div class="ops-stat violet"><i class="bi bi-file-earmark"></i><div><strong>{{ number_format($stats['drafts']) }}</strong><span>Drafts</span></div></div>
</div>
<section class="module-panel module-list-panel">
  <form class="module-toolbar" method="get" action="{{ route('admin.blog.index') }}" data-ajax-filter>
    <div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Blog posts</div>
    <div class="blog-filter-tools">
      <select class="form-select" name="status" aria-label="Filter by status"><option value="">All status</option><option value="published" @selected(request('status')==='published')>Published</option><option value="draft" @selected(request('status')==='draft')>Draft</option></select>
      <label class="module-search"><i class="bi bi-search" aria-hidden="true"></i><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search title or slug..." aria-label="Live search blog posts" autocomplete="off"><span class="module-search-shortcut">LIVE</span></label>
    </div>
  </form>
  <div data-ajax-list aria-live="polite">@include('admin.blog._table')</div>
</section>
@endsection
