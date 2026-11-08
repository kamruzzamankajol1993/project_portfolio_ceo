<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Industry;
use App\Models\Profile;
use App\Models\ProfileVersion;
use App\Services\ProfileWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerProfileController extends Controller
{
    public function editCompany(Request $request): View
    {
        return $this->editType($request, Profile::TYPE_COMPANY);
    }

    public function editPersonal(Request $request): View
    {
        return $this->editType($request, Profile::TYPE_PERSONAL);
    }

    public function storeCompany(Request $request, ProfileWorkflowService $workflow): RedirectResponse
    {
        return $this->storeType($request, Profile::TYPE_COMPANY, $workflow);
    }

    public function storePersonal(Request $request, ProfileWorkflowService $workflow): RedirectResponse
    {
        return $this->storeType($request, Profile::TYPE_PERSONAL, $workflow);
    }

    public function updateCompany(Request $request, Profile $profile, ProfileWorkflowService $workflow): RedirectResponse
    {
        return $this->updateType($request, $profile, Profile::TYPE_COMPANY, $workflow);
    }

    public function updatePersonal(Request $request, Profile $profile, ProfileWorkflowService $workflow): RedirectResponse
    {
        return $this->updateType($request, $profile, Profile::TYPE_PERSONAL, $workflow);
    }

    private function editType(Request $request, string $profileType): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $profile = null;
        $version = null;
        $lockedForReview = false;

        if ($request->filled('profile')) {
            $profile = Profile::query()
                ->where('profile_type', $profileType)
                ->whereHas('customerAccess', fn ($query) => $query->where('customer_id', $customer->id)->whereIn('access_level', ['owner', 'editor']))
                ->with([
                    'publishedVersion.personalDetails',
                    'publishedVersion.companyDetails',
                    'publishedVersion.sections.items',
                    'publishedVersion.links',
                    'publishedVersion.references',
                    'publishedVersion.mediaLinks.media',
                    'publishedVersion.seo',
                    'publishedVersion.relationships.relatedProfile.publishedVersion.personalDetails',
                    'publishedVersion.relationships.relatedProfile.publishedVersion.companyDetails',
                ])
                ->findOrFail($request->integer('profile'));

            $latest = $profile->versions()->with([
                'personalDetails', 'companyDetails', 'sections.items', 'links', 'references', 'mediaLinks.media', 'relationships.relatedProfile.publishedVersion.personalDetails', 'relationships.relatedProfile.publishedVersion.companyDetails', 'seo',
            ])->orderByDesc('version_number')->first();

            $lockedForReview = $latest?->review_status === ProfileVersion::PENDING;
            if ($lockedForReview || ($latest && in_array($latest->review_status, [ProfileVersion::DRAFT, 'changes_requested', ProfileVersion::REJECTED], true))) {
                $version = $latest;
            } else {
                $version = $profile->publishedVersion;
            }
        } else {
            abort_unless(
                $customer->profileTypeAccess()->active()->where('profile_type', $profileType)->exists(),
                403,
                'Your profile creation access must be approved before creating this profile.'
            );
        }

        $details = $version?->personalDetails ?? $version?->companyDetails;
        $formRelationships = old('relationships', $version?->relationships?->map(fn ($item) => $item->only(['related_profile_id', 'relation_type', 'position', 'description']))->all() ?: [['related_profile_id' => '', 'relation_type' => 'founder', 'position' => '', 'description' => '']]);
        $relatedProfiles = Profile::published()->when($profile?->id, fn ($query, $id) => $query->where('id', '!=', $id))->with(['publishedVersion.personalDetails', 'publishedVersion.companyDetails'])->orderBy('slug')->get();
        $view = $profileType === Profile::TYPE_COMPANY
            ? 'website.customer.companyProfileUpdate'
            : 'website.customer.personalProfileUpdate';

        return view($view, [
            'customer' => $customer,
            'profile' => $profile,
            'version' => $version,
            'details' => $details,
            'industries' => Industry::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'lockedForReview' => $lockedForReview,
            'profileType' => $profileType,
            'formRelationships' => $formRelationships,
            'relatedProfiles' => $relatedProfiles,
        ]);
    }

    private function storeType(Request $request, string $profileType, ProfileWorkflowService $workflow): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $data = $request->validate($this->rules($profileType));
        $version = $workflow->saveCustomerProfile($customer, $profileType, $request, $data);
        $profile = $version->profile;

        if (($data['submit_action'] ?? 'draft') === 'submit') {
            return redirect()->route('customer.dashboard')->with('status', 'Your profile has been submitted. It will appear publicly only after administrator approval.');
        }

        $route = $profileType === Profile::TYPE_COMPANY ? 'customer.company-profile-update' : 'customer.personal-profile-update';
        return redirect()->route($route, ['profile' => $profile->id])->with('status', 'Your profile draft has been saved. It is not public until approved.');
    }

    private function updateType(Request $request, Profile $profile, string $profileType, ProfileWorkflowService $workflow): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        abort_unless($profile->profile_type === $profileType, 404);
        abort_unless($profile->customerAccess()->where('customer_id', $customer->id)->whereIn('access_level', ['owner', 'editor'])->exists(), 403);

        $data = $request->validate($this->rules($profileType, $profile));
        $version = $workflow->saveCustomerProfile($customer, $profileType, $request, $data, $profile);

        if (($data['submit_action'] ?? 'draft') === 'submit') {
            return redirect()->route('customer.dashboard')->with('status', 'Your profile changes have been submitted for administrator approval. The currently published version remains visible until approval.');
        }

        $route = $profileType === Profile::TYPE_COMPANY ? 'customer.company-profile-update' : 'customer.personal-profile-update';
        return redirect()->route($route, ['profile' => $profile->id])->with('status', 'Your profile draft has been saved. Changes are not public until administrator approval.');
    }

    private function rules(string $profileType, ?Profile $profile = null): array
    {
        $slugRules = ['nullable', 'string', 'max:190', 'regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/'];
        $slugRules[] = $profile ? Rule::unique('profiles', 'slug')->ignore($profile->id) : Rule::unique('profiles', 'slug');

        $rules = [
            'submit_action' => ['required', Rule::in(['draft', 'submit'])],
            'proposed_slug' => $slugRules,
            'change_summary' => ['nullable', 'string', 'max:2000'],
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
                'career_timeline' => ['nullable', 'array', 'max:40'],
                'career_timeline.*.year' => ['nullable', 'string', 'max:60'],
                'career_timeline.*.title' => ['nullable', 'string', 'max:220'],
                'career_timeline.*.organization' => ['nullable', 'string', 'max:220'],
                'career_timeline.*.location' => ['nullable', 'string', 'max:180'],
                'career_timeline.*.description' => ['nullable', 'string', 'max:10000'],
                'affiliations' => ['nullable', 'array', 'max:40'],
                'affiliations.*.company' => ['nullable', 'string', 'max:220'],
                'affiliations.*.position' => ['nullable', 'string', 'max:220'],
                'affiliations.*.period' => ['nullable', 'string', 'max:100'],
                'affiliations.*.company_url' => ['nullable', 'url', 'max:500'],
                'affiliations.*.logo_url' => ['nullable', 'url', 'max:500'],
                'affiliations.*.description' => ['nullable', 'string', 'max:10000'],
                'education_items' => ['nullable', 'array', 'max:30'],
                'education_items.*.institution' => ['nullable', 'string', 'max:220'],
                'education_items.*.degree' => ['nullable', 'string', 'max:220'],
                'education_items.*.field' => ['nullable', 'string', 'max:220'],
                'education_items.*.year' => ['nullable', 'string', 'max:60'],
                'education_items.*.image_url' => ['nullable', 'url', 'max:500'],
                'education_items.*.details' => ['nullable', 'string', 'max:10000'],
                'achievement_items' => ['nullable', 'array', 'max:40'],
                'achievement_items.*.title' => ['nullable', 'string', 'max:220'],
                'achievement_items.*.issuer' => ['nullable', 'string', 'max:220'],
                'achievement_items.*.year' => ['nullable', 'string', 'max:60'],
                'achievement_items.*.url' => ['nullable', 'url', 'max:500'],
                'achievement_items.*.description' => ['nullable', 'string', 'max:10000'],
                'achievement_items.*.icon' => ['nullable', 'string', 'max:100'],
                'media_items' => ['nullable', 'array', 'max:40'],
                'media_items.*.title' => ['nullable', 'string', 'max:255'],
                'media_items.*.publisher' => ['nullable', 'string', 'max:180'],
                'media_items.*.url' => ['nullable', 'url', 'max:500'],
                'media_items.*.thumbnail_url' => ['nullable', 'url', 'max:500'],
                'media_items.*.date' => ['nullable', 'date'],
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
            'values_culture' => ['nullable', 'string', 'max:10000'],
            'highlights' => ['nullable', 'array', 'max:12'],
            'highlights.*.title' => ['nullable', 'string', 'max:120'],
            'highlights.*.description' => ['nullable', 'string', 'max:500'],
            'highlights.*.icon' => ['nullable', 'string', 'max:100'],
            'leadership_items' => ['nullable', 'array', 'max:40'],
            'leadership_items.*.name' => ['nullable', 'string', 'max:220'],
            'leadership_items.*.position' => ['nullable', 'string', 'max:220'],
            'leadership_items.*.profile_url' => ['nullable', 'url', 'max:500'],
            'leadership_items.*.photo_url' => ['nullable', 'url', 'max:500'],
            'leadership_items.*.bio' => ['nullable', 'string', 'max:5000'],
            'service_items' => ['nullable', 'array', 'max:40'],
            'service_items.*.title' => ['nullable', 'string', 'max:220'],
            'service_items.*.icon' => ['nullable', 'string', 'max:100'],
            'service_items.*.description' => ['nullable', 'string', 'max:5000'],
            'milestone_items' => ['nullable', 'array', 'max:40'],
            'milestone_items.*.title' => ['nullable', 'string', 'max:220'],
            'milestone_items.*.year' => ['nullable', 'string', 'max:60'],
            'milestone_items.*.description' => ['nullable', 'string', 'max:10000'],
            'media_items' => ['nullable', 'array', 'max:40'],
            'media_items.*.title' => ['nullable', 'string', 'max:255'],
            'media_items.*.publisher' => ['nullable', 'string', 'max:180'],
            'media_items.*.url' => ['nullable', 'url', 'max:500'],
            'media_items.*.thumbnail_url' => ['nullable', 'url', 'max:500'],
            'media_items.*.date' => ['nullable', 'date'],
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
