@extends('admin.master.master')
@section('title','Global Notice')
@section('body')
<div class="module-heading">
  <div>
    <div class="eyebrow">WEBSITE COMMUNICATION</div>
    <h1><span class="heading-icon"><i class="bi bi-megaphone"></i></span>Global Notice</h1>
    <p>Manage the announcement bar displayed across the public website. Changes are saved to the existing homepage settings database table.</p>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success" role="alert">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="alert alert-danger" role="alert"><strong>Please check the following fields:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="module-panel" style="margin-bottom:18px;padding:16px 18px;">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge bg-primary">Public Website Notice</span>
    <span class="text-muted small">One global announcement bar, shown in the website header.</span>
    <a class="btn btn-sm btn-outline-primary ms-auto" href="{{ route('site.home') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Preview Website</a>
  </div>
</div>

<form class="module-panel module-form settings-panel" method="post" action="{{ route('admin.global-notice.update') }}">
  @csrf
  @method('PUT')
  <h2 class="settings-title"><i class="bi bi-megaphone"></i> Announcement Content</h2>
  <div class="form-grid">
    <label class="checkrow wide"><input type="hidden" name="announcement_enabled" value="0"><input type="checkbox" name="announcement_enabled" value="1" @checked(old('announcement_enabled', $noticeSettings['announcement_enabled'] ?? true))> Show global notice on the public website</label>
    <label>Notice label<input class="form-control" type="text" name="announcement_label" maxlength="40" value="{{ old('announcement_label', $noticeSettings['announcement_label'] ?? 'ANNOUNCEMENT') }}" placeholder="ANNOUNCEMENT"></label>
    <label>Badge text<input class="form-control" type="text" name="announcement_badge" maxlength="30" value="{{ old('announcement_badge', $noticeSettings['announcement_badge'] ?? 'New') }}" placeholder="New"></label>
    <label class="wide">Notice title<input class="form-control" type="text" name="announcement_title" maxlength="220" value="{{ old('announcement_title', $noticeSettings['announcement_title'] ?? '') }}"></label>
    <label class="wide">Notice description<textarea class="form-control" name="announcement_description" rows="3" maxlength="500">{{ old('announcement_description', $noticeSettings['announcement_description'] ?? '') }}</textarea></label>
    <label>Button text<input class="form-control" type="text" name="announcement_button_text" maxlength="80" value="{{ old('announcement_button_text', $noticeSettings['announcement_button_text'] ?? 'Submit Your Profile') }}"></label>
    <label>Button URL / internal path<input class="form-control" type="text" name="announcement_button_url" maxlength="500" value="{{ old('announcement_button_url', $noticeSettings['announcement_button_url'] ?? '/customer-register') }}" placeholder="/customer-register or https://example.com"></label>
    <p class="form-hint wide">Turn off the checkbox to hide the notice without deleting its content. Use a site path beginning with / or a full https:// URL.</p>
  </div>
  <div class="form-actions">
    <button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save Global Notice</button>
  </div>
</form>
@endsection
