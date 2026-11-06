@extends('admin.master.master')
@section('title',$role->exists?'Edit Role':'Add Role')
@section('body')
<div class="module-heading"><div><div class="eyebrow">ROLE BUILDER</div><h1>{{ $role->exists?'Edit Role':'Add Role' }}</h1></div><a class="module-secondary" href="{{ route('admin.roles.index') }}">Back to roles</a></div>
<form method="post" class="module-panel module-form module-editor" action="{{ $role->exists?route('admin.roles.update',$role):route('admin.roles.store') }}">@csrf @if($role->exists)@method('PUT')@endif
<label>Role name *<input class="form-control" name="name" value="{{ old('name',$role->name) }}" required></label>
<div class="permission-toolbar"><label class="checkrow"><input type="checkbox" id="check-all-permissions"> Select all permissions</label><small>Tick a group to select everything in that group or tick individual permissions.</small></div>
@php($selected=old('permissions',$role->exists?$role->permissions->pluck('id')->all():[]))
@forelse($groups as $groupName=>$permissions)
<div class="permission-group"><div class="permission-group-header"><label class="checkrow"><input type="checkbox" class="group-checkbox"> <strong>{{ $groupName }}</strong></label><span class="soft-tag">{{ $permissions->count() }} options</span></div>
 <div class="permission-grid">@foreach($permissions as $permission)<label class="checkrow"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="permission-checkbox" @checked(in_array($permission->id,$selected))> {{ $permission->name }}</label>@endforeach</div>
</div>
@empty<p>No permissions yet. <a href="{{ route('admin.permissions.create') }}">Create permissions first.</a></p>@endforelse
<div class="form-actions"><button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save role</button></div>
</form>
@endsection
