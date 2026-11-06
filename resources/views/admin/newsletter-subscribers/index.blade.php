@extends('admin.master.master')
@section('title', 'Newsletter Subscribers')
@section('body')
<div class="module-heading">
  <div>
    <div class="eyebrow">AUDIENCE</div>
    <h1><span class="heading-icon"><i class="bi bi-envelope-paper"></i></span>Newsletter Subscribers</h1>
    <p>View and manage email addresses submitted through the website footer newsletter form.</p>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="module-panel p-3"><small class="text-muted">Total subscribers</small><div class="fs-3 fw-bold">{{ number_format($stats['total']) }}</div></div></div>
  <div class="col-md-4"><div class="module-panel p-3"><small class="text-muted">Active subscriptions</small><div class="fs-3 fw-bold text-success">{{ number_format($stats['subscribed']) }}</div></div></div>
  <div class="col-md-4"><div class="module-panel p-3"><small class="text-muted">Unsubscribed</small><div class="fs-3 fw-bold">{{ number_format($stats['unsubscribed']) }}</div></div></div>
</div>

<form method="get" class="module-panel p-3 mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-6"><label class="form-label" for="newsletter-search">Search email</label><input id="newsletter-search" class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="name@example.com"></div>
    <div class="col-md-3"><label class="form-label" for="newsletter-status">Status</label><select id="newsletter-status" class="form-select" name="status"><option value="">All statuses</option><option value="subscribed" @selected(request('status')==='subscribed')>Subscribed</option><option value="unsubscribed" @selected(request('status')==='unsubscribed')>Unsubscribed</option></select></div>
    <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Filter</button><a class="btn btn-light" href="{{ route('admin.newsletter-subscribers.index') }}">Reset</a></div>
  </div>
</form>

<div class="module-panel p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Email</th><th>Status</th><th>Subscribed</th><th>Last updated</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      @forelse($subscribers as $subscriber)
        <tr>
          <td class="fw-semibold">{{ $subscriber->email }}</td>
          <td><span class="badge {{ $subscriber->status==='subscribed' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($subscriber->status) }}</span></td>
          <td>{{ $subscriber->subscribed_at?->format('d M Y, h:i A') ?? '—' }}</td>
          <td>{{ $subscriber->updated_at?->format('d M Y, h:i A') ?? '—' }}</td>
          <td class="text-end">
            <form class="d-inline" method="post" action="{{ route('admin.newsletter-subscribers.toggle-status', $subscriber) }}">@csrf
              <button class="btn btn-sm btn-outline-primary" type="submit">{{ $subscriber->status==='subscribed' ? 'Unsubscribe' : 'Reactivate' }}</button>
            </form>
            <form class="d-inline" method="post" action="{{ route('admin.newsletter-subscribers.destroy', $subscriber) }}" data-confirm="Permanently remove this subscriber?">@csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Delete subscriber"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-5">No newsletter subscribers found.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if($subscribers->hasPages())
    <div class="p-3">{{ $subscribers->links() }}</div>
  @endif
</div>
@endsection
