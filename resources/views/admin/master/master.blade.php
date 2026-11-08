<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') | {{ $siteSetting->site_name }}</title>
  @if($siteSetting->publicUrl('favicon'))<link rel="icon" href="{{ $siteSetting->publicUrl('favicon') }}">@endif
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400..900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/choices.js@11.2.4/public/assets/styles/choices.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-lite.min.css" rel="stylesheet">
  <link href="{{ asset('public/admin/css/admin.css') }}" rel="stylesheet">
  <link href="{{ asset('public/admin/css/modules.css') }}" rel="stylesheet">
  @yield('css')
</head>
<body class="admin-body"><div class="admin-app">
  @include('admin.include.sidebar')
  <main class="main-area">@include('admin.include.header')
    <div class="dashboard-content">
      @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
      @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Please fix the following:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
      @yield('body')
      @include('admin.include.footer')
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js@11.2.4/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-lite.min.js"></script>
<script src="{{ asset('public/admin/js/admin.js') }}"></script>
<script src="{{ asset('public/admin/js/modules.js') }}"></script>
@yield('scripts')
</body></html>
