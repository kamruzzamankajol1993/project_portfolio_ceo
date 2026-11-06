@extends('admin.master.master')
@section('title',$permission?'Edit Permission':'Create Permissions')
@section('body')
<div class="module-heading"><div><div class="eyebrow">PERMISSION EDITOR</div><h1>{{ $permission?'Edit permission':'Create group permissions' }}</h1></div><a href="{{ route('admin.permissions.index') }}" class="module-secondary"><i class="bi bi-arrow-left"></i> Back to permissions</a></div>
<form class="module-panel module-form module-editor" method="post" action="{{ $permission?route('admin.permissions.update',$permission):route('admin.permissions.store') }}">@csrf @if($permission)@method('PUT')@endif
<label>Group name *<input type="text" class="form-control" name="group" value="{{ old('group',$permission?->group) }}" placeholder="e.g. Articles" required></label>
@if($permission)
<label>Permission name *<input type="text" class="form-control" name="name" value="{{ old('name',$permission->name) }}" required></label>
@else
<div class="mt-3"><div class="section-mini-title">Permissions in this group</div><div id="permission-inputs">
@foreach(old('names',['']) as $i=>$name)<div class="permission-entry"><input class="form-control" name="names[]" value="{{ $name }}" placeholder="e.g. articles.create" required><button class="module-secondary" type="button" data-remove-permission aria-label="Remove permission"><i class="bi bi-x-lg"></i></button></div>@endforeach
</div><button class="module-secondary" type="button" id="add-permission"><i class="bi bi-plus-lg"></i> Add another permission</button></div>
@endif
<div class="form-actions"><button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save permissions</button></div></form>
@endsection
