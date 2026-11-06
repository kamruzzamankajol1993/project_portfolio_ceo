<div class="table-responsive">
<table class="table admin-table module-table contact-inbox-table">
  <thead><tr><th>Sender</th><th>Subject & Message</th><th>Received</th><th>Status</th><th class="actions-header">Action</th></tr></thead>
  <tbody>
  @forelse($messages as $message)
    <tr class="{{ $message->status === 'unread' ? 'contact-row-unread' : '' }}">
      <td><div class="contact-sender"><span class="contact-sender-icon"><i class="bi bi-person"></i></span><div><strong>{{ $message->name }}</strong><small>{{ $message->email }}</small>@if($message->phone)<small>{{ $message->phone }}</small>@endif</div></div></td>
      <td><a class="contact-subject-link" href="{{ route('admin.contact-messages.show', $message) }}">{{ $message->subject }}</a><small class="contact-message-preview">{{ \Illuminate\Support\Str::limit($message->message, 105) }}</small></td>
      <td><span class="contact-date">{{ $message->created_at?->format('d M Y') }}</span><small class="contact-time">{{ $message->created_at?->format('h:i A') }}</small></td>
      <td><span class="soft-tag {{ $message->status === 'unread' ? 'tag-violet' : 'tag-active' }}">{{ ucfirst($message->status) }}</span></td>
      <td class="actions-cell"><div class="row-actions">
        <a class="row-edit action-icon" href="{{ route('admin.contact-messages.show', $message) }}" title="Read message" aria-label="Read message"><i class="bi bi-eye"></i></a>
        <form method="post" action="{{ route('admin.contact-messages.destroy', $message) }}" data-confirm="Delete this contact message permanently?">@csrf @method('DELETE')<button type="submit" class="row-delete action-icon" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button></form>
      </div></td>
    </tr>
  @empty
    <tr><td colspan="5"><div class="contact-inbox-empty"><i class="bi bi-inbox"></i><strong>No messages found</strong><span>Messages submitted through the public Contact Us form will appear here.</span></div></td></tr>
  @endforelse
  </tbody>
</table>
</div>
@include('admin.shared._pagination', ['paginator' => $messages])
