@extends('admin.master.master')
@section('title','Backup & Maintenance')
@section('body')
<div class="module-heading"><div><div class="eyebrow">OPERATIONS</div><h1><span class="heading-icon"><i class="bi bi-cloud-arrow-up"></i></span>Backup & Maintenance</h1><p>Private backups, public maintenance mode and cache management.</p></div></div>
<div class="ops-two-col">
 <section class="module-panel"><div class="ops-section-heading"><h2><i class="bi bi-shield-lock"></i> Create backup</h2><span class="soft-tag tag-active">Private storage</span></div>
 <p class="ops-note">Exports your MySQL/SQLite database. @if($zipAvailable) ZIP also includes managed image uploads. @else PHP ZIP is unavailable, so the export contains SQL only. @endif Database recovery requires manual import.</p>
 <form action="{{ route('admin.backups.create') }}" method="post" data-confirm="Create a new private database backup now?" class="ops-actions">@csrf<button class="module-btn" type="submit"><i class="bi bi-plus-circle"></i> Create new backup</button></form>
 <div class="ops-infobox"><i class="bi bi-lock"></i> Backups are stored outside the public web directory. Downloads require backup permission.</div>
 </section>
 <section class="module-panel"><div class="ops-section-heading"><h2><i class="bi bi-cone-striped"></i> Maintenance mode</h2><span class="soft-tag {{ $siteSetting->maintenance_mode ? 'tag-inactive':'tag-active' }}">{{ $siteSetting->maintenance_mode ? 'ON':'OFF' }}</span></div>
 <p class="ops-note">Temporarily show the maintenance page to public homepage visitors. Administrators keep access.</p>
 <form action="{{ route('admin.backups.maintenance') }}" method="post" class="ops-actions" data-confirm="Change public maintenance mode?">@csrf<input type="hidden" name="enabled" value="{{ $siteSetting->maintenance_mode ? 0 : 1 }}"><button class="module-secondary"><i class="bi bi-power"></i> {{ $siteSetting->maintenance_mode ? 'Disable':'Enable' }} maintenance</button></form>
 </section>
</div>
<section class="module-panel module-list-panel"><div class="ops-section-heading"><h2><i class="bi bi-archive"></i> Backup history</h2><span class="soft-tag">{{ count($backups) }} file(s)</span></div><div class="table-responsive"><table class="module-table table"><thead><tr><th>Backup file</th><th>Created</th><th>Size</th><th class="actions-header">Actions</th></tr></thead><tbody>
@forelse($backups as $backup)
<tr><td><strong><i class="bi bi-file-earmark-zip text-primary me-2"></i>{{ $backup['name'] }}</strong></td><td>{{ date('d M Y · h:i A',$backup['created']) }}</td><td>{{ number_format($backup['size']/1048576,2) }} MB</td><td class="actions-cell"><div class="row-actions d-flex"><a class="row-edit action-icon" title="Download" aria-label="Download backup" href="{{ route('admin.backups.download',$backup['name']) }}"><i class="bi bi-download"></i></a><form method="post" action="{{ route('admin.backups.destroy',$backup['name']) }}" data-confirm="Permanently remove this backup?"><input type="hidden" name="_method" value="DELETE">@csrf<button type="submit" class="row-delete action-icon" title="Delete" aria-label="Delete backup"><i class="bi bi-trash3"></i></button></form></div></td></tr>
@empty<tr><td colspan="4" class="text-center py-4">No backups yet. Create your first private backup.</td></tr>@endforelse
</tbody></table></div></section>
<section class="module-panel ops-maintenance-panel"><div><h2><i class="bi bi-lightning-charge"></i> Clear application cache</h2><p>Use after deployments or changes. Does not delete user uploads or backups.</p></div><div class="ops-inline"><form method="post" action="{{ route('admin.backups.cache') }}">@csrf<input type="hidden" name="scope" value="views"><button class="module-secondary"><i class="bi bi-window"></i> Clear views</button></form><form method="post" action="{{ route('admin.backups.cache') }}" data-confirm="Clear application configuration, route, view and app caches?">@csrf<input type="hidden" name="scope" value="app"><button class="module-secondary"><i class="bi bi-arrow-clockwise"></i> Optimize clear</button></form></div></section>
@endsection
