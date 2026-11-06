@extends('website.master.master')

@section('title')
    {{ $filterType === 'personal' ? 'Entrepreneur Profiles' : ($filterType === 'company' ? 'Company Profiles' : 'All Profiles') }}
    | {{ $siteSetting->site_name }}
@endsection

@section('css')
<link href="{{ asset('/') }}public/website/profile-pages.css" rel="stylesheet">
<style>
.profile-directory{padding-top:32px;padding-bottom:48px}.profile-directory .section-heading{margin-bottom:18px}.profile-directory .directory-search{display:flex;gap:8px;max-width:520px;margin:0 0 18px;flex-wrap:wrap}.profile-directory .directory-search input{min-width:180px;flex:1;border:1px solid #e0e8f5;border-radius:7px;padding:10px 12px;font-size:12px}.profile-directory .directory-search select{border:1px solid #e0e8f5;border-radius:7px;padding:10px 12px;font-size:12px;background:#fff}.profile-directory .directory-status{text-align:center;color:#71829b;font-size:12px;padding:18px}.profile-directory .directory-load-more{display:block;margin:18px auto 0}.profile-directory .profile-card h3 a{color:inherit}.profile-directory .profile-card h3 a:hover{color:#0865f5}
</style>
@endsection

@section('body')
<main class="profile-page profile-directory page-shell container-fluid" data-profile-directory data-next-page="{{ $profiles->nextPageUrl() }}">
  <div class="section-heading"><div><span class="auth-eyebrow">DISCOVER PEOPLE &amp; ORGANISATIONS</span><h1 class="mb-1">{{ $filterType === 'personal' ? 'Entrepreneur Profiles' : ($filterType === 'company' ? 'Company Profiles' : 'All Profiles') }}</h1>
    <p>{{ $filterType === 'personal' ? 'Browse published entrepreneurs, founders and business leaders.' : ($filterType === 'company' ? 'Browse published companies and organisations.' : 'Browse published personal and company profiles in one place.') }}</p></div><div class="section-controls"><span>{{ number_format($profiles->total()) }} published profiles</span></div></div>
  <form class="directory-search" method="get" action="{{ route('site.profiles.index') }}"><select name="type" aria-label="Filter profile type"><option value="">All profiles</option><option value="personal" @selected($filterType==='personal')>Personal profiles</option><option value="company" @selected($filterType==='company')>Company profiles</option></select><input type="search" name="q" value="{{ request('q') }}" placeholder="Search people or companies..." aria-label="Search profiles"><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button></form>
  <div class="featured-grid" data-profile-list>@include('website.profile._directory-cards', ['profiles' => $profiles])</div>
  @if($profiles->isEmpty())<div class="directory-status">No published profiles found.</div>@else<div class="directory-status" data-profile-status>{{ $profiles->hasMorePages() ? 'Scroll to load more profiles.' : 'You are viewing all published profiles.' }}</div>@endif
  <div data-profile-sentinel aria-hidden="true"></div><button class="btn btn-outline-primary directory-load-more" type="button" data-profile-load-more @if(!$profiles->hasMorePages()) hidden @endif>Load more profiles</button>
</main>
@endsection

@section('scripts')<script src="{{ asset('/') }}public/website/profile-list.js"></script>@endsection
