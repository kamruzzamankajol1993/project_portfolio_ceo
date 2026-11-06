<div class="table-responsive">
    <table class="table admin-table module-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone</th>
                <th>Email verification</th>
                <th>Account status</th>
                <th>Registered</th>
                <th class="actions-header">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>
                        <div class="user-line">
                            <span class="user-thumbnail fallback">{{ strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                            <div>
                                <strong>{{ $customer->name }}</strong>
                                <small>{{ $customer->email }} · {{ $customer->customer_code }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $customer->phone ?: '—' }}</td>
                    <td>
                        @if($customer->hasVerifiedEmail())
                            <span class="soft-tag tag-active">Verified</span>
                        @else
                            <span class="soft-tag tag-inactive">Not verified</span>
                        @endif
                    </td>
                    <td>
                        @php($statusClass = $customer->status === 'active' ? 'tag-active' : ($customer->status === 'rejected' ? 'tag-inactive' : 'tag-violet'))
                        <span class="soft-tag {{ $statusClass }}">{{ str($customer->status)->replace('_', ' ')->title() }}</span>
                    </td>
                    <td>{{ $customer->created_at?->format('d M Y') }}</td>
                    <td class="actions-cell">
                        @if($customer->status === 'pending_approval' && $customer->hasVerifiedEmail())
                            <div class="d-flex justify-content-end gap-2">
                                <form method="post" action="{{ route('admin.customer-accounts.approve', $customer) }}" data-confirm="Approve this customer account?" data-confirm-button="Approve account">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="post" action="{{ route('admin.customer-accounts.reject', $customer) }}" data-confirm="Decline this customer account?" data-confirm-button="Decline account">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">No customer accounts found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.shared._pagination', ['paginator' => $customers])
