<div class="table-responsive"><table class="module-table table"><thead><tr><th>Date & Time</th><th>Administrator</th><th>Module</th><th>Action</th><th>Request</th><th>IP Address</th></tr></thead><tbody>
@forelse($logs as $log)
<tr><td><strong>{{ $log->created_at?->format('d M Y') }}</strong><small class="ops-muted d-block">{{ $log->created_at?->format('h:i A') }}</small></td><td>{{ $log->actor_name ?: 'Removed user' }}</td><td><span class="soft-tag tag-violet">{{ ucfirst($log->module) }}</span></td><td><span class="soft-tag tag-active">{{ $log->action }}</span></td><td><span class="ops-code">{{ $log->method }}</span> <span title="{{ $log->path }}">{{ \Illuminate\Support\Str::limit($log->path,42) }}</span></td><td><small class="ops-muted">{{ $log->ip_address ?: '—' }}</small></td></tr>
@empty<tr><td colspan="6" class="text-center py-4">No activity logs match your filters.</td></tr>@endforelse
</tbody></table></div>
@include('admin.shared._pagination',['paginator'=>$logs])
