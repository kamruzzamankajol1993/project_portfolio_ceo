@extends('admin.master.master')
@section('title','Storage & Cleanup')
@section('body')
<div class="module-heading"><div><div class="eyebrow">FILE HEALTH</div><h1><span class="heading-icon"><i class="bi bi-device-hdd"></i></span>Storage & Cleanup</h1><p>Live disk usage and safe, limited cleanup tools.</p></div></div>
<div class="ops-stats">
 @foreach(['uploads'=>['Images','bi-images','blue'],'logs'=>['App logs','bi-file-earmark-text','amber'],'backups'=>['Backups','bi-archive','violet']] as $key=>$info)
 <div class="ops-stat {{ $info[2] }}"><i class="bi {{ $info[1] }}"></i><div><strong>{{ number_format($usage[$key]['bytes']/1048576,2) }} MB</strong><span>{{ $info[0] }} · {{ $usage[$key]['count'] }} files</span></div></div>
 @endforeach
 <div class="ops-stat mint"><i class="bi bi-images"></i><div><strong>{{ number_format($mediaCount) }}</strong><span>Managed images</span></div></div>
</div>
<div class="ops-two-col">
 <section class="module-panel"><div class="ops-section-heading"><h2><i class="bi bi-recycle"></i> Unused images</h2><span class="soft-tag tag-violet">{{ $orphanCount }} detected</span></div>
 <p class="ops-note">Only UUID WebP files inside <code>public/uploads/users</code> and <code>public/uploads/settings</code> are eligible. Files referenced by Users, Settings or SEO Pages are never intentionally deleted.</p>
 <div class="ops-subtitle">Eligible for cleanup (older than 24h): <strong>{{ count($safeOrphans) }}</strong></div>
 <div class="ops-mini-list">@forelse(array_slice($safeOrphans,0,8) as $item)<div><i class="bi bi-image"></i><span title="{{ $item['path'] }}">{{ \Illuminate\Support\Str::limit($item['path'],48) }}</span><small>{{ number_format($item['size']/1024,1) }} KB</small></div>@empty<p class="ops-muted mb-0">No old unreferenced files found.</p>@endforelse</div>
 @can('storage.cleanup')<form method="post" action="{{ route('admin.storage.cleanup') }}" data-confirm="Delete unreferenced images older than 24 hours?" class="ops-actions">@csrf<input type="hidden" name="target" value="orphans"><button class="module-secondary ops-danger" @disabled(count($safeOrphans)===0)><i class="bi bi-trash3"></i> Clean unused images</button></form>@endcan
 </section>
 <section class="module-panel"><div class="ops-section-heading"><h2><i class="bi bi-journal-x"></i> Old application logs</h2><span class="soft-tag">30+ days</span></div>
 <p class="ops-note">Clear rotated files named <code>laravel-YYYY-MM-DD.log</code> older than 30 days. The current <code>laravel.log</code> file and activity database records are untouched.</p>
 @can('storage.cleanup')<form method="post" action="{{ route('admin.storage.cleanup') }}" data-confirm="Remove old rotated Laravel log files?" class="ops-actions">@csrf<input type="hidden" name="target" value="old_logs"><button class="module-secondary"><i class="bi bi-broom"></i> Clear old log files</button></form>@endcan
 <div class="ops-infobox"><i class="bi bi-shield-check"></i> No arbitrary paths, database records or active media are selected for cleanup.</div>
 </section>
</div>
@endsection
