<div class="table-responsive">
    <table class="table admin-table module-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Requested profile</th>
                <th>Message</th>
                <th>Requested on</th>
                <th>Status</th>
                <th class="actions-header">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $profileRequest)
                <tr>
                    <td>
                        @if($profileRequest->customer)
                            <strong>{{ $profileRequest->customer->name }}</strong><br>
                            <small>{{ $profileRequest->customer->email }}</small>
                        @else
                            <span class="text-muted">Customer account deleted</span>
                        @endif
                    </td>
                    <td><span class="soft-tag">{{ str($profileRequest->requested_profile_type)->title() }}</span></td>
                    <td style="min-width:180px;max-width:300px;white-space:normal">{{ $profileRequest->message ?: '—' }}</td>
                    <td>{{ $profileRequest->created_at?->format('d M Y, h:i A') }}</td>
                    <td>
                        @php($statusClass = $profileRequest->status === 'approved' ? 'tag-active' : ($profileRequest->status === 'rejected' ? 'tag-inactive' : 'tag-violet'))
                        <span class="soft-tag {{ $statusClass }}">{{ str($profileRequest->status)->title() }}</span>
                        @if($profileRequest->review_note)
                            <small class="d-block text-muted mt-1">{{ $profileRequest->review_note }}</small>
                        @endif
                    </td>
                    <td class="actions-cell">
                        @if($profileRequest->status === 'pending' && $profileRequest->customer?->isActive())
                            <div class="d-flex justify-content-end gap-2">
                                <form method="post" action="{{ route('admin.profile-requests.approve', $profileRequest) }}" data-confirm="Approve this profile creation request?" data-confirm-button="Approve request">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="post" action="{{ route('admin.profile-requests.reject', $profileRequest) }}" data-confirm="Decline this profile creation request?" data-confirm-button="Decline request">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            </div>
                        @elseif($profileRequest->status === 'pending')
                            <span class="text-muted small">Customer account not active</span>
                        @else
                            <span class="text-muted small">Reviewed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">No profile requests found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.shared._pagination', ['paginator' => $requests])
