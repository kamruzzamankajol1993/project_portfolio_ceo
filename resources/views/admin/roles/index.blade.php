@extends('admin.master.master')
@section('title','Roles')
@section('body')
<div class="module-heading"><div><div class="eyebrow">ACCESS MANAGEMENT</div><h1><span class="heading-icon"><i class="bi bi-shield-lock-fill"></i></span>Roles</h1><p>Assign individual, group-wise or all permissions.</p></div><a class="module-btn" href="{{ route('admin.roles.create') }}"><i class="bi bi-plus-lg"></i> Add role</a></div>
<section class="module-panel module-list-panel"><div class="module-toolbar"><div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Live roles list</div><form class="module-search" data-ajax-filter action="{{ route('admin.roles.index') }}" method="get" role="search"><i class="bi bi-search" aria-hidden="true"></i><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search roles" aria-label="Search roles" autocomplete="off"><span class="module-search-shortcut">Live</span></form></div><div data-ajax-list>@include('admin.roles._table')</div></section>
@endsection
