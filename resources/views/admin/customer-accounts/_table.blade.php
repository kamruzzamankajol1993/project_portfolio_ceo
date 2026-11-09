<div class="customer-bulk-toolbar">
    <div class="small text-secondary">
        <i class="bi bi-check2-square me-1"></i>
        <span id="selected-customer-count">0</span> selected on this page
    </div>
    <form id="bulkDeleteCustomersForm" method="post" action="{{ route('admin.customer-accounts.bulk-destroy') }}" data-confirm="Permanently delete all selected customer accounts? Related customer access requests and access grants will also be removed." data-confirm-button="Delete selected accounts">
        @csrf
        @method('DELETE')
        <button id="bulk-delete-customers-button" type="submit" class="customer-bulk-delete" disabled>
            <i class="bi bi-trash3"></i> Delete selected (<span id="bulk-delete-count">0</span>)
        </button>
    </form>
</div>

<div class="table-responsive">
    <table class="table admin-table module-table customer-accounts-table">
        <thead>
            <tr>
                <th class="customer-select-cell">
                    <input id="select-all-customers" type="checkbox" aria-label="Select all customers on this page" title="Select all on this page">
                </th>
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
                    <td class="customer-select-cell">
                        <input class="customer-row-checkbox" form="bulkDeleteCustomersForm" type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" aria-label="Select {{ $customer->name }}">
                    </td>
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
                        @php($statusClass = $customer->status === 'active' ? 'tag-active' : (in_array($customer->status, ['rejected', 'inactive'], true) ? 'tag-inactive' : 'tag-violet'))
                        <span class="soft-tag {{ $statusClass }}">{{ str($customer->status)->replace('_', ' ')->title() }}</span>
                    </td>
                    <td>{{ $customer->created_at?->format('d M Y') }}</td>
                    <td class="actions-cell">
                        <div class="row-actions customer-row-actions">
                            <a class="row-edit action-icon" href="{{ route('admin.customer-accounts.edit', array_merge(['customer' => $customer], request()->only(['search', 'status', 'page']))) }}" title="Edit customer" aria-label="Edit {{ $customer->name }}">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                            </a>

                            @if($customer->status === 'pending_approval' && $customer->hasVerifiedEmail())
                                <form method="post" action="{{ route('admin.customer-accounts.approve', $customer) }}" data-confirm="Approve this customer account?" data-confirm-button="Approve account">
                                    @csrf
                                    <button type="submit" class="customer-status-action is-activate" title="Approve and activate" aria-label="Approve {{ $customer->name }}">
                                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                                    </button>
                                </form>
                                <form method="post" action="{{ route('admin.customer-accounts.reject', $customer) }}" data-confirm="Decline this customer account?" data-confirm-button="Decline account">
                                    @csrf
                                    <button type="submit" class="customer-status-action is-deactivate" title="Reject account" aria-label="Reject {{ $customer->name }}">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @elseif($customer->hasVerifiedEmail() && in_array($customer->status, ['active', 'inactive', 'rejected'], true))
                                <form method="post" action="{{ route('admin.customer-accounts.toggle-status', $customer) }}" data-confirm="{{ $customer->status === 'active' ? 'Deactivate this customer account?' : 'Activate this customer account?' }}" data-confirm-button="{{ $customer->status === 'active' ? 'Deactivate account' : 'Activate account' }}">
                                    @csrf
                                    <button type="submit" class="customer-status-action {{ $customer->status === 'active' ? 'is-deactivate' : 'is-activate' }}" title="{{ $customer->status === 'active' ? 'Deactivate account' : 'Activate account' }}" aria-label="{{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }} {{ $customer->name }}">
                                        <i class="bi {{ $customer->status === 'active' ? 'bi-toggle-on' : 'bi-toggle-off' }}" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @else
                                <span class="customer-status-locked" title="The customer must verify their email or complete approval first" aria-label="Account action unavailable">
                                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                </span>
                            @endif

                            <form method="post" action="{{ route('admin.customer-accounts.destroy', $customer) }}" data-confirm="Permanently delete {{ $customer->name }}'s customer account? Related access requests and grants will also be removed." data-confirm-button="Delete account">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="row-delete action-icon" title="Delete customer" aria-label="Delete {{ $customer->name }}">
                                    <i class="bi bi-trash3" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4">No customer accounts found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('admin.shared._pagination', ['paginator' => $customers])
