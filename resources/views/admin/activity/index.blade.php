@extends('admin.master.master')
@section('title','Activity Logs')
@section('body')
<div class="module-heading"><div><div class="eyebrow">AUDIT CENTER</div><h1><span class="heading-icon"><i class="bi bi-journal-text"></i></span>Activity Logs</h1><p>Track actual administrator actions, sign-ins and changes.</p></div></div>
<div class="ops-stats"><div class="ops-stat blue"><i class="bi bi-clock-history"></i><div><strong>{{ number_format($total) }}</strong><span>Recorded activities</span></div></div><div class="ops-stat mint"><i class="bi bi-lightning-charge"></i><div><strong>{{ number_format($today) }}</strong><span>Today</span></div></div></div>
<div class="module-panel module-list-panel">
 <form method="get" action="{{ route('admin.activity.index') }}" class="module-toolbar ops-toolbar" data-ajax-filter>
  <div class="ops-filter-row"><label class="visually-hidden" for="audit-module">Module</label><select id="audit-module" class="form-select form-select-sm" name="module"><option value="">All modules</option>@foreach($modules as $module)<option value="{{ $module }}" @selected(request('module')===$module)>{{ ucfirst($module) }}</option>@endforeach</select><label class="visually-hidden" for="audit-days">Date range</label><select id="audit-days" class="form-select form-select-sm" name="days"><option value="">All time</option>@foreach([1,7,30,90] as $days)<option value="{{ $days }}" @selected((string)request('days')===(string)$days)>Last {{ $days }} days</option>@endforeach</select></div>
  <div class="module-search"><i class="bi bi-search"></i><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Type to search logs..." aria-label="Live search logs"><span class="module-search-shortcut">LIVE</span></div>
 </form>
 <div data-ajax-list aria-live="polite">@include('admin.activity._table')</div>
</div>
@can('activity.cleanup')
<div class="module-panel ops-maintenance-panel"><div><h2><i class="bi bi-hourglass-split"></i> Audit retention</h2><p>Delete only entries older than the selected period. This cannot be undone.</p></div>
<form action="{{ route('admin.activity.prune') }}" method="post" class="ops-inline" data-confirm="Permanently delete old activity records?">@csrf<select class="form-select form-select-sm" name="days" aria-label="Retention days"><option value="90">Older than 90 days</option><option value="30">Older than 30 days</option><option value="60">Older than 60 days</option><option value="180">Older than 180 days</option></select><button type="submit" class="module-secondary ops-danger"><i class="bi bi-trash3"></i> Prune logs</button></form></div>
@endcan
@endsection
