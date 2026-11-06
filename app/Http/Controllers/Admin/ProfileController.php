<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Industry;
use App\Models\Profile;
use App\Models\ProfileReviewLog;
use App\Models\ProfileVersion;
use App\Services\ProfileWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $query = Profile::query()->with([
            'publishedVersion.personalDetails',
            'publishedVersion.companyDetails',
            'latestVersion',
            'createdByCustomer',
            'customerAccess.customer',
        ]);

        if ($request->filled('type')) {
            $query->where('profile_type', $request->string('type')->toString());
        }
        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'pending') {
                $query->whereHas('versions', fn ($builder) => $builder->where('review_status', ProfileVersion::PENDING));
            } elseif ($status === 'published') {
                $query->where('status', 'published');
            } elseif ($status === 'draft') {
                $query->where('status', 'draft')->whereDoesntHave('versions', fn ($builder) => $builder->where('review_status', ProfileVersion::PENDING));
            }
        }
        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($term): void {
                $builder->where('slug', 'like', "%{$term}%")
                    ->orWhereHas('publishedVersion.personalDetails', fn ($details) => $details->where('full_name', 'like', "%{$term}%"))
                    ->orWhereHas('publishedVersion.companyDetails', fn ($details) => $details->where('company_name', 'like', "%{$term}%"));
            });
        }

        $profiles = $query->latest('id')->paginate(20)->withQueryString();
        $stats = [
            'total' => Profile::count(),
            'published' => Profile::where('status', 'published')->count(),
            'pending' => ProfileVersion::where('review_status', ProfileVersion::PENDING)->count(),
            'drafts' => ProfileVersion::whereIn('review_status', [ProfileVersion::DRAFT, 'changes_requested'])->count(),
        ];

        return view('admin.profiles.index', compact('profiles', 'stats'));
    }

    public function submissions(Request $request): View
    {
        $query = ProfileVersion::query()
            ->where('review_status', ProfileVersion::PENDING)
            ->with([
                'profile.publishedVersion.personalDetails',
                'profile.publishedVersion.companyDetails',
                'personalDetails', 'companyDetails', 'submittedByCustomer',
            ]);

        if ($request->filled('type')) {
            $query->whereHas('profile', fn ($builder) => $builder->where('profile_type', $request->string('type')->toString()));
        }
        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($term): void {
                $builder->whereHas('personalDetails', fn ($details) => $details->where('full_name', 'like', "%{$term}%"))
                    ->orWhereHas('companyDetails', fn ($details) => $details->where('company_name', 'like', "%{$term}%"))
                    ->orWhereHas('submittedByCustomer', fn ($customer) => $customer->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            });
        }

        $submissions = $query->latest('submitted_at')->paginate(20)->withQueryString();
        $pendingCount = ProfileVersion::where('review_status', ProfileVersion::PENDING)->count();
        return view('admin.profiles.submissions', compact('submissions', 'pendingCount'));
    }


    public function individualProfiles(Request $request): View
    {
        $request->merge(['type' => Profile::TYPE_PERSONAL]);
        return $this->index($request);
    }

    public function companyProfiles(Request $request): View
    {
        $request->merge(['type' => Profile::TYPE_COMPANY]);
        return $this->index($request);
    }

    public function revisionHistory(Request $request): View
    {
        $query = ProfileReviewLog::query()
            ->with([
                'version.profile',
                'version.personalDetails',
                'version.companyDetails',
                'admin',
                'customer',
            ]);

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        if ($request->filled('type')) {
            $query->whereHas('version.profile', fn ($profileQuery) =>
                $profileQuery->where('profile_type', $request->string('type')->toString())
            );
        }

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->whereHas('version.profile', fn ($profileQuery) =>
                $profileQuery->where('slug', 'like', "%{$term}%")
                    ->orWhereHas('publishedVersion.personalDetails', fn ($details) =>
                        $details->where('full_name', 'like', "%{$term}%")
                    )
                    ->orWhereHas('publishedVersion.companyDetails', fn ($details) =>
                        $details->where('company_name', 'like', "%{$term}%")
                    )
            );
        }

        $logs = $query->latest('created_at')->paginate(30)->withQueryString();

        return view('admin.profiles.revision-history', compact('logs'));
    }

    public function verificationCenter(Request $request): View
    {
        $query = Profile::query()->with([
            'publishedVersion.personalDetails',
            'publishedVersion.companyDetails',
            'createdByCustomer',
            'customerAccess.customer',
        ]);

        if ($request->filled('type') && in_array($request->type, ['personal', 'company'], true)) {
            $query->where('profile_type', $request->type);
        }

        if ($request->filled('verification')) {
            $query->where('is_verified', $request->verification === 'verified');
        }

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($term): void {
                $builder->where('slug', 'like', "%{$term}%")
                    ->orWhereHas('publishedVersion.personalDetails', fn ($details) => $details->where('full_name', 'like', "%{$term}%"))
                    ->orWhereHas('publishedVersion.companyDetails', fn ($details) => $details->where('company_name', 'like', "%{$term}%"));
            });
        }

        $profiles = $query->latest('updated_at')->paginate(25)->withQueryString();
        $stats = [
            'verified' => Profile::where('is_verified', true)->count(),
            'unverified' => Profile::where('is_verified', false)->count(),
            'published' => Profile::published()->count(),
        ];

        return view('admin.profiles.verification-center', compact('profiles', 'stats'));
    }

    public function toggleVerification(Request $request, Profile $profile): RedirectResponse
    {
        $data = $request->validate([
            'is_verified' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $profile->forceFill(['is_verified' => (bool) $data['is_verified']])->save();

        $versionId = $profile->published_version_id
            ?: $profile->versions()->latest('version_number')->value('id');

        if ($versionId) {
            ProfileReviewLog::create([
                'profile_version_id' => $versionId,
                'action' => $data['is_verified'] ? 'verified' : 'verification_removed',
                'admin_id' => $request->user()->id,
                'note' => $data['note'] ?? ($data['is_verified'] ? 'Profile verified by administrator.' : 'Profile verification removed by administrator.'),
                'created_at' => now(),
            ]);
        }

        return back()->with('success', $data['is_verified'] ? 'Profile marked as verified.' : 'Profile verification removed.');
    }

    public function create(): View
    {
        return view('admin.profiles.form', [
            'profile' => new Profile(),
            'version' => new ProfileVersion(),
            'details' => null,
            'industries' => Industry::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'customers' => Customer::where('status', 'active')->whereNotNull('email_verified_at')->orderBy('name')->get(),
            'relatedProfiles' => Profile::published()->with(['publishedVersion.personalDetails', 'publishedVersion.companyDetails'])->orderBy('slug')->get(),
            'formRelationships' => [['related_profile_id' => '', 'relation_type' => 'founder', 'position' => '', 'description' => '']],
            'formMode' => 'create',
        ]);
    }

    public function store(Request $request, ProfileWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate($this->rules($request->string('profile_type')->toString()));
        $version = $workflow->saveAdminProfile($request->user(), $request, $data);

        return redirect()->route('admin.profiles.edit', $version->profile)
            ->with('success', !empty($data['publish_now']) ? 'Profile created and published successfully.' : 'Profile draft created successfully.');
    }

    public function edit(Profile $profile): View
    {
        $latest = $profile->versions()->with([
            'personalDetails', 'companyDetails', 'sections.items', 'links', 'references', 'mediaLinks.media', 'relationships.relatedProfile.publishedVersion.personalDetails', 'relationships.relatedProfile.publishedVersion.companyDetails', 'seo',
        ])->orderByDesc('version_number')->first();
        abort_if($latest?->review_status === ProfileVersion::PENDING, 422, 'Review the pending submission before editing this profile.');

        $version = $latest && in_array($latest->review_status, [ProfileVersion::DRAFT, 'changes_requested', ProfileVersion::REJECTED], true)
            ? $latest
            : $profile->publishedVersion()->with([
                'personalDetails', 'companyDetails', 'sections.items', 'links', 'references', 'mediaLinks.media', 'relationships.relatedProfile.publishedVersion.personalDetails', 'relationships.relatedProfile.publishedVersion.companyDetails', 'seo',
            ])->first();

        $details = $version?->personalDetails ?? $version?->companyDetails;

        return view('admin.profiles.form', [
            'profile' => $profile,
            'version' => $version ?: new ProfileVersion(),
            'details' => $details,
            'industries' => Industry::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'customers' => Customer::where('status', 'active')->whereNotNull('email_verified_at')->orderBy('name')->get(),
            'relatedProfiles' => Profile::published()->where('id', '!=', $profile->id)->with(['publishedVersion.personalDetails', 'publishedVersion.companyDetails'])->orderBy('slug')->get(),
            'formRelationships' => old('relationships', $version?->relationships?->map(fn ($item) => $item->only(['related_profile_id', 'relation_type', 'position', 'description']))->all() ?: [['related_profile_id' => '', 'relation_type' => 'founder', 'position' => '', 'description' => '']]),
            'formMode' => 'edit',
        ]);
    }

    public function update(Request $request, Profile $profile, ProfileWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate($this->rules($profile->profile_type, $profile));
        $data['profile_type'] = $profile->profile_type;
        $version = $workflow->saveAdminProfile($request->user(), $request, $data, $profile);

        $profile->refresh();
        return redirect()->route('admin.profiles.edit', $profile)
            ->with('success', !empty($data['publish_now']) ? 'Profile changes published successfully.' : 'Profile draft saved successfully.');
    }

    public function review(ProfileVersion $version): View
    {
        $version->load([
            'profile.publishedVersion.personalDetails',
            'profile.publishedVersion.companyDetails',
            'personalDetails.industry', 'companyDetails.industry',
            'sections.items', 'links', 'references', 'mediaLinks.media', 'relationships.relatedProfile.publishedVersion.personalDetails', 'relationships.relatedProfile.publishedVersion.companyDetails', 'seo',
            'submittedByCustomer', 'reviewLogs.admin', 'reviewLogs.customer',
        ]);

        return view('admin.profiles.review', compact('version'));
    }

    public function approve(ProfileVersion $version, ProfileWorkflowService $workflow): RedirectResponse
    {
        DB::transaction(function () use ($version, $workflow): void {
            $locked = ProfileVersion::query()->lockForUpdate()->findOrFail($version->id);
            abort_unless($locked->review_status === ProfileVersion::PENDING, 422, 'This version is no longer waiting for review.');
            $workflow->publishVersion($locked, auth()->user(), 'Approved by administrator.');
        });

        return redirect()->route('admin.profiles.submissions')->with('success', 'Profile version approved and published.');
    }

    public function returnForChanges(Request $request, ProfileVersion $version, ProfileWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate(['review_note' => ['required', 'string', 'min:5', 'max:2000']]);
        abort_unless($version->review_status === ProfileVersion::PENDING, 422, 'Only pending submissions can be returned.');

        DB::transaction(fn () => $workflow->requestChanges($version, $request->user(), $data['review_note']));
        return redirect()->route('admin.profiles.submissions')->with('success', 'Profile returned to the customer for correction.');
    }

    public function reject(Request $request, ProfileVersion $version, ProfileWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:2000']]);
        abort_unless($version->review_status === ProfileVersion::PENDING, 422, 'Only pending submissions can be declined.');

        DB::transaction(fn () => $workflow->rejectVersion($version, $request->user(), $data['review_note'] ?? null));
        return redirect()->route('admin.profiles.submissions')->with('success', 'Profile submission declined.');
    }

    public function history(Profile $profile): View
    {
        $versions = $profile->versions()->with([
            'personalDetails', 'companyDetails', 'submittedByCustomer', 'reviewedByAdmin',
            'reviewLogs.admin', 'reviewLogs.customer',
        ])->orderByDesc('version_number')->get();

        return view('admin.profiles.history', compact('profile', 'versions'));
    }

    private function rules(string $profileType, ?Profile $profile = null): array
    {
        abort_unless(in_array($profileType, [Profile::TYPE_PERSONAL, Profile::TYPE_COMPANY], true), 422, 'Invalid profile type.');
        $slugRules = ['nullable', 'string', 'max:190', 'regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/'];
        $slugRules[] = $profile ? Rule::unique('profiles', 'slug')->ignore($profile->id) : Rule::unique('profiles', 'slug');

        $rules = [
            'profile_type' => [$profile ? 'nullable' : 'required', Rule::in([Profile::TYPE_PERSONAL, Profile::TYPE_COMPANY])],
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->where(fn ($query) => $query->where('status', 'active')->whereNotNull('email_verified_at'))],
            'proposed_slug' => $slugRules,
            'change_summary' => ['nullable', 'string', 'max:2000'],
            'publish_now' => ['nullable', 'boolean'],
            'is_verified' => ['nullable', 'boolean'],
            'industry_id' => ['nullable', 'integer', 'exists:industries,id'],
            'links' => ['nullable', 'array', 'max:20'],
            'links.*.link_type' => ['nullable', 'string', 'max:50'],
            'links.*.label' => ['nullable', 'string', 'max:120'],
            'links.*.url' => ['nullable', 'url', 'max:500'],
            'links.*.icon' => ['nullable', 'string', 'max:100'],
            'references' => ['nullable', 'array', 'max:30'],
            'references.*.title' => ['nullable', 'string', 'max:255'],
            'references.*.publisher' => ['nullable', 'string', 'max:180'],
            'references.*.url' => ['nullable', 'url', 'max:500'],
            'references.*.source_date' => ['nullable', 'date'],
            'references.*.description' => ['nullable', 'string', 'max:2000'],
            'sections' => ['nullable', 'array', 'max:20'],
            'sections.*.title' => ['nullable', 'string', 'max:190'],
            'sections.*.intro' => ['nullable', 'string', 'max:5000'],
            'sections.*.section_type' => ['nullable', 'string', 'max:40'],
            'sections.*.layout' => ['nullable', 'string', 'max:40'],
            'sections.*.is_visible' => ['nullable', 'boolean'],
            'sections.*.items' => ['nullable', 'array', 'max:30'],
            'sections.*.items.*.title' => ['nullable', 'string', 'max:220'],
            'sections.*.items.*.subtitle' => ['nullable', 'string', 'max:220'],
            'sections.*.items.*.description' => ['nullable', 'string', 'max:10000'],
            'sections.*.items.*.icon' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.image_path' => ['nullable', 'string', 'max:500'],
            'sections.*.items.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sections.*.items.*.is_visible' => ['nullable', 'boolean'],
            'sections.*.items.*.url' => ['nullable', 'url', 'max:500'],
            'gallery' => ['nullable', 'array', 'max:12'],
            'gallery.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'relationships' => ['nullable', 'array', 'max:30'],
            'relationships.*.related_profile_id' => ['nullable', 'integer', Rule::exists('profiles', 'id')->where(fn ($query) => $query->where('status', 'published'))],
            'relationships.*.relation_type' => ['nullable', 'string', 'max:60'],
            'relationships.*.position' => ['nullable', 'string', 'max:180'],
            'relationships.*.description' => ['nullable', 'string', 'max:2000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:5000'],
            'robots' => ['nullable', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:500'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:500'],
            'twitter_image' => ['nullable', 'url', 'max:500'],
        ];

        if ($profileType === Profile::TYPE_PERSONAL) {
            return array_merge($rules, [
                'full_name' => ['required', 'string', 'max:180'],
                'headline' => ['nullable', 'string', 'max:255'],
                'professional_title' => ['nullable', 'string', 'max:180'],
                'current_position' => ['nullable', 'string', 'max:180'],
                'company_organisation' => ['nullable', 'string', 'max:180'],
                'location' => ['nullable', 'string', 'max:180'],
                'hometown' => ['nullable', 'string', 'max:180'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
                'occupation' => ['nullable', 'string', 'max:180'],
                'public_contact_email' => ['nullable', 'email', 'max:191'],
                'public_phone' => ['nullable', 'string', 'max:30'],
                'personal_website' => ['nullable', 'url', 'max:255'],
                'short_bio' => ['nullable', 'string', 'max:5000'],
                'about' => ['nullable', 'string', 'max:30000'],
                'profile_quote' => ['nullable', 'string', 'max:5000'],
                'early_life' => ['nullable', 'string', 'max:30000'],
                'career_entrepreneurship' => ['nullable', 'string', 'max:30000'],
                'education_summary' => ['nullable', 'string', 'max:30000'],
                'awards_publications_recognition' => ['nullable', 'string', 'max:30000'],
                'show_public_email' => ['nullable', 'boolean'],
                'show_public_phone' => ['nullable', 'boolean'],
                'show_date_of_birth' => ['nullable', 'boolean'],
                'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ]);
        }

        return array_merge($rules, [
            'company_name' => ['required', 'string', 'max:180'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:' . now()->year],
            'headquarters' => ['nullable', 'string', 'max:180'],
            'company_size' => ['nullable', 'string', 'max:60'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'area_served' => ['nullable', 'string', 'max:255'],
            'official_website' => ['nullable', 'url', 'max:255'],
            'public_email' => ['nullable', 'email', 'max:191'],
            'public_phone' => ['nullable', 'string', 'max:30'],
            'short_description' => ['nullable', 'string', 'max:5000'],
            'overview' => ['nullable', 'string', 'max:30000'],
            'history_background' => ['nullable', 'string', 'max:30000'],
            'mission' => ['nullable', 'string', 'max:10000'],
            'vision' => ['nullable', 'string', 'max:10000'],
            'accuracy_cta_enabled' => ['nullable', 'boolean'],
            'accuracy_cta_title' => ['nullable', 'string', 'max:190'],
            'accuracy_cta_description' => ['nullable', 'string', 'max:5000'],
            'accuracy_cta_button_label' => ['nullable', 'string', 'max:120'],
            'accuracy_cta_button_url' => ['nullable', 'url', 'max:500'],
            'accuracy_cta_secondary_label' => ['nullable', 'string', 'max:120'],
            'accuracy_cta_secondary_url' => ['nullable', 'url', 'max:500'],
            'parent_company' => ['nullable', 'string', 'max:180'],
            'show_public_email' => ['nullable', 'boolean'],
            'show_public_phone' => ['nullable', 'boolean'],
            'company_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'company_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);
    }
}
