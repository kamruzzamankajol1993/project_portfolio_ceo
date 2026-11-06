@extends('admin.master.master')
@section('title','Homepage Manager')
@section('body')
<div class="module-heading">
  <div>
    <div class="eyebrow">WEBSITE CONTENT</div>
    <h1><span class="heading-icon"><i class="bi bi-house-gear"></i></span>Homepage Manager</h1>
    <p>Manage homepage sections, profile selections and the footer newsletter content in one place.</p>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success" role="alert">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-danger" role="alert"><strong>Please check the following fields:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="module-panel" style="margin-bottom:18px;padding:14px 18px;">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge bg-primary">5 Homepage Sections</span>
    <span class="text-muted small">Search · Hero · Statistics · Why Choose Us · Submission CTA</span>
    <a class="btn btn-sm btn-outline-primary ms-auto" href="{{ route('site.home') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Preview Website</a>
  </div>
</div>

<form class="module-panel module-form settings-panel" method="post" action="{{ route('admin.homepage-manager.update') }}">
  @csrf
  @method('PUT')
<h2 class="settings-title"><i class="bi bi-search"></i> Website Search</h2>
<div class="form-grid">
<label class="wide">Search placeholder for header, hero and mobile<input class="form-control" type="text" name="site_search_placeholder" maxlength="150" value="{{ old('site_search_placeholder', $searchSettings['site_search_placeholder'] ?? 'Search entrepreneurs, companies, or industries...') }}"></label>
<p class="form-hint wide">This placeholder is shared by all public website search boxes. Search suggestions are loaded with AJAX without reloading the page.</p>
</div>
<h2 class="settings-title"><i class="bi bi-layout-text-window-reverse"></i> Homepage Hero Content</h2>
<div class="form-grid">
<label class="checkrow wide"><input type="hidden" name="hero_enabled" value="0"><input type="checkbox" name="hero_enabled" value="1" @checked(old('hero_enabled', $heroSettings['hero_enabled'] ?? true))> Show homepage hero section</label>
<label class="wide">Eyebrow / top label<input class="form-control" type="text" name="hero_eyebrow" maxlength="180" value="{{ old('hero_eyebrow', $heroSettings['hero_eyebrow'] ?? 'PEOPLE • BUSINESSES • IDEAS • IMPACT') }}"></label>
<label class="wide">Hero headline<textarea class="form-control" name="hero_title" rows="3" maxlength="220" placeholder="Use a new line for each line of the heading">{{ old('hero_title', $heroSettings['hero_title'] ?? "Discover Entrepreneurs\nWho Shape a Brighter\nTomorrow.") }}</textarea><small class="form-hint">Line breaks are preserved on the homepage.</small></label>
<label class="wide">Hero description<textarea class="form-control" name="hero_description" rows="3" maxlength="1000">{{ old('hero_description', $heroSettings['hero_description'] ?? 'Explore the stories, careers and achievements of entrepreneurs, founders and business leaders from around the world.') }}</textarea></label>
<label>Search button text<input class="form-control" type="text" name="hero_search_button_text" maxlength="40" value="{{ old('hero_search_button_text', $heroSettings['hero_search_button_text'] ?? 'Search') }}"></label>
<label>Popular searches label<input class="form-control" type="text" name="hero_popular_label" maxlength="60" value="{{ old('hero_popular_label', $heroSettings['hero_popular_label'] ?? 'Popular searches:') }}"></label>
<label class="wide">Popular search terms<textarea class="form-control" name="hero_popular_searches" rows="3" maxlength="1000" placeholder="One term per line">{{ old('hero_popular_searches', $heroSettings['hero_popular_searches'] ?? "Elon Musk\nJeff Bezos\nSatya Nadella\nStartups\nFintech") }}</textarea><small class="form-hint">Enter one search term per line (up to 12). Clicking a term on the website starts an AJAX search.</small></label>
<label>Primary button text<input class="form-control" type="text" name="hero_primary_button_text" maxlength="80" value="{{ old('hero_primary_button_text', $heroSettings['hero_primary_button_text'] ?? 'Explore Entrepreneurs') }}"></label>
<label>Primary button URL<input class="form-control" type="text" name="hero_primary_button_url" maxlength="500" value="{{ old('hero_primary_button_url', $heroSettings['hero_primary_button_url'] ?? '#featured') }}" placeholder="#featured or /personal-profile"></label>
<label>Secondary button text<input class="form-control" type="text" name="hero_secondary_button_text" maxlength="80" value="{{ old('hero_secondary_button_text', $heroSettings['hero_secondary_button_text'] ?? 'Create Your Profile') }}"></label>
<label>Secondary button URL<input class="form-control" type="text" name="hero_secondary_button_url" maxlength="500" value="{{ old('hero_secondary_button_url', $heroSettings['hero_secondary_button_url'] ?? '/customer-register') }}" placeholder="/customer-register or https://example.com"></label>
<p class="form-hint wide">Hero content is saved in the existing homepage_settings table. You can edit the copy, change popular searches, update both CTA buttons or hide the hero section without changing Blade files.</p>
</div>
<h2 class="settings-title"><i class="bi bi-bar-chart-line"></i> Homepage Statistics</h2>
<div class="form-grid">
<label class="checkrow wide"><input type="hidden" name="stats_enabled" value="0"><input type="checkbox" name="stats_enabled" value="1" @checked(old('stats_enabled', $statisticsSettings['stats_enabled'] ?? true))> Show statistics section on homepage</label>
@for($i = 1; $i <= 4; $i++)
  <div class="wide settings-subpanel" style="border:1px solid #e8edf5;border-radius:10px;padding:14px;margin:4px 0;">
    <strong>Statistics Card {{ $i }}</strong>
    <div class="form-grid">
      <label>Value<input class="form-control" type="text" name="stat_{{ $i }}_value" maxlength="60" value="{{ old('stat_'.$i.'_value', $statisticsSettings['stat_'.$i.'_value'] ?? '') }}"></label>
      <label>Label<input class="form-control" type="text" name="stat_{{ $i }}_label" maxlength="100" value="{{ old('stat_'.$i.'_label', $statisticsSettings['stat_'.$i.'_label'] ?? '') }}"></label>
      <label>Short description<input class="form-control" type="text" name="stat_{{ $i }}_caption" maxlength="160" value="{{ old('stat_'.$i.'_caption', $statisticsSettings['stat_'.$i.'_caption'] ?? '') }}"></label>
      <label>Bootstrap icon class<input class="form-control" type="text" name="stat_{{ $i }}_icon" maxlength="80" value="{{ old('stat_'.$i.'_icon', $statisticsSettings['stat_'.$i.'_icon'] ?? 'bi-people-fill') }}" placeholder="bi-people-fill"></label>
      <label>Icon colour<select class="form-select" name="stat_{{ $i }}_color"><option value="blue" @selected(old('stat_'.$i.'_color', $statisticsSettings['stat_'.$i.'_color'] ?? 'blue') === 'blue')>Blue</option><option value="green" @selected(old('stat_'.$i.'_color', $statisticsSettings['stat_'.$i.'_color'] ?? 'blue') === 'green')>Green</option></select></label>
    </div>
  </div>
@endfor
<p class="form-hint wide">All four statistic cards are editable. The displayed values are editorial values entered here; they are not automatically calculated from database totals.</p>
</div>

<h2 class="settings-title"><i class="bi bi-patch-question"></i> Why Choose Us Section</h2>
<div class="form-grid">
<label class="checkrow wide"><input type="hidden" name="why_enabled" value="0"><input type="checkbox" name="why_enabled" value="1" @checked(old('why_enabled', $whySettings['why_enabled'] ?? true))> Show Why Choose Us section</label>
<label>Section title<input class="form-control" type="text" name="why_title" maxlength="220" value="{{ old('why_title', $whySettings['why_title'] ?? 'Why :site_name?') }}"></label>
<label>Intro icon class<input class="form-control" type="text" name="why_icon" maxlength="80" value="{{ old('why_icon', $whySettings['why_icon'] ?? 'bi-book-half') }}" placeholder="bi-book-half"></label>
<label class="wide">Section description<textarea class="form-control" name="why_description" rows="3" maxlength="1000">{{ old('why_description', $whySettings['why_description'] ?? '') }}</textarea></label>
@for($i = 1; $i <= 3; $i++)
  <div class="wide settings-subpanel" style="border:1px solid #e8edf5;border-radius:10px;padding:14px;margin:4px 0;">
    <strong>Why Point {{ $i }}</strong>
    <div class="form-grid">
      <label>Icon class<input class="form-control" type="text" name="why_point_{{ $i }}_icon" maxlength="80" value="{{ old('why_point_'.$i.'_icon', $whySettings['why_point_'.$i.'_icon'] ?? 'bi-shield-check') }}" placeholder="bi-shield-check"></label>
      <label>Title<input class="form-control" type="text" name="why_point_{{ $i }}_title" maxlength="120" value="{{ old('why_point_'.$i.'_title', $whySettings['why_point_'.$i.'_title'] ?? '') }}"></label>
      <label class="wide">Short description<input class="form-control" type="text" name="why_point_{{ $i }}_caption" maxlength="180" value="{{ old('why_point_'.$i.'_caption', $whySettings['why_point_'.$i.'_caption'] ?? '') }}"></label>
    </div>
  </div>
@endfor
<p class="form-hint wide">Use <code>:site_name</code> in the title if you want the current website name to appear automatically.</p>
</div>

<h2 class="settings-title"><i class="bi bi-person-plus"></i> Entrepreneur Profile Submission CTA</h2>
<div class="form-grid">
<label class="checkrow wide"><input type="hidden" name="submit_enabled" value="0"><input type="checkbox" name="submit_enabled" value="1" @checked(old('submit_enabled', $submitSettings['submit_enabled'] ?? true))> Show entrepreneur submission banner</label>
<label>Icon class<input class="form-control" type="text" name="submit_icon" maxlength="80" value="{{ old('submit_icon', $submitSettings['submit_icon'] ?? 'bi-people-fill') }}" placeholder="bi-people-fill"></label>
<label>Button text<input class="form-control" type="text" name="submit_button_text" maxlength="80" value="{{ old('submit_button_text', $submitSettings['submit_button_text'] ?? 'Request Your Profile') }}"></label>
<label class="wide">Heading<input class="form-control" type="text" name="submit_title" maxlength="220" value="{{ old('submit_title', $submitSettings['submit_title'] ?? 'Are You an Entrepreneur?') }}"></label>
<label class="wide">Description<textarea class="form-control" name="submit_description" rows="3" maxlength="1000">{{ old('submit_description', $submitSettings['submit_description'] ?? '') }}</textarea></label>
<label class="wide">Button URL / internal path<input class="form-control" type="text" name="submit_button_url" maxlength="500" value="{{ old('submit_button_url', $submitSettings['submit_button_url'] ?? '/customer-register') }}" placeholder="/customer-register or https://example.com"></label>
<p class="form-hint wide">This banner can be edited or hidden without changing the homepage Blade template.</p>
</div>


<h2 class="settings-title"><i class="bi bi-envelope-paper"></i> Footer Newsletter</h2>
<div class="form-grid">
  <label class="checkrow wide"><input type="hidden" name="newsletter_enabled" value="0"><input type="checkbox" name="newsletter_enabled" value="1" @checked(old('newsletter_enabled', $newsletterSettings['newsletter_enabled'] ?? true))> Show newsletter signup in the website footer</label>
  <label>Eyebrow label<input class="form-control" type="text" name="newsletter_label" maxlength="100" value="{{ old('newsletter_label', $newsletterSettings['newsletter_label'] ?? 'STAY IN THE LOOP') }}"></label>
  <label>Heading<input class="form-control" type="text" name="newsletter_title" maxlength="180" value="{{ old('newsletter_title', $newsletterSettings['newsletter_title'] ?? 'Ideas worth exploring.') }}"></label>
  <label class="wide">Description<textarea class="form-control" name="newsletter_description" rows="2" maxlength="500">{{ old('newsletter_description', $newsletterSettings['newsletter_description'] ?? 'Get the latest stories and updates from :site_name.') }}</textarea><small class="form-hint">Use :site_name to insert your website name.</small></label>
  <label>Email placeholder<input class="form-control" type="text" name="newsletter_placeholder" maxlength="120" value="{{ old('newsletter_placeholder', $newsletterSettings['newsletter_placeholder'] ?? 'Your email address') }}"></label>
  <label>Button text<input class="form-control" type="text" name="newsletter_button_text" maxlength="50" value="{{ old('newsletter_button_text', $newsletterSettings['newsletter_button_text'] ?? 'Subscribe') }}"></label>
  <label class="wide">Success message<textarea class="form-control" name="newsletter_success_message" rows="2" maxlength="250">{{ old('newsletter_success_message', $newsletterSettings['newsletter_success_message'] ?? 'Thank you for subscribing to our newsletter.') }}</textarea></label>
  <p class="form-hint wide mb-0">Newsletter content is saved in the existing homepage_settings table. Email addresses submitted by visitors are stored in newsletter_subscribers.</p>
</div>
  <div class="form-actions">
    <button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save Homepage Content</button>
  </div>
</form>

<section class="module-panel module-form settings-panel" style="margin-top:18px;">
  <h2 class="settings-title"><i class="bi bi-grid-3x3-gap"></i> Homepage Profile Sections</h2>
  <p class="form-hint">Select the profiles for Hero Profiles, Featured Entrepreneurs, Entrepreneur Spotlights, FEATURED STORY and Recently Published Profiles below. Each seeded section can be edited independently; you can also create additional custom sections. All selections use published profiles and keep the existing website design.</p>

  <form method="post" action="{{ route('admin.homepage-manager.profile-sections.store') }}" class="settings-subpanel" style="border:1px solid #e8edf5;border-radius:10px;padding:14px;margin:12px 0;">
    @csrf
    <h3 class="h6 fw-bold">Create an additional profile section</h3>
    <div class="form-grid">
      <label>Section title *<input class="form-control" name="title" maxlength="220" required placeholder="e.g. Featured Entrepreneurs"></label>
      <label>Display order<input class="form-control" type="number" name="sort_order" min="0" value="0"></label>
      <label class="wide">Section subtitle<input class="form-control" name="subtitle" maxlength="500" placeholder="A short description shown below the title"></label>
      <label class="checkrow wide"><input type="hidden" name="is_enabled" value="0"><input type="checkbox" name="is_enabled" value="1" checked> Show this section on the homepage</label>
      <label class="wide">Select published profiles (Ctrl / Cmd to select multiple)
        <select class="form-select" name="profile_ids[]" multiple size="8">
          @foreach($publishedProfiles as $publishedProfile)
            @php($publishedName = $publishedProfile->profile_type === 'personal' ? ($publishedProfile->publishedVersion?->personalDetails?->full_name ?? $publishedProfile->slug) : ($publishedProfile->publishedVersion?->companyDetails?->company_name ?? $publishedProfile->slug))
            <option value="{{ $publishedProfile->id }}">{{ $publishedName }} — {{ ucfirst($publishedProfile->profile_type) }}</option>
          @endforeach
        </select>
      </label>
    </div>
    <div class="form-actions"><button class="module-btn" type="submit"><i class="bi bi-plus-circle"></i> Create Homepage Section</button></div>
  </form>

  @forelse($homepageProfileSections as $profileSection)
    <form method="post" action="{{ route('admin.homepage-manager.profile-sections.update', $profileSection) }}" class="settings-subpanel" style="border:1px solid #e8edf5;border-radius:10px;padding:14px;margin:12px 0;">
      @csrf @method('PUT')
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2"><h3 class="h6 fw-bold mb-0">{{ $profileSection->title }}</h3><span class="soft-tag {{ $profileSection->is_enabled ? 'tag-active' : 'tag-inactive' }}">{{ $profileSection->is_enabled ? 'Enabled' : 'Hidden' }}</span></div>
      <div class="form-grid">
        <label>Section title<input class="form-control" name="title" maxlength="220" required value="{{ $profileSection->title }}"></label>
        <label>Display order<input class="form-control" type="number" name="sort_order" min="0" value="{{ $profileSection->sort_order }}"></label>
        <label class="wide">Section subtitle<input class="form-control" name="subtitle" maxlength="500" value="{{ $profileSection->subtitle }}"></label>
        <label class="checkrow wide"><input type="hidden" name="is_enabled" value="0"><input type="checkbox" name="is_enabled" value="1" @checked($profileSection->is_enabled)> Show this section on the homepage</label>
        <label class="wide">Profiles in this section <small class="text-muted">For FEATURED STORY, the first selected profile is displayed as the main story.</small>
          <select class="form-select" name="profile_ids[]" @if($profileSection->section_key !== 'featured_story') multiple size="8" @endif>
            @php($selectedProfileIds = $profileSection->items->pluck('profile_id')->map(fn($id) => (string)$id)->all())
            @foreach($publishedProfiles as $publishedProfile)
              @php($publishedName = $publishedProfile->profile_type === 'personal' ? ($publishedProfile->publishedVersion?->personalDetails?->full_name ?? $publishedProfile->slug) : ($publishedProfile->publishedVersion?->companyDetails?->company_name ?? $publishedProfile->slug))
              <option value="{{ $publishedProfile->id }}" @selected(in_array((string)$publishedProfile->id, $selectedProfileIds, true))>{{ $publishedName }} — {{ ucfirst($publishedProfile->profile_type) }}</option>
            @endforeach
          </select>
        </label>
      </div>
      <div class="form-actions"><button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save Section</button></div>
    </form>
    <form method="post" action="{{ route('admin.homepage-manager.profile-sections.destroy', $profileSection) }}" class="mb-3" data-confirm="Remove this homepage section? Its profile selections will also be removed." data-confirm-button="Remove section">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> Delete {{ $profileSection->title }}</button></form>
  @empty
    <div class="alert alert-light border mb-0">No custom profile sections have been created yet. Create one above to choose which profiles appear on the homepage.</div>
  @endforelse
</section>
@endsection
