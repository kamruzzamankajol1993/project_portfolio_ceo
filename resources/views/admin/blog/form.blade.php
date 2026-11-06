@extends('admin.master.master')
@section('title',$post->exists?'Edit Blog Post':'Create Blog Post')
@section('body')
<div class="module-heading"><div><div class="eyebrow">WEBSITE CONTENT</div><h1><span class="heading-icon"><i class="bi bi-journal-richtext"></i></span>{{ $post->exists?'Edit blog post':'Create blog post' }}</h1><p>Write an article, set a clean URL slug and optimize search metadata.</p></div><a href="{{ route('admin.blog.index') }}" class="module-secondary"><i class="bi bi-arrow-left"></i> Back to blog</a></div>
<form class="module-panel module-form blog-editor" method="post" enctype="multipart/form-data" action="{{ $post->exists?route('admin.blog.update',$post):route('admin.blog.store') }}">
  @csrf @if($post->exists)@method('PUT')@endif
  <div class="blog-editor-top"><div><span class="soft-tag tag-violet">CONTENT EDITOR</span><h2 class="mt-2 mb-1">Article details</h2><p class="ops-note mb-0">Keep the title clear and the URL short, descriptive and readable.</p></div><div class="blog-status-field"><label for="blog-status">Publishing status</label><select class="form-select js-choice" id="blog-status" name="status"><option value="draft" @selected(old('status',$post->status??'draft')==='draft')>Draft</option><option value="published" @selected(old('status',$post->status)==='published')>Published</option></select></div></div>
  <div class="form-grid blog-main-fields">
    <label class="wide">Post title *<input id="blog-title" data-slug-source class="form-control" name="title" value="{{ old('title',$post->title) }}" maxlength="255" placeholder="e.g. How to Build a Strong Business Profile" required></label>
    <label class="wide">SEO-friendly slug *<div class="slug-input-wrap"><span>{{ url('/blog') }}/</span><input id="blog-slug" class="form-control" name="slug" data-slug-target value="{{ old('slug',$post->slug) }}" maxlength="180" placeholder="how-to-build-a-strong-business-profile" pattern="[A-Za-z0-9]+(-[A-Za-z0-9]+)*" required></div><small class="form-hint">Auto-generated from the title. Use English letters, numbers and hyphens only. You can edit it manually.</small></label>
    <label class="wide">Short excerpt<textarea class="form-control" name="excerpt" rows="2" maxlength="500" placeholder="A short summary shown on the blog listing...">{{ old('excerpt',$post->excerpt) }}</textarea></label>
    <label class="wide">Article content *<textarea class="form-control js-summernote" name="content" rows="12" required>{{ old('content',$post->content) }}</textarea></label>
    <label>Featured image<input class="form-control" type="file" name="featured_image" accept="image/jpeg,image/png,image/webp">@if($post->featured_image_url)<img class="blog-feature-preview" src="{{ $post->featured_image_url }}" alt="Current featured image">@endif</label>
    <label>Publish date/time<input class="form-control" type="datetime-local" name="published_at" value="{{ old('published_at',$post->published_at?->format('Y-m-d\\TH:i')) }}"><small class="form-hint">If blank, current time is used when publishing.</small></label>
  </div>
  <details class="blog-seo-details" open><summary><i class="bi bi-search"></i> SEO settings <span class="soft-tag">Optional</span></summary>
    <div class="form-grid">
      <label>Meta title<input class="form-control" name="meta_title" value="{{ old('meta_title',$post->meta_title) }}" maxlength="255" placeholder="Search result title"></label>
      <label>Meta robots<select class="form-select" name="meta_robots">@foreach(['index,follow','noindex,follow','index,nofollow','noindex,nofollow'] as $robots)<option value="{{ $robots }}" @selected(old('meta_robots',$post->meta_robots??'index,follow')===$robots)>{{ $robots }}</option>@endforeach</select></label>
      <label class="wide">Meta description<textarea class="form-control" name="meta_description" rows="2" maxlength="500" placeholder="A concise search description...">{{ old('meta_description',$post->meta_description) }}</textarea></label>
      <label class="wide">Meta keywords<input class="form-control" name="meta_keywords" value="{{ old('meta_keywords',$post->meta_keywords) }}" placeholder="business, entrepreneur, profile"></label>
      <label class="wide">Canonical URL<input class="form-control" type="url" name="canonical_url" value="{{ old('canonical_url',$post->canonical_url) }}" placeholder="https://example.com/blog/article-slug"></label>
    </div>
  </details>
  <div class="form-actions"><button class="module-btn"><i class="bi bi-check2-circle"></i> {{ $post->exists?'Save changes':'Save post' }}</button><a class="module-secondary" href="{{ route('admin.blog.index') }}">Cancel</a></div>
</form>
@endsection
