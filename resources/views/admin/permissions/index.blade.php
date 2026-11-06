@extends('admin.master.master')
@section('title','Permissions')
@section('body')
<div class="module-heading"><div><div class="eyebrow">ACCESS MANAGEMENT</div><h1><span class="heading-icon"><i class="bi bi-key-fill"></i></span>Permissions</h1><p>Create groups and attach multiple permissions using the plus button.</p></div><a href="{{ route('admin.permissions.create') }}" class="module-btn"><i class="bi bi-plus-lg"></i> Add permissions</a></div>
<section class="module-panel module-list-panel"><div class="module-toolbar"><div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Live permissions list</div><form class="module-search" data-ajax-filter action="{{ route('admin.permissions.index') }}" method="get" role="search"><i class="bi bi-search" aria-hidden="true"></i><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search group or permission" aria-label="Search permissions" autocomplete="off"><span class="module-search-shortcut">Live</span></form></div><div data-ajax-list>@include('admin.permissions._table')</div></section>
@endsection
