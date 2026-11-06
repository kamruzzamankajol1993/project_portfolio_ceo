@extends('admin.master.master')
@section('title', 'View Contact Message')
@section('body')
<div class="module-heading">
  <div><div class="eyebrow">WEBSITE COMMUNICATION</div><h1><span class="heading-icon"><i class="bi bi-envelope-open"></i></span>Message Details</h1><p>Review the message and contact the sender directly.</p></div>
  <a class="module-secondary" href="{{ route('admin.contact-messages.index') }}"><i class="bi bi-arrow-left"></i> Back to Inbox</a>
</div>
<section class="module-panel contact-message-detail-panel">
  <div class="contact-detail-heading">
    <div><span class="soft-tag {{ $contactMessage->status === 'unread' ? 'tag-violet' : 'tag-active' }}">{{ ucfirst($contactMessage->status) }}</span><h2>{{ $contactMessage->subject }}</h2><p>Received {{ $contactMessage->created_at?->format('d F Y \a\t h:i A') }}</p></div>
    <div class="row-actions">
      <form method="post" action="{{ route('admin.contact-messages.toggle-read', $contactMessage) }}">@csrf<button class="module-secondary" type="submit"><i class="bi bi-envelope-check"></i> Mark as {{ $contactMessage->status === 'unread' ? 'read' : 'unread' }}</button></form>
      <form method="post" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}" data-confirm="Delete this contact message permanently?">@csrf @method('DELETE')<button class="module-btn contact-delete-btn" type="submit"><i class="bi bi-trash3"></i> Delete</button></form>
    </div>
  </div>
  <div class="contact-sender-details">
    <div><small>From</small><strong>{{ $contactMessage->name }}</strong></div>
    <div><small>Email</small><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></div>
    <div><small>Phone</small>@if($contactMessage->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactMessage->phone) }}">{{ $contactMessage->phone }}</a>@else<strong>Not provided</strong>@endif</div>
  </div>
  <div class="contact-message-body"><div class="contact-message-body-title"><i class="bi bi-chat-left-text"></i> Message</div><p>{{ $contactMessage->message }}</p></div>
  <div class="contact-admin-reply"><i class="bi bi-reply-fill"></i><div><strong>Reply to this sender</strong><span>Use your email application to respond directly.</span></div><a class="module-btn" href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: ' . $contactMessage->subject) }}"><i class="bi bi-send"></i> Reply by email</a></div>
</section>
@endsection
