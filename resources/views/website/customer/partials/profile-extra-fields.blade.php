<section class="editor-card">
  <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-share"></i></span><div><h2>Official links</h2><p>Add websites and verified social or professional pages.</p></div></div><span class="section-step">{{ $profileType === 'company' ? '04' : '04' }}</span></div>
  <div class="editor-repeat-list" id="profile-links-container">
    @foreach($formLinks as $index => $link)
      <div class="editor-form-grid editor-repeat-row" data-repeat-row>
        <div class="form-field"><label>Link type</label><select class="form-select" name="links[{{ $index }}][link_type]"><option value="website" @selected(($link['link_type'] ?? 'website') === 'website')>Website</option><option value="linkedin" @selected(($link['link_type'] ?? '') === 'linkedin')>LinkedIn</option><option value="facebook" @selected(($link['link_type'] ?? '') === 'facebook')>Facebook</option><option value="instagram" @selected(($link['link_type'] ?? '') === 'instagram')>Instagram</option><option value="youtube" @selected(($link['link_type'] ?? '') === 'youtube')>YouTube</option><option value="x" @selected(($link['link_type'] ?? '') === 'x')>X / Twitter</option><option value="other" @selected(($link['link_type'] ?? '') === 'other')>Other</option></select></div>
        <div class="form-field"><label>Link label</label><input class="form-control" name="links[{{ $index }}][label]" value="{{ $link['label'] ?? '' }}" placeholder="Official website / LinkedIn"></div>
        <div class="form-field"><label>URL</label><input class="form-control" type="url" name="links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}" placeholder="https://"></div>
        <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove</button></div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="profile-links-container" data-template="profile-link-template"><i class="bi bi-plus-lg"></i> Add link</button>
  <template id="profile-link-template">
    <div class="editor-form-grid editor-repeat-row" data-repeat-row>
      <div class="form-field"><label>Link type</label><select class="form-select" name="links[__INDEX__][link_type]"><option value="website">Website</option><option value="linkedin">LinkedIn</option><option value="facebook">Facebook</option><option value="instagram">Instagram</option><option value="youtube">YouTube</option><option value="x">X / Twitter</option><option value="other">Other</option></select></div>
      <div class="form-field"><label>Link label</label><input class="form-control" name="links[__INDEX__][label]" placeholder="Official website / LinkedIn"></div>
      <div class="form-field"><label>URL</label><input class="form-control" type="url" name="links[__INDEX__][url]" placeholder="https://"></div>
      <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove</button></div>
    </div>
  </template>
</section>

<section class="editor-card">
  <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-journal-check"></i></span><div><h2>References & sources</h2><p>Add sources that support the information in this profile.</p></div></div><span class="section-step">05</span></div>
  <div id="profile-references-container">
    @foreach($formReferences as $index => $reference)
      <div class="editor-form-grid editor-repeat-row" data-repeat-row>
        <div class="form-field"><label>Reference title</label><input class="form-control" name="references[{{ $index }}][title]" value="{{ $reference['title'] ?? '' }}" placeholder="Source or article title"></div>
        <div class="form-field"><label>Publisher</label><input class="form-control" name="references[{{ $index }}][publisher]" value="{{ $reference['publisher'] ?? '' }}" placeholder="Publisher / organisation"></div>
        <div class="form-field"><label>Source URL</label><input class="form-control" type="url" name="references[{{ $index }}][url]" value="{{ $reference['url'] ?? '' }}" placeholder="https://"></div>
        <div class="form-field"><label>Source date</label><input class="form-control" type="date" name="references[{{ $index }}][source_date]" value="{{ $reference['source_date'] ?? '' }}"></div>
        <div class="form-field field-span-2"><label>Source note</label><textarea class="form-control" name="references[{{ $index }}][description]" rows="2">{{ $reference['description'] ?? '' }}</textarea></div>
        <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove</button></div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="profile-references-container" data-template="profile-reference-template"><i class="bi bi-plus-lg"></i> Add reference</button>
  <template id="profile-reference-template">
    <div class="editor-form-grid editor-repeat-row" data-repeat-row>
      <div class="form-field"><label>Reference title</label><input class="form-control" name="references[__INDEX__][title]" placeholder="Source or article title"></div>
      <div class="form-field"><label>Publisher</label><input class="form-control" name="references[__INDEX__][publisher]" placeholder="Publisher / organisation"></div>
      <div class="form-field"><label>Source URL</label><input class="form-control" type="url" name="references[__INDEX__][url]" placeholder="https://"></div>
      <div class="form-field"><label>Source date</label><input class="form-control" type="date" name="references[__INDEX__][source_date]"></div>
      <div class="form-field field-span-2"><label>Source note</label><textarea class="form-control" name="references[__INDEX__][description]" rows="2"></textarea></div>
      <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove</button></div>
    </div>
  </template>
</section>

<section class="editor-card">
  <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-layout-text-window-reverse"></i></span><div><h2>Additional profile sections</h2><p>Create sections, choose their layout, add text and images, and control visibility.</p></div></div><span class="section-step">06</span></div>
  <div id="profile-sections-container">
    @foreach($formSections as $sectionIndex => $section)
      <div class="profile-dynamic-section editor-repeat-row" data-section-row>
        <div class="editor-form-grid">
          <div class="form-field"><label>Section title</label><input class="form-control" name="sections[{{ $sectionIndex }}][title]" value="{{ $section['title'] ?? '' }}" placeholder="e.g. Services & Operations" @disabled($lockedForReview)></div>
          <div class="form-field"><label>Section type</label><select class="form-select" name="sections[{{ $sectionIndex }}][section_type]" @disabled($lockedForReview)><option value="content" @selected(($section['section_type'] ?? 'content') === 'content')>Content</option><option value="cards" @selected(($section['section_type'] ?? '') === 'cards')>Cards</option><option value="list" @selected(($section['section_type'] ?? '') === 'list')>List</option></select></div>
          <div class="form-field"><label>Layout</label><select class="form-select" name="sections[{{ $sectionIndex }}][layout]" @disabled($lockedForReview)><option value="default" @selected(($section['layout'] ?? 'default') === 'default')>Default</option><option value="grid" @selected(($section['layout'] ?? '') === 'grid')>Grid</option><option value="timeline" @selected(($section['layout'] ?? '') === 'timeline')>Timeline</option></select></div>
          <div class="form-field"><label>Section visibility</label><div class="form-check"><input type="hidden" name="sections[{{ $sectionIndex }}][is_visible]" value="0"><input class="form-check-input" type="checkbox" name="sections[{{ $sectionIndex }}][is_visible]" value="1" @checked($section['is_visible'] ?? true) @disabled($lockedForReview)><label class="form-check-label">Show this section publicly</label></div></div>
          <div class="form-field field-span-2"><label>Section introduction</label><textarea class="form-control" name="sections[{{ $sectionIndex }}][intro]" rows="2" @disabled($lockedForReview)>{{ $section['intro'] ?? '' }}</textarea></div>
        </div>
        <div class="profile-section-items" data-items-container>
          @foreach(($section['items'] ?? [['title'=>'','subtitle'=>'','description'=>'','url'=>'','icon'=>'','image_path'=>'','is_visible'=>true]]) as $itemIndex => $item)
            <div class="editor-form-grid editor-repeat-row" data-repeat-row>
              <div class="form-field"><label>Item title</label><input class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][title]" value="{{ $item['title'] ?? '' }}" @disabled($lockedForReview)></div>
              <div class="form-field"><label>Subtitle</label><input class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][subtitle]" value="{{ $item['subtitle'] ?? '' }}" @disabled($lockedForReview)></div>
              <div class="form-field"><label>Icon class</label><input class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][icon]" value="{{ $item['icon'] ?? '' }}" placeholder="bi bi-award" @disabled($lockedForReview)></div>
              <div class="form-field"><label>External URL</label><input class="form-control" type="url" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][url]" value="{{ $item['url'] ?? '' }}" placeholder="https://" @disabled($lockedForReview)></div>
              <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][description]" rows="2" @disabled($lockedForReview)>{{ $item['description'] ?? '' }}</textarea></div>
              <div class="form-field field-span-2">
                @if(!empty($item['image_path']))
                  <img src="{{ \Illuminate\Support\Str::startsWith($item['image_path'], ['http://','https://']) ? $item['image_path'] : asset('public/'.ltrim($item['image_path'],'/')) }}" alt="Section item image" class="profile-current-media">
                @endif
                <input type="hidden" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][image_path]" value="{{ $item['image_path'] ?? '' }}">
                <label>Item image<input class="form-control" type="file" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][image]" accept="image/png,image/jpeg,image/webp" @disabled($lockedForReview)></label>
              </div>
              <div class="form-field"><label>Item visibility</label><div class="form-check"><input type="hidden" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][is_visible]" value="0"><input class="form-check-input" type="checkbox" name="sections[{{ $sectionIndex }}][items][{{ $itemIndex }}][is_visible]" value="1" @checked($item['is_visible'] ?? true) @disabled($lockedForReview)><label class="form-check-label">Show this item</label></div></div>
              <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row @disabled($lockedForReview)><i class="bi bi-trash"></i> Remove item</button></div>
            </div>
          @endforeach
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2"><button type="button" class="btn btn-outline-primary btn-sm" data-add-item @disabled($lockedForReview)><i class="bi bi-plus-lg"></i> Add item</button><button type="button" class="btn btn-light btn-sm" data-remove-section @disabled($lockedForReview)><i class="bi bi-trash"></i> Remove section</button></div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-section @disabled($lockedForReview)><i class="bi bi-plus-lg"></i> Add profile section</button>
  <template id="profile-section-template">
    <div class="profile-dynamic-section editor-repeat-row" data-section-row>
      <div class="editor-form-grid">
        <div class="form-field"><label>Section title</label><input class="form-control" name="sections[__SECTION__][title]" placeholder="e.g. Awards & Recognition"></div>
        <div class="form-field"><label>Section type</label><select class="form-select" name="sections[__SECTION__][section_type]"><option value="content">Content</option><option value="cards">Cards</option><option value="list">List</option></select></div>
        <div class="form-field"><label>Layout</label><select class="form-select" name="sections[__SECTION__][layout]"><option value="default">Default</option><option value="grid">Grid</option><option value="timeline">Timeline</option></select></div>
        <div class="form-field"><label>Section visibility</label><div class="form-check"><input type="hidden" name="sections[__SECTION__][is_visible]" value="0"><input class="form-check-input" type="checkbox" name="sections[__SECTION__][is_visible]" value="1" checked><label class="form-check-label">Show this section publicly</label></div></div>
        <div class="form-field field-span-2"><label>Section introduction</label><textarea class="form-control" name="sections[__SECTION__][intro]" rows="2"></textarea></div>
      </div>
      <div class="profile-section-items" data-items-container>
        <div class="editor-form-grid editor-repeat-row" data-repeat-row>
          <div class="form-field"><label>Item title</label><input class="form-control" name="sections[__SECTION__][items][0][title]"></div>
          <div class="form-field"><label>Subtitle</label><input class="form-control" name="sections[__SECTION__][items][0][subtitle]"></div>
          <div class="form-field"><label>Icon class</label><input class="form-control" name="sections[__SECTION__][items][0][icon]" placeholder="bi bi-award"></div>
          <div class="form-field"><label>External URL</label><input class="form-control" type="url" name="sections[__SECTION__][items][0][url]" placeholder="https://"></div>
          <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="sections[__SECTION__][items][0][description]" rows="2"></textarea></div>
          <div class="form-field field-span-2"><input type="hidden" name="sections[__SECTION__][items][0][image_path]" value=""><label>Item image<input class="form-control" type="file" name="sections[__SECTION__][items][0][image]" accept="image/png,image/jpeg,image/webp"></label></div>
          <div class="form-field"><label>Item visibility</label><div class="form-check"><input type="hidden" name="sections[__SECTION__][items][0][is_visible]" value="0"><input class="form-check-input" type="checkbox" name="sections[__SECTION__][items][0][is_visible]" value="1" checked><label class="form-check-label">Show this item</label></div></div>
          <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove item</button></div>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-2"><button type="button" class="btn btn-outline-primary btn-sm" data-add-item><i class="bi bi-plus-lg"></i> Add item</button><button type="button" class="btn btn-light btn-sm" data-remove-section><i class="bi bi-trash"></i> Remove section</button></div>
    </div>
  </template>
  <template id="profile-section-item-template">
    <div class="editor-form-grid editor-repeat-row" data-repeat-row>
      <div class="form-field"><label>Item title</label><input class="form-control" name="sections[__SECTION__][items][__ITEM__][title]"></div>
      <div class="form-field"><label>Subtitle</label><input class="form-control" name="sections[__SECTION__][items][__ITEM__][subtitle]"></div>
      <div class="form-field"><label>Icon class</label><input class="form-control" name="sections[__SECTION__][items][__ITEM__][icon]" placeholder="bi bi-award"></div>
      <div class="form-field"><label>External URL</label><input class="form-control" type="url" name="sections[__SECTION__][items][__ITEM__][url]" placeholder="https://"></div>
      <div class="form-field field-span-2"><label>Description</label><textarea class="form-control" name="sections[__SECTION__][items][__ITEM__][description]" rows="2"></textarea></div>
      <div class="form-field field-span-2"><input type="hidden" name="sections[__SECTION__][items][__ITEM__][image_path]" value=""><label>Item image<input class="form-control" type="file" name="sections[__SECTION__][items][__ITEM__][image]" accept="image/png,image/jpeg,image/webp"></label></div>
      <div class="form-field"><label>Item visibility</label><div class="form-check"><input type="hidden" name="sections[__SECTION__][items][__ITEM__][is_visible]" value="0"><input class="form-check-input" type="checkbox" name="sections[__SECTION__][items][__ITEM__][is_visible]" value="1" checked><label class="form-check-label">Show this item</label></div></div>
      <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove item</button></div>
    </div>
  </template>
</section>


<section class="editor-card">
  <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-diagram-3"></i></span><div><h2>Related profiles</h2><p>Connect this person or company to other published profiles.</p></div></div><span class="section-step">07</span></div>
  <div id="profile-relationships-container">
    @foreach($formRelationships as $index => $relationship)
      <div class="editor-form-grid editor-repeat-row" data-repeat-row>
        <div class="form-field"><label>Related profile</label><select class="form-select" name="relationships[{{ $index }}][related_profile_id]"><option value="">Select a published profile</option>@foreach($relatedProfiles as $relatedProfile)@php($relatedDetails = $relatedProfile->profile_type === 'personal' ? $relatedProfile->publishedVersion?->personalDetails : $relatedProfile->publishedVersion?->companyDetails)@php($relatedName = $relatedProfile->profile_type === 'personal' ? ($relatedDetails?->full_name ?? $relatedProfile->slug) : ($relatedDetails?->company_name ?? $relatedProfile->slug))<option value="{{ $relatedProfile->id }}" @selected((string)($relationship['related_profile_id'] ?? '') === (string)$relatedProfile->id)>{{ $relatedName }} — {{ ucfirst($relatedProfile->profile_type) }}</option>@endforeach</select></div>
        <div class="form-field"><label>Relationship type</label><select class="form-select" name="relationships[{{ $index }}][relation_type]"><option value="founder" @selected(($relationship['relation_type'] ?? '')==='founder')>Founder</option><option value="co_founder" @selected(($relationship['relation_type'] ?? '')==='co_founder')>Co-founder</option><option value="ceo" @selected(($relationship['relation_type'] ?? '')==='ceo')>CEO</option><option value="executive" @selected(($relationship['relation_type'] ?? '')==='executive')>Executive</option><option value="company" @selected(($relationship['relation_type'] ?? '')==='company')>Company / organisation</option><option value="investor" @selected(($relationship['relation_type'] ?? '')==='investor')>Investor</option><option value="related" @selected(($relationship['relation_type'] ?? 'related')==='related')>Related</option></select></div>
        <div class="form-field"><label>Position / role</label><input class="form-control" name="relationships[{{ $index }}][position]" value="{{ $relationship['position'] ?? '' }}" placeholder="Founder & CEO"></div>
        <div class="form-field"><label>Relationship note</label><input class="form-control" name="relationships[{{ $index }}][description]" value="{{ $relationship['description'] ?? '' }}"></div>
        <div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove</button></div>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-add-row="profile-relationships-container" data-template="profile-relationship-template"><i class="bi bi-plus-lg"></i> Add related profile</button>
  <template id="profile-relationship-template"><div class="editor-form-grid editor-repeat-row" data-repeat-row><div class="form-field"><label>Related profile</label><select class="form-select" name="relationships[__INDEX__][related_profile_id]"><option value="">Select a published profile</option>@foreach($relatedProfiles as $relatedProfile)@php($relatedDetails = $relatedProfile->profile_type === 'personal' ? $relatedProfile->publishedVersion?->personalDetails : $relatedProfile->publishedVersion?->companyDetails)@php($relatedName = $relatedProfile->profile_type === 'personal' ? ($relatedDetails?->full_name ?? $relatedProfile->slug) : ($relatedDetails?->company_name ?? $relatedProfile->slug))<option value="{{ $relatedProfile->id }}">{{ $relatedName }} — {{ ucfirst($relatedProfile->profile_type) }}</option>@endforeach</select></div><div class="form-field"><label>Relationship type</label><select class="form-select" name="relationships[__INDEX__][relation_type]"><option value="founder">Founder</option><option value="co_founder">Co-founder</option><option value="ceo">CEO</option><option value="executive">Executive</option><option value="company">Company / organisation</option><option value="investor">Investor</option><option value="related">Related</option></select></div><div class="form-field"><label>Position / role</label><input class="form-control" name="relationships[__INDEX__][position]" placeholder="Founder & CEO"></div><div class="form-field"><label>Relationship note</label><input class="form-control" name="relationships[__INDEX__][description]"></div><div class="form-field editor-repeat-action"><label>&nbsp;</label><button type="button" class="btn btn-light" data-remove-row><i class="bi bi-trash"></i> Remove</button></div></div></template>
</section>

<section class="editor-card">
  <div class="editor-card-heading"><div><span class="editor-section-icon"><i class="bi bi-search"></i></span><div><h2>Search engine settings</h2><p>Optional metadata for this profile page.</p></div></div><span class="section-step">08</span></div>
  <div class="editor-form-grid">
    <div class="form-field"><label>Meta title</label><input class="form-control" name="meta_title" maxlength="255" value="{{ old('meta_title',$version?->seo?->meta_title ?? '') }}"></div>
    <div class="form-field"><label>Meta description</label><textarea class="form-control" name="meta_description" rows="2" maxlength="500">{{ old('meta_description',$version?->seo?->meta_description ?? '') }}</textarea></div>
    <div class="form-field field-span-2"><label>Meta keywords</label><textarea class="form-control" name="meta_keywords" rows="2">{{ old('meta_keywords',$version?->seo?->meta_keywords ?? '') }}</textarea></div>
    <div class="form-field"><label>Search indexing</label><select class="form-select" name="robots"><option value="index,follow" @selected(old('robots',$version?->seo?->robots ?? 'index,follow')==='index,follow')>Index, follow</option><option value="noindex,follow" @selected(old('robots',$version?->seo?->robots ?? '')==='noindex,follow')>No index, follow</option><option value="index,nofollow" @selected(old('robots',$version?->seo?->robots ?? '')==='index,nofollow')>Index, no follow</option><option value="noindex,nofollow" @selected(old('robots',$version?->seo?->robots ?? '')==='noindex,nofollow')>No index, no follow</option></select></div>
    <div class="form-field"><label>Canonical URL</label><input class="form-control" type="url" name="canonical_url" value="{{ old('canonical_url',$version?->seo?->canonical_url ?? '') }}" placeholder="https://"></div>
    <div class="form-field"><label>Open Graph title</label><input class="form-control" name="og_title" value="{{ old('og_title',$version?->seo?->og_title ?? '') }}"></div>
    <div class="form-field"><label>Open Graph description</label><textarea class="form-control" name="og_description" rows="2">{{ old('og_description',$version?->seo?->og_description ?? '') }}</textarea></div>
    <div class="form-field"><label>Open Graph image URL</label><input class="form-control" type="url" name="og_image" value="{{ old('og_image',$version?->seo?->og_image ?? '') }}"></div>
    <div class="form-field"><label>Twitter title</label><input class="form-control" name="twitter_title" value="{{ old('twitter_title',$version?->seo?->twitter_title ?? '') }}"></div>
    <div class="form-field"><label>Twitter description</label><textarea class="form-control" name="twitter_description" rows="2">{{ old('twitter_description',$version?->seo?->twitter_description ?? '') }}</textarea></div>
    <div class="form-field"><label>Twitter image URL</label><input class="form-control" type="url" name="twitter_image" value="{{ old('twitter_image',$version?->seo?->twitter_image ?? '') }}"></div>
  </div>
</section>
