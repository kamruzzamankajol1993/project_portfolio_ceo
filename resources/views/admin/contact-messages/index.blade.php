@extends('admin.master.master')
@section('title', 'Contact Inbox')
@section('body')
<div class="module-heading">
  <div><div class="eyebrow">WEBSITE COMMUNICATION</div><h1><span class="heading-icon"><i class="bi bi-inbox-fill"></i></span>Contact Inbox</h1><p>Messages submitted through the public Contact Us form.</p></div>
</div>
<div class="ops-stats contact-inbox-stats">
  <div class="ops-stat"><i class="bi bi-envelope-paper"></i><div><strong>{{ number_format($stats['total']) }}</strong><span>Total messages</span></div></div>
  <div class="ops-stat amber"><i class="bi bi-envelope-exclamation"></i><div><strong>{{ number_format($stats['unread']) }}</strong><span>Unread messages</span></div></div>
  <div class="ops-stat mint"><i class="bi bi-calendar-check"></i><div><strong>{{ number_format($stats['today']) }}</strong><span>Received today</span></div></div>
</div>
<section class="module-panel module-list-panel contact-inbox-panel">
  <form class="module-toolbar" method="get" action="{{ route('admin.contact-messages.index') }}" data-ajax-filter>
    <div class="module-toolbar-hint"><span class="toolbar-indicator"></span> Incoming messages</div>
    <div class="contact-inbox-filters">
      <select class="form-select" name="status" aria-label="Filter by status">
        <option value="">All messages</option>
        <option value="unread" @selected(request('status') === 'unread')>Unread</option>
        <option value="read" @selected(request('status') === 'read')>Read</option>
      </select>
      <label class="module-search"><i class="bi bi-search"></i><input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email or subject..."></label>
      <button class="module-secondary" type="submit"><i class="bi bi-filter"></i> Filter</button>
    </div>
  </form>
  <div data-ajax-list>@include('admin.contact-messages._table')</div>
</section>
@endsection
