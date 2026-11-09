<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MediaAsset;
use App\Models\Permission;
use App\Models\Profile;
use App\Models\ProfileVersion;
use App\Models\User;
use App\Services\AdminBackupService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalProfiles = Profile::count();
        $publishedPersonalCount = Profile::published()->personal()->count();
        $publishedCompanyCount = Profile::published()->company()->count();
        $pendingSubmissionCount = ProfileVersion::pendingReview()->count();
        $pendingAccountCount = Customer::whereIn('status', ['pending_verification', 'pending_approval'])->count();

        // The status chart reflects each profile's latest version, not every historical revision.
        $latestVersionIds = ProfileVersion::query()
            ->selectRaw('MAX(id)')
            ->groupBy('profile_id');

        $latestStatusCounts = ProfileVersion::query()
            ->whereIn('id', $latestVersionIds)
            ->selectRaw('review_status, COUNT(*) as aggregate')
            ->groupBy('review_status')
            ->pluck('aggregate', 'review_status');

        $statusCounts = [
            'published' => (int) ($latestStatusCounts[ProfileVersion::APPROVED] ?? 0),
            'pending' => (int) ($latestStatusCounts[ProfileVersion::PENDING] ?? 0),
            'draft' => (int) ($latestStatusCounts[ProfileVersion::DRAFT] ?? 0),
            'changes_requested' => (int) ($latestStatusCounts['changes_requested'] ?? 0),
            'rejected' => (int) ($latestStatusCounts[ProfileVersion::REJECTED] ?? 0),
        ];
        $statusCounts['other'] = max(0, $totalProfiles - array_sum($statusCounts));
        $statusTotal = max(1, array_sum($statusCounts));

        $statusColors = [
            'published' => '#10b981',
            'pending' => '#3996fa',
            'draft' => '#9a4bfa',
            'changes_requested' => '#ff970b',
            'rejected' => '#f43f52',
            'other' => '#cbd5e1',
        ];
        $statusLegend = [];
        $offset = 0;
        $gradientParts = [];
        foreach ($statusCounts as $key => $count) {
            $percentage = round(($count / $statusTotal) * 100, 1);
            $end = $offset + $percentage;
            $gradientParts[] = $statusColors[$key] . ' ' . $offset . '% ' . $end . '%';
            $statusLegend[] = [
                'key' => $key,
                'label' => match ($key) {
                    'published' => 'Published',
                    'pending' => 'In Review',
                    'draft' => 'Draft',
                    'changes_requested' => 'Changes Requested',
                    'rejected' => 'Rejected',
                    default => 'Other / Archived',
                },
                'count' => $count,
                'percentage' => $percentage,
                'color' => $statusColors[$key],
            ];
            $offset = $end;
        }
        $statusDonutStyle = 'conic-gradient(' . implode(', ', $gradientParts) . ')';

        // The chart range is selectable and every point comes from profile creation timestamps.
        $period = in_array($request->query('period'), ['7d', '30d', '6m', '12m'], true)
            ? $request->query('period')
            : '6m';
        $growthLabels = [];
        $personalGrowth = [];
        $companyGrowth = [];

        if (in_array($period, ['7d', '30d'], true)) {
            $bucketCount = $period === '7d' ? 7 : 6;
            $bucketDays = $period === '7d' ? 1 : 5;
            $growthStartDate = now()->subDays($period === '7d' ? 6 : 29)->startOfDay();
            for ($bucket = 0; $bucket < $bucketCount; $bucket++) {
                $bucketStart = $growthStartDate->copy()->addDays($bucket * $bucketDays);
                $bucketEnd = $bucketStart->copy()->addDays($bucketDays);
                if ($bucket === $bucketCount - 1) {
                    $bucketEnd = now()->addSecond();
                }
                $growthLabels[] = $bucketStart->format('d M');
                $personalGrowth[] = Profile::query()->personal()->where('created_at', '>=', $bucketStart)->where('created_at', '<', $bucketEnd)->count();
                $companyGrowth[] = Profile::query()->company()->where('created_at', '>=', $bucketStart)->where('created_at', '<', $bucketEnd)->count();
            }
        } else {
            $bucketCount = $period === '12m' ? 12 : 6;
            $growthStartDate = now()->startOfMonth()->subMonths($bucketCount - 1);
            for ($monthOffset = $bucketCount - 1; $monthOffset >= 0; $monthOffset--) {
                $month = now()->startOfMonth()->subMonths($monthOffset);
                $nextMonth = $month->copy()->addMonth();
                $growthLabels[] = $month->format('M');
                $personalGrowth[] = Profile::query()->personal()->where('created_at', '>=', $month)->where('created_at', '<', $nextMonth)->count();
                $companyGrowth[] = Profile::query()->company()->where('created_at', '>=', $month)->where('created_at', '<', $nextMonth)->count();
            }
        }
        $growthEndDate = now();

        $chartMax = max(1, max(array_merge($personalGrowth, $companyGrowth)));
        $chartPointSet = static function (array $values) use ($chartMax): array {
            $lastIndex = max(1, count($values) - 1);
            $points = [];
            foreach ($values as $index => $value) {
                $x = (int) round(($index / $lastIndex) * 620);
                $y = (int) round(172 - (($value / $chartMax) * 150));
                $points[] = ['x' => $x, 'y' => $y];
            }
            $coordinates = array_map(fn ($point) => $point['x'] . ',' . $point['y'], $points);
            $firstX = $points[0]['x'] ?? 0;
            $lastX = $points[count($points) - 1]['x'] ?? 620;
            $line = 'M' . implode(' L', $coordinates);
            $fill = 'M' . $firstX . ',172 L' . implode(' L', $coordinates) . ' L' . $lastX . ',172 Z';

            return ['line' => $line, 'fill' => $fill, 'points' => $points];
        };
        $personalChart = $chartPointSet($personalGrowth);
        $companyChart = $chartPointSet($companyGrowth);
        $chartGridVertical = implode(' ', array_map(
            fn ($index) => 'M' . (int) round(($index / max(1, count($growthLabels) - 1)) * 620) . ' 12V172',
            array_keys($growthLabels)
        ));

        $admin = auth()->user();
        $recentCustomers = $admin?->can('customers.manage')
            ? Customer::query()->latest('id')->limit(5)->get()
            : collect();

        // Prepare plain display rows here so the Blade template only renders data.
        $recentSubmissionRows = $admin?->can('profiles.manage')
            ? ProfileVersion::query()
                ->where(function ($query): void {
                    $query->whereNotNull('submitted_by_customer_id')
                        ->orWhereNotNull('created_by_customer_id');
                })
                ->with([
                    'profile',
                    'personalDetails',
                    'companyDetails',
                    'submittedByCustomer',
                    'createdByCustomer',
                ])
                ->orderByRaw('COALESCE(submitted_at, updated_at) DESC')
                ->limit(5)
                ->get()
                ->map(function (ProfileVersion $version): array {
                    $profile = $version->profile;
                    $isPersonal = $profile?->profile_type === Profile::TYPE_PERSONAL;

                    $detailName = $isPersonal
                        ? $version->personalDetails?->full_name
                        : $version->companyDetails?->company_name;

                    $customer = $version->submittedByCustomer ?? $version->createdByCustomer;
                    $status = (string) $version->review_status;
                    $date = $version->submitted_at ?? $version->updated_at;

                    $actionUrl = null;
                    $actionLabel = null;

                    if ($status === ProfileVersion::PENDING) {
                        $actionUrl = route('admin.profiles.review', $version);
                        $actionLabel = 'Review';
                    } elseif ($profile) {
                        $actionUrl = route('admin.profiles.edit', $profile);
                        $actionLabel = 'View';
                    }

                    return [
                        'name' => $detailName ?: $profile?->slug ?: $version->proposed_slug ?: 'Untitled profile',
                        'is_personal' => $isPersonal,
                        'customer_email' => $customer?->email ?: '—',
                        'date_label' => $date?->format('d M Y') ?: '—',
                        'status_label' => ucwords(str_replace('_', ' ', $status)),
                        'status_class' => match ($status) {
                            'pending' => 'pill-review',
                            'approved' => 'pill-published',
                            'rejected', 'changes_requested' => 'pill-changes',
                            default => 'pill-draft',
                        },
                        'action_url' => $actionUrl,
                        'action_label' => $actionLabel,
                    ];
                })
            : collect();

        $mediaTotals = MediaAsset::query()
            ->selectRaw('COUNT(*) as asset_count, COALESCE(SUM(size_bytes), 0) as total_bytes')
            ->first();
        $imageBytes = (int) MediaAsset::query()->where('mime_type', 'like', 'image/%')->sum('size_bytes');
        $documentBytes = (int) MediaAsset::query()->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->sum('size_bytes');
        $otherBytes = max(0, (int) ($mediaTotals->total_bytes ?? 0) - $imageBytes - $documentBytes);
        $totalMediaBytes = (int) ($mediaTotals->total_bytes ?? 0);
        $storagePercent = $totalMediaBytes > 0 ? round(($imageBytes / $totalMediaBytes) * 100, 1) : 0;

        $formatBytes = static function (int $bytes): string {
            if ($bytes <= 0) {
                return '0 B';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

            return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1) . ' ' . $units[$power];
        };

        $lastBackup = null;
        try {
            $lastBackup = app(AdminBackupService::class)->all()[0]['created'] ?? null;
        } catch (\Throwable $exception) {
            report($exception);
        }

        return view('admin.dashboard.dashboard', [
            'userCount' => User::whereDoesntHave('roles', fn ($query) => $query->where('name', 'Super Admin'))->count(),
            'roleCount' => Role::where('name', '!=', 'Super Admin')->count(),
            'permissionCount' => Permission::count(),
            'pendingAccountCount' => $pendingAccountCount,
            'recentCustomers' => $recentCustomers,
            'totalProfiles' => $totalProfiles,
            'publishedPersonalCount' => $publishedPersonalCount,
            'publishedCompanyCount' => $publishedCompanyCount,
            'pendingSubmissionCount' => $pendingSubmissionCount,
            'statusCounts' => $statusCounts,
            'statusLegend' => $statusLegend,
            'statusTotal' => array_sum($statusCounts),
            'statusDonutStyle' => $statusDonutStyle,
            'growthLabels' => $growthLabels,
            'growthPeriod' => $period,
            'growthStartDate' => $growthStartDate,
            'growthEndDate' => $growthEndDate,
            'personalGrowth' => $personalGrowth,
            'companyGrowth' => $companyGrowth,
            'chartMax' => $chartMax,
            'personalChart' => $personalChart,
            'companyChart' => $companyChart,
            'chartGridVertical' => $chartGridVertical,
            'recentSubmissionRows' => $recentSubmissionRows,
            'mediaAssetCount' => (int) ($mediaTotals->asset_count ?? 0),
            'totalMediaBytes' => $totalMediaBytes,
            'imageBytes' => $imageBytes,
            'documentBytes' => $documentBytes,
            'otherBytes' => $otherBytes,
            'storagePercent' => $storagePercent,
            'formatBytes' => $formatBytes,
            'lastBackup' => $lastBackup ? Carbon::createFromTimestamp($lastBackup) : null,
            'databaseDriver' => DB::connection()->getDriverName(),
        ]);
    }
}
