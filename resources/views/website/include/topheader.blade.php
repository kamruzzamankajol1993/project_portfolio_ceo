@if(($announcementSettings['announcement_enabled'] ?? true) === true || ($announcementSettings['announcement_enabled'] ?? true) === 1 || ($announcementSettings['announcement_enabled'] ?? true) === '1')
  @php
    $announcementUrl = $announcementSettings['announcement_button_url'] ?? '/customer-register';
    $announcementExternal = preg_match('~^https?://~i', $announcementUrl) === 1;
    $announcementHref = $announcementExternal ? $announcementUrl : url($announcementUrl ?: '/customer-register');
  @endphp
  <div class="announcement-wrap">
    <div class="container-fluid page-shell">
      <div class="announcement-bar">
        <div class="announcement-icon"><i class="bi bi-megaphone-fill" aria-hidden="true"></i></div>
        @if(!empty($announcementSettings['announcement_label']))<span class="announcement-label">{{ $announcementSettings['announcement_label'] }}</span>@endif
        @if(!empty($announcementSettings['announcement_badge']))<span class="new-pill">{{ $announcementSettings['announcement_badge'] }}</span>@endif
        <div class="announcement-copy">
          @if(!empty($announcementSettings['announcement_title']))<strong>{{ $announcementSettings['announcement_title'] }}</strong>@endif
          @if(!empty($announcementSettings['announcement_description']))<small>{{ $announcementSettings['announcement_description'] }}</small>@endif
        </div>
        @if(!empty($announcementSettings['announcement_button_text']))
          <a href="{{ $announcementHref }}" class="btn btn-primary btn-sm announcement-action" @if($announcementExternal) target="_blank" rel="noopener noreferrer" @endif>{{ $announcementSettings['announcement_button_text'] }} <i class="bi bi-arrow-right"></i></a>
        @endif
        <button class="announcement-close" type="button" aria-label="Dismiss announcement"><i class="bi bi-x-lg"></i></button>
      </div>
    </div>
  </div>
@endif
