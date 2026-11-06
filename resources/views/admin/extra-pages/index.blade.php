@extends('admin.master.master')
@section('title','Extra Pages')
@section('body')
<div class="module-heading">
  <div><div class="eyebrow">WEBSITE CONTENT</div><h1><span class="heading-icon"><i class="bi bi-file-earmark-richtext"></i></span>Extra Pages</h1><p>Manage About Us, Contact Us, Terms & Conditions and Privacy Policy content, including SEO metadata.</p></div>
</div>
@php($publishedPages = $pages->where('is_published', true)->count())
<div class="ops-stats extra-page-admin-stats">
  <div class="ops-stat"><i class="bi bi-files"></i><div><strong>{{ $pages->count() }}</strong><span>Managed pages</span></div></div>
  <div class="ops-stat mint"><i class="bi bi-globe2"></i><div><strong>{{ $publishedPages }}</strong><span>Published</span></div></div>
  <div class="ops-stat violet"><i class="bi bi-file-earmark-lock"></i><div><strong>{{ $pages->count() - $publishedPages }}</strong><span>Draft / hidden</span></div></div>
</div>
<div class="ops-tabbar extra-page-tabs">
  <div class="ops-tab-links">
    @foreach($pages as $page)
      @php($tabIcon = ['about'=>'bi-info-circle','contact'=>'bi-envelope','terms'=>'bi-file-earmark-check','privacy'=>'bi-shield-lock'][$page->page_key] ?? 'bi-file-earmark-text')
      <a href="{{ route('admin.extra-pages.index',['tab'=>$page->slug]) }}" class="{{ $activeSlug===$page->slug?'is-active':'' }}"><i class="bi {{ $tabIcon }}"></i> {{ $page->title }}</a>
    @endforeach
  </div>
  <select class="form-select form-select-sm ops-tab-select" data-extra-page-tab aria-label="Select extra page">
    @foreach($pages as $page)<option value="{{ route('admin.extra-pages.index',['tab'=>$page->slug]) }}" @selected($activeSlug===$page->slug)>{{ $page->title }}</option>@endforeach
  </select>
</div>
<section class="module-panel module-form extra-page-editor">
  @php($previewRoute = match($editing->slug) {
    'about-us' => route('site.about-us'),
    'contact-us' => route('site.contact-us'),
    'terms-and-conditions' => route('site.terms'),
    default => route('site.privacy'),
  })
  <div class="ops-section-heading"><h2><i class="bi bi-pencil-square"></i> Edit {{ $editing->title }}</h2><div class="extra-page-state"><span class="soft-tag {{ $editing->is_published?'tag-active':'tag-inactive' }}">{{ $editing->is_published?'Published':'Draft / Hidden' }}</span><a class="module-secondary" href="{{ $previewRoute }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Preview</a></div></div>
  <form method="post" action="{{ route('admin.extra-pages.update',$editing) }}">@csrf @method('PUT')
    <div class="form-grid">
      <label>Page title *<input class="form-control" name="title" value="{{ old('title',$editing->title) }}" maxlength="255" required></label>
      <label>Fixed public slug<div class="slug-input-wrap"><span>{{ url('/') }}/</span><input class="form-control" value="{{ $editing->slug }}" readonly></div><small class="form-hint">The public URL is fixed so existing links do not break.</small></label>
      <label class="wide">Page content<textarea class="form-control js-summernote" name="content" rows="12">{{ old('content',$editing->content) }}</textarea></label>
    </div>
    <details class="blog-seo-details" open><summary><i class="bi bi-search"></i> SEO settings <span class="soft-tag">Page metadata</span></summary>
      <div class="form-grid">
        <label>Meta title<input class="form-control" name="meta_title" value="{{ old('meta_title',$editing->meta_title) }}" maxlength="255"></label>
        <label>Meta robots<select class="form-select" name="meta_robots">@foreach(['index,follow','noindex,follow','index,nofollow','noindex,nofollow'] as $robots)<option value="{{ $robots }}" @selected(old('meta_robots',$editing->meta_robots)===$robots)>{{ $robots }}</option>@endforeach</select></label>
        <label class="wide">Meta description<textarea class="form-control" name="meta_description" rows="2" maxlength="500">{{ old('meta_description',$editing->meta_description) }}</textarea></label>
        <label class="wide">Meta keywords<input class="form-control" name="meta_keywords" value="{{ old('meta_keywords',$editing->meta_keywords) }}"></label>
        <label class="wide">Canonical URL<input class="form-control" type="url" name="canonical_url" value="{{ old('canonical_url',$editing->canonical_url) }}" placeholder="https://example.com/{{ $editing->slug }}"></label>
      </div>
    </details>
    <div class="extra-page-publish-row"><label class="checkrow"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$editing->is_published))> Publish this page on the public website</label><small class="form-hint">Unpublished pages are hidden and their public URL returns 404. Add content before publishing.</small></div>
    <div class="form-actions"><button class="module-btn"><i class="bi bi-save"></i> Save {{ $editing->title }}</button></div>
  </form>
</section>
@endsection
