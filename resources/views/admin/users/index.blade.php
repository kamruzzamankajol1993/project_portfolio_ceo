@extends('admin.master.master')
@section('title','Users')
@section('body')
<div class="module-heading"><div><div class="eyebrow">ACCESS MANAGEMENT</div><h1><span class="heading-icon"><i class="bi bi-people-fill"></i></span>Users</h1><p>Manage visible accounts and assigned roles.</p></div><a class="module-btn" href="{{ route('admin.users.create') }}"><i class="bi bi-plus-lg"></i> Add user</a></div>
<section class="module-panel module-list-panel">
<div class="module-toolbar"><div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Live users list</div><form class="module-search" data-ajax-filter action="{{ route('admin.users.index') }}" method="get" role="search"><i class="bi bi-search" aria-hidden="true"></i><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email or phone" aria-label="Search users" autocomplete="off"><span class="module-search-shortcut">Live</span></form></div><div data-ajax-list>@include('admin.users._table')</div></section>
@endsection
