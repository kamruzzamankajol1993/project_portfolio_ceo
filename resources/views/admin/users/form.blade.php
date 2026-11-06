@extends('admin.master.master')
@section('title', $user->exists?'Edit User':'Add User')
@section('body')
<div class="module-heading"><div><div class="eyebrow">USER MANAGEMENT</div><h1>{{ $user->exists?'Edit User':'Add User' }}</h1></div><a href="{{ route('admin.users.index') }}" class="module-secondary"><i class="bi bi-arrow-left"></i> Back to list</a></div>
<form class="module-panel module-form module-editor" method="post" enctype="multipart/form-data" action="{{ $user->exists?route('admin.users.update',$user):route('admin.users.store') }}">@csrf @if($user->exists)@method('PUT')@endif
<div class="form-grid">
 <label>Full name *<input class="form-control" name="name" value="{{ old('name',$user->name) }}" required></label>
 <label>Email *<input class="form-control" type="email" name="email" value="{{ old('email',$user->email) }}" required></label>
 <label>Phone<input class="form-control" type="tel" name="phone" value="{{ old('phone',$user->phone) }}"></label>
 <label>Status *<select class="form-select js-choice" name="status"><option value="active" @selected(old('status',$user->status??'active')==='active')>Active</option><option value="inactive" @selected(old('status',$user->status)==='inactive')>Inactive</option></select></label>
 <label>Password {{ $user->exists?'(leave blank to keep)':'*' }}<input class="form-control" type="password" name="password" autocomplete="new-password" {{ $user->exists?'':'required' }}></label>
 <label>Confirm password<input class="form-control" type="password" name="password_confirmation" autocomplete="new-password" {{ $user->exists?'':'required' }}></label>
 <label>Profile photo<input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp">@if($user->image_url)<img class="preview-image" src="{{ $user->image_url }}" alt="Profile image">@endif</label>
 <label>Roles<select class="form-select js-choice" name="roles[]" multiple>@foreach($roles as $role)<option value="{{ $role->id }}" @selected(in_array($role->id,old('roles',$user->roles->pluck('id')->all())))>{{ $role->name }}</option>@endforeach</select></label>
 <label class="wide">Address<textarea class="form-control" name="address" rows="2">{{ old('address',$user->address) }}</textarea></label>
 <label class="wide">Bio<textarea class="form-control js-summernote" name="bio" rows="3">{{ old('bio',$user->bio) }}</textarea></label>
</div><div class="form-actions"><button class="module-btn" type="submit"><i class="bi bi-check2-circle"></i> Save user</button></div></form>
@endsection
