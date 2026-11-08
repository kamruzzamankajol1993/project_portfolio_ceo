<?php

namespace App\Services;

use App\Models\CompanyProfileDetail;
use App\Models\Customer;
use App\Models\MediaAsset;
use App\Models\PersonalProfileDetail;
use App\Models\Profile;
use App\Models\ProfileReference;
use App\Models\ProfileReviewLog;
use App\Models\ProfileSection;
use App\Models\ProfileVersion;
use App\Models\ProfileVersionLink;
use App\Models\ProfileVersionMedia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProfileWorkflowService
{
    private const RESERVED_SLUGS = [
        'profiles', 'company-profile', 'personal-profile', 'about-us', 'contact-us', 'admin', 'blog',
        'home', 'ajax', 'search', 'login', 'register', 'logout', 'forgot-password', 'reset-password',
        'customer-register', 'customer-login', 'customer-dashboard', 'customer-profile',
        'customer-company-profile-update', 'customer-personal-profile-update', 'customer-profile-requests',
        'customer-email-verification-notice', 'terms-and-conditions', 'privacy-policy', 'robots.txt', 'sitemap.xml',
    ];
    private const PERSONAL_FIELDS = [
        'full_name', 'headline', 'professional_title', 'current_position', 'company_organisation',
        'industry_id', 'location', 'hometown', 'nationality', 'date_of_birth', 'occupation',
        'public_contact_email', 'public_phone', 'personal_website', 'short_bio', 'about',
        'profile_quote', 'early_life', 'career_entrepreneurship', 'education_summary',
        'awards_publications_recognition', 'show_public_email', 'show_public_phone', 'show_date_of_birth',
        'career_timeline', 'affiliations', 'education_items', 'achievement_items', 'media_items',
    ];

    private const COMPANY_FIELDS = [
        'company_name', 'tagline', 'industry_id', 'founded_year', 'headquarters', 'company_size',
        'business_type', 'area_served', 'official_website', 'public_email', 'public_phone',
        'short_description', 'overview', 'history_background', 'mission', 'vision', 'parent_company',
        'show_public_email', 'show_public_phone', 'accuracy_cta_enabled', 'accuracy_cta_title',
        'accuracy_cta_description', 'accuracy_cta_button_label', 'accuracy_cta_button_url',
        'accuracy_cta_secondary_label', 'accuracy_cta_secondary_url', 'values_culture',
        'highlights', 'leadership_items', 'service_items', 'milestone_items', 'media_items',
    ];

    public function saveCustomerProfile(
        Customer $customer,
        string $profileType,
        Request $request,
        array $data,
        ?Profile $profile = null
    ): ProfileVersion {
        return DB::transaction(function () use ($customer, $profileType, $request, $data, $profile): ProfileVersion {
            if ($profile) {
                $profile = Profile::query()->lockForUpdate()->findOrFail($profile->id);
                $hasAccess = $profile->customerAccess()
                    ->where('customer_id', $customer->id)
                    ->whereIn('access_level', ['owner', 'editor'])
                    ->exists();
                abort_unless($hasAccess && $profile->profile_type === $profileType, 403);
            } else {
                abort_unless(
                    $customer->profileTypeAccess()->active()->where('profile_type', $profileType)->exists(),
                    403,
                    'Your profile creation access must be approved before creating this profile.'
                );

                $name = $this->profileName($profileType, $data);
                $slug = $this->uniqueSlug(filled($data['proposed_slug'] ?? null) ? $data['proposed_slug'] : $name);
                $profile = Profile::create([
                    'profile_type' => $profileType,
                    'slug' => $slug,
                    'status' => 'draft',
                    'created_by_customer_id' => $customer->id,
                ]);

                $profile->customerAccess()->create([
                    'customer_id' => $customer->id,
                    'access_level' => 'owner',
                    'is_primary' => true,
                ]);
            }

            $version = $this->editableVersion($profile, 'customer', $customer->id);
            $this->persistVersionContent($version, $profileType, $request, $data, $customer, null);

            if (($data['submit_action'] ?? 'draft') === 'submit') {
                $this->submitVersion($version, $customer);
            } else {
                $version->forceFill(['review_status' => ProfileVersion::DRAFT])->save();
                $this->log($version, 'draft_saved', null, $customer, 'Customer saved a profile draft.');
            }

            return $version->fresh(['profile']);
        });
    }

    public function saveAdminProfile(User $admin, Request $request, array $data, ?Profile $profile = null): ProfileVersion
    {
        return DB::transaction(function () use ($admin, $request, $data, $profile): ProfileVersion {
            $profileType = $data['profile_type'];
            $customerId = filled($data['customer_id'] ?? null) ? $data['customer_id'] : null;
            $customer = $customerId ? Customer::query()->findOrFail($customerId) : null;

            if ($profile) {
                $profile = Profile::query()->lockForUpdate()->findOrFail($profile->id);
                abort_unless($profile->profile_type === $profileType, 422, 'The profile type cannot be changed after creation.');
            } else {
                $name = $this->profileName($profileType, $data);
                $profile = Profile::create([
                    'profile_type' => $profileType,
                    'slug' => $this->uniqueSlug(filled($data['proposed_slug'] ?? null) ? $data['proposed_slug'] : $name),
                    'status' => 'draft',
                    'created_by_admin_id' => $admin->id,
                ]);
            }

            $version = $this->editableVersion($profile, 'admin', $admin->id);
            $this->persistVersionContent($version, $profileType, $request, $data, null, $admin);

            if ($customer) {
                $profile->customerAccess()->where('is_primary', true)->where('customer_id', '!=', $customer->id)->delete();
                $profile->customerAccess()->updateOrCreate(
                    ['customer_id' => $customer->id],
                    [
                        'access_level' => 'owner',
                        'is_primary' => true,
                        'granted_by_admin_id' => $admin->id,
                    ]
                );
            } elseif (array_key_exists('customer_id', $data)) {
                $profile->customerAccess()->where('is_primary', true)->delete();
            }

            if (!empty($data['is_verified'])) {
                $profile->forceFill(['is_verified' => true])->save();
            } else {
                $profile->forceFill(['is_verified' => false])->save();
            }

            if (!empty($data['publish_now'])) {
                $this->publishVersion($version, $admin, 'Published by administrator.');
            } else {
                $version->forceFill([
                    'review_status' => ProfileVersion::DRAFT,
                    'reviewed_by_admin_id' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                ])->save();
                $this->log($version, 'admin_draft_saved', $admin, null, 'Administrator saved a profile draft.');
            }

            return $version->fresh(['profile']);
        });
    }

    public function editableVersion(Profile $profile, string $actorType, int $actorId): ProfileVersion
    {
        $latest = $profile->versions()->orderByDesc('version_number')->first();

        if ($latest?->review_status === ProfileVersion::PENDING) {
            throw new HttpException(422, 'This profile version is waiting for administrator review.');
        }

        if ($latest && in_array($latest->review_status, [ProfileVersion::DRAFT, 'changes_requested', ProfileVersion::REJECTED], true)) {
            return $latest;
        }

        $source = $profile->publishedVersion()
            ->with(['personalDetails', 'companyDetails', 'sections.items', 'links', 'references', 'mediaLinks', 'relationships', 'seo'])
            ->first();

        $version = $profile->versions()->create([
            'version_number' => ((int) $profile->versions()->max('version_number')) + 1,
            'proposed_slug' => $profile->slug,
            'review_status' => ProfileVersion::DRAFT,
            'created_by_admin_id' => $actorType === 'admin' ? $actorId : null,
            'created_by_customer_id' => $actorType === 'customer' ? $actorId : null,
        ]);

        if ($source) {
            $this->cloneVersionContent($source, $version);
        }

        return $version;
    }

    public function submitVersion(ProfileVersion $version, Customer $customer): void
    {
        $wasReturnedForChanges = $version->reviewLogs()->where('action', 'changes_requested')->exists();
        $version->forceFill([
            'review_status' => ProfileVersion::PENDING,
            'submitted_by_customer_id' => $customer->id,
            'submitted_at' => now(),
            'review_note' => null,
            'reviewed_by_admin_id' => null,
            'reviewed_at' => null,
        ])->save();

        $this->log(
            $version,
            $wasReturnedForChanges ? 'customer_resubmitted' : 'submitted',
            null,
            $customer,
            $wasReturnedForChanges
                ? 'Customer resubmitted the corrected profile.'
                : 'Customer submitted the profile for administrator review.'
        );
    }

    public function publishVersion(ProfileVersion $version, User $admin, ?string $note = null): void
    {
        $profile = $version->profile()->lockForUpdate()->firstOrFail();
        $slug = $this->uniqueSlug($version->proposed_slug ?: $profile->slug, $profile->id);

        $version->forceFill([
            'review_status' => ProfileVersion::APPROVED,
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        $profile->forceFill([
            'slug' => $slug,
            'status' => 'published',
            'published_version_id' => $version->id,
            'published_at' => now(),
        ])->save();

        $this->log($version, 'approved', $admin, null, $note ?: 'Administrator approved this profile version.');
        $this->log($version, 'published', $admin, null, 'This profile version is now public.');
    }

    public function requestChanges(ProfileVersion $version, User $admin, string $note): void
    {
        $version->forceFill([
            'review_status' => 'changes_requested',
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        $this->log($version, 'changes_requested', $admin, null, $note);
    }

    public function rejectVersion(ProfileVersion $version, User $admin, ?string $note = null): void
    {
        $version->forceFill([
            'review_status' => ProfileVersion::REJECTED,
            'reviewed_by_admin_id' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ])->save();

        $this->log($version, 'rejected', $admin, null, $note ?: 'Administrator declined this submission.');
    }

    private function persistVersionContent(
        ProfileVersion $version,
        string $profileType,
        Request $request,
        array $data,
        ?Customer $customer,
        ?User $admin
    ): void {
        $fields = $profileType === Profile::TYPE_PERSONAL ? self::PERSONAL_FIELDS : self::COMPANY_FIELDS;
        $attributes = Arr::only($data, $fields);

        // Admin forms submit an explicit marker for each repeatable group. This
        // lets an administrator remove every row and intentionally clear the
        // corresponding JSON field, while leaving unrelated legacy data intact.
        $structuredArrayFields = $profileType === Profile::TYPE_PERSONAL
            ? ['career_timeline', 'affiliations', 'education_items', 'achievement_items', 'media_items']
            : ['highlights', 'leadership_items', 'service_items', 'milestone_items', 'media_items'];

        foreach ($structuredArrayFields as $fieldName) {
            if ($request->has($fieldName . '_submitted')) {
                $attributes[$fieldName] = collect($data[$fieldName] ?? [])
                    ->filter(fn ($item) => is_array($item) && collect($item)->contains(fn ($value) => filled($value)))
                    ->values()
                    ->all();
            }
        }

        foreach (['show_public_email', 'show_public_phone', 'show_date_of_birth', 'accuracy_cta_enabled'] as $booleanField) {
            if (in_array($booleanField, $fields, true)) {
                $attributes[$booleanField] = (bool) ($data[$booleanField] ?? false);
            }
        }

        if ($profileType === Profile::TYPE_PERSONAL) {
            PersonalProfileDetail::updateOrCreate(
                ['profile_version_id' => $version->id],
                array_merge($attributes, ['profile_version_id' => $version->id])
            );
        } else {
            CompanyProfileDetail::updateOrCreate(
                ['profile_version_id' => $version->id],
                array_merge($attributes, ['profile_version_id' => $version->id])
            );
        }

        $version->forceFill([
            'proposed_slug' => $this->uniqueSlug(filled($data['proposed_slug'] ?? null) ? $data['proposed_slug'] : $this->profileName($profileType, $data), $version->profile_id),
            'change_summary' => $data['change_summary'] ?? null,
        ])->save();

        $version->links()->delete();
        foreach (($data['links'] ?? []) as $index => $link) {
            if (blank($link['url'] ?? null)) {
                continue;
            }
            $version->links()->create([
                'link_type' => $link['link_type'] ?? 'website',
                'label' => $link['label'] ?? null,
                'url' => $link['url'],
                'icon' => $link['icon'] ?? null,
                'is_visible' => true,
                'sort_order' => $index,
            ]);
        }

        $version->references()->delete();
        foreach (($data['references'] ?? []) as $index => $reference) {
            if (blank($reference['title'] ?? null)) {
                continue;
            }
            $version->references()->create([
                'title' => $reference['title'],
                'publisher' => $reference['publisher'] ?? null,
                'url' => $reference['url'] ?? null,
                'source_date' => $reference['source_date'] ?? null,
                'description' => $reference['description'] ?? null,
                'sort_order' => $index,
            ]);
        }

        $version->sections()->delete();

        // Admin-side profile editing keeps its existing form unchanged. If an
        // admin saves a version without these customer-editor fields, rebuild
        // the review mirrors from the already stored JSON detail columns.
        $editorialData = $data;
        $storedDetails = $profileType === Profile::TYPE_PERSONAL
            ? PersonalProfileDetail::query()->where('profile_version_id', $version->id)->first()
            : CompanyProfileDetail::query()->where('profile_version_id', $version->id)->first();
        $editorialFieldNames = $profileType === Profile::TYPE_PERSONAL
            ? ['career_timeline', 'affiliations', 'education_items', 'achievement_items', 'media_items']
            : ['highlights', 'leadership_items', 'service_items', 'milestone_items', 'media_items', 'values_culture', 'accuracy_cta_enabled', 'accuracy_cta_title', 'accuracy_cta_description', 'accuracy_cta_button_label', 'accuracy_cta_button_url'];
        foreach ($editorialFieldNames as $fieldName) {
            if (!array_key_exists($fieldName, $editorialData)) {
                $editorialData[$fieldName] = $storedDetails?->{$fieldName} ?? [];
            }
        }

        // Mirror the structured editor fields into the existing section/item
        // structure as a read-only editorial-review representation. This keeps
        // the admin review screen unchanged while making every submitted field
        // visible to the reviewer through its existing section renderer.
        $editorialGroups = $profileType === Profile::TYPE_PERSONAL ? [
            [
                'key' => '_editor_career_timeline', 'title' => 'Career timeline', 'layout' => 'timeline',
                'items' => array_map(fn ($item) => [
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['year'] ?? null,
                    'description' => trim(implode("\n", array_filter([
                        $item['organization'] ?? null,
                        $item['location'] ?? null,
                        $item['description'] ?? null,
                    ]))),
                ], $editorialData['career_timeline'] ?? []),
            ],
            [
                'key' => '_editor_companies', 'title' => 'Companies & affiliations', 'layout' => 'list',
                'items' => array_map(fn ($item) => [
                    'title' => $item['company'] ?? null,
                    'subtitle' => trim(implode(' · ', array_filter([$item['position'] ?? null, $item['period'] ?? null]))),
                    'description' => $item['description'] ?? null,
                    'url' => $item['company_url'] ?? null,
                ], $editorialData['affiliations'] ?? []),
            ],
            [
                'key' => '_editor_education', 'title' => 'Education & qualifications', 'layout' => 'list',
                'items' => array_map(fn ($item) => [
                    'title' => $item['institution'] ?? null,
                    'subtitle' => trim(implode(' · ', array_filter([$item['degree'] ?? null, $item['field'] ?? null, $item['year'] ?? null]))),
                    'description' => $item['details'] ?? null,
                ], $editorialData['education_items'] ?? []),
            ],
            [
                'key' => '_editor_achievements', 'title' => 'Awards, publications & recognition', 'layout' => 'list',
                'items' => array_map(fn ($item) => [
                    'title' => $item['title'] ?? null,
                    'subtitle' => trim(implode(' · ', array_filter([$item['issuer'] ?? null, $item['year'] ?? null]))),
                    'description' => $item['description'] ?? null,
                    'url' => $item['url'] ?? null,
                ], $editorialData['achievement_items'] ?? []),
            ],
            [
                'key' => '_editor_media', 'title' => 'Media, interviews & gallery', 'layout' => 'grid',
                'items' => array_map(fn ($item) => [
                    'title' => $item['title'] ?? null,
                    'subtitle' => trim(implode(' · ', array_filter([$item['publisher'] ?? null, $item['date'] ?? null]))),
                    'url' => $item['url'] ?? null,
                ], $editorialData['media_items'] ?? []),
            ],
        ] : [
            [
                'key' => '_editor_overview_highlights', 'title' => 'Company highlights', 'layout' => 'grid',
                'items' => array_merge(
                    array_map(fn ($item) => [
                        'title' => $item['title'] ?? null,
                        'description' => $item['description'] ?? null,
                        'icon' => $item['icon'] ?? null,
                    ], $editorialData['highlights'] ?? []),
                    filled($editorialData['values_culture'] ?? null) ? [[
                        'title' => 'Values & culture',
                        'description' => $editorialData['values_culture'],
                    ]] : [],
                    !empty($editorialData['accuracy_cta_enabled']) && filled($editorialData['accuracy_cta_title'] ?? null) ? [[
                        'title' => $editorialData['accuracy_cta_title'],
                        'subtitle' => $editorialData['accuracy_cta_button_label'] ?? null,
                        'description' => $editorialData['accuracy_cta_description'] ?? null,
                        'url' => $editorialData['accuracy_cta_button_url'] ?? null,
                    ]] : []
                ),
            ],
            [
                'key' => '_editor_leadership', 'title' => 'Leadership team', 'layout' => 'grid',
                'items' => array_map(fn ($item) => [
                    'title' => $item['name'] ?? null,
                    'subtitle' => $item['position'] ?? null,
                    'description' => $item['bio'] ?? null,
                    'url' => $item['profile_url'] ?? null,
                ], $editorialData['leadership_items'] ?? []),
            ],
            [
                'key' => '_editor_services', 'title' => 'Products & services', 'layout' => 'grid',
                'items' => array_map(fn ($item) => [
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['icon'] ?? null,
                    'description' => $item['description'] ?? null,
                    'icon' => $item['icon'] ?? null,
                ], $editorialData['service_items'] ?? []),
            ],
            [
                'key' => '_editor_achievements', 'title' => 'Achievements & milestones', 'layout' => 'timeline',
                'items' => array_map(fn ($item) => [
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['year'] ?? null,
                    'description' => $item['description'] ?? null,
                ], $editorialData['milestone_items'] ?? []),
            ],
            [
                'key' => '_editor_media', 'title' => 'Media & news', 'layout' => 'grid',
                'items' => array_map(fn ($item) => [
                    'title' => $item['title'] ?? null,
                    'subtitle' => trim(implode(' · ', array_filter([$item['publisher'] ?? null, $item['date'] ?? null]))),
                    'url' => $item['url'] ?? null,
                ], $editorialData['media_items'] ?? []),
            ],
        ];

        foreach ($editorialGroups as $group) {
            $editorialItems = collect($group['items'] ?? [])
                ->filter(fn ($item) => collect($item)->contains(fn ($value) => filled($value)))
                ->values();

            if ($editorialItems->isEmpty()) {
                continue;
            }

            $editorialSection = $version->sections()->create([
                'section_key' => $group['key'],
                'title' => $group['title'],
                'intro' => null,
                'section_type' => 'content',
                'layout' => $group['layout'] ?? 'default',
                'settings' => [],
                'is_visible' => true,
                'sort_order' => $version->sections()->count(),
            ]);

            foreach ($editorialItems as $itemIndex => $item) {
                $editorialSection->items()->create([
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['subtitle'] ?? null,
                    'description' => $item['description'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'image_path' => null,
                    'url' => $item['url'] ?? null,
                    'metadata' => [],
                    'is_visible' => true,
                    'sort_order' => $itemIndex,
                ]);
            }
        }

        foreach (($data['sections'] ?? []) as $sectionIndex => $sectionData) {
            if (blank($sectionData['title'] ?? null)) {
                continue;
            }
            $sectionKeyBase = Str::limit(Str::slug($sectionData['title'], '_') ?: 'section_' . ($sectionIndex + 1), 80, '');
            $sectionKey = $sectionKeyBase;
            $sectionKeySuffix = 2;
            while ($version->sections()->where('section_key', $sectionKey)->exists()) {
                $sectionKey = $sectionKeyBase . '_' . $sectionKeySuffix++;
            }
            $section = $version->sections()->create([
                'section_key' => $sectionKey,
                'title' => $sectionData['title'],
                'intro' => $sectionData['intro'] ?? null,
                'section_type' => $sectionData['section_type'] ?? 'content',
                'layout' => $sectionData['layout'] ?? 'default',
                'settings' => [],
                'is_visible' => (bool) ($sectionData['is_visible'] ?? true),
                'sort_order' => $sectionIndex,
            ]);

            foreach (($sectionData['items'] ?? []) as $itemIndex => $item) {
                $itemImage = $request->file("sections.{$sectionIndex}.items.{$itemIndex}.image");
                $imagePath = $item['image_path'] ?? null;

                if ($itemImage) {
                    $imagePath = app(PublicImageService::class)->upload($itemImage, 'profiles', 1200);
                }

                if (
                    blank($item['title'] ?? null)
                    && blank($item['description'] ?? null)
                    && blank($item['url'] ?? null)
                    && blank($imagePath)
                ) {
                    continue;
                }

                $section->items()->create([
                    'title' => $item['title'] ?? null,
                    'subtitle' => $item['subtitle'] ?? null,
                    'description' => $item['description'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'image_path' => $imagePath,
                    'url' => $item['url'] ?? null,
                    'metadata' => [],
                    'is_visible' => (bool) ($item['is_visible'] ?? true),
                    'sort_order' => $itemIndex,
                ]);
            }
        }

        if (array_key_exists('relationships', $data)) {
            $version->relationships()->delete();
            $seenRelationships = [];
            foreach (($data['relationships'] ?? []) as $relationshipIndex => $relationship) {
                $relatedProfileId = $relationship['related_profile_id'] ?? null;
                if (blank($relatedProfileId)) {
                    continue;
                }
                abort_if((int) $relatedProfileId === (int) $version->profile_id, 422, 'A profile cannot be related to itself.');
                abort_unless(\App\Models\Profile::published()->whereKey($relatedProfileId)->exists(), 422, 'Related profiles must already be published.');
                $relationshipType = $relationship['relation_type'] ?? 'related';
                $relationshipKey = $relatedProfileId . ':' . $relationshipType;
                if (isset($seenRelationships[$relationshipKey])) {
                    continue;
                }
                $seenRelationships[$relationshipKey] = true;
                $version->relationships()->create([
                    'related_profile_id' => $relatedProfileId,
                    'relation_type' => $relationshipType,
                    'position' => $relationship['position'] ?? null,
                    'description' => $relationship['description'] ?? null,
                    'is_visible' => true,
                    'sort_order' => $relationshipIndex,
                ]);
            }
        }

        $seoFields = Arr::only($data, ['meta_title', 'meta_description', 'meta_keywords', 'robots', 'canonical_url', 'og_title', 'og_description', 'og_image', 'twitter_title', 'twitter_description', 'twitter_image']);
        if ($seoFields) {
            $version->seo()->updateOrCreate([], array_merge(['robots' => 'index,follow'], $seoFields));
        }

        $imageService = app(PublicImageService::class);
        $imageFields = $profileType === Profile::TYPE_PERSONAL
            ? ['profile_photo' => 'portrait']
            : ['company_logo' => 'logo', 'company_banner' => 'banner'];

        foreach ($imageFields as $inputName => $role) {
            if ($request->hasFile($inputName)) {
                $version->mediaLinks()->where('role', $role)->delete();
                $path = $imageService->upload($request->file($inputName), 'profiles', $role === 'banner' ? 1800 : 1000);
                $asset = MediaAsset::create([
                    'uploaded_by_customer_id' => $customer?->id,
                    'uploaded_by_admin_id' => $admin?->id,
                    'collection' => $role,
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $request->file($inputName)->getClientOriginalName(),
                    'mime_type' => $request->file($inputName)->getMimeType(),
                    'size_bytes' => $request->file($inputName)->getSize(),
                ]);
                $version->mediaLinks()->create(['media_asset_id' => $asset->id, 'role' => $role, 'sort_order' => 0]);
            }
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery', []) as $index => $file) {
                if (!$file) {
                    continue;
                }
                $path = $imageService->upload($file, 'profiles', 1400);
                $asset = MediaAsset::create([
                    'uploaded_by_customer_id' => $customer?->id,
                    'uploaded_by_admin_id' => $admin?->id,
                    'collection' => 'gallery',
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
                $version->mediaLinks()->create(['media_asset_id' => $asset->id, 'role' => 'gallery', 'sort_order' => $index]);
            }
        }
    }

    private function cloneVersionContent(ProfileVersion $source, ProfileVersion $target): void
    {
        foreach (['personalDetails', 'companyDetails'] as $relation) {
            if ($source->{$relation}) {
                $copy = $source->{$relation}->replicate();
                $copy->profile_version_id = $target->id;
                $copy->save();
            }
        }

        foreach ($source->sections as $section) {
            $copy = $section->replicate();
            $copy->profile_version_id = $target->id;
            $copy->save();
            foreach ($section->items as $item) {
                $itemCopy = $item->replicate();
                $itemCopy->section_id = $copy->id;
                $itemCopy->save();
            }
        }

        foreach ($source->links as $link) {
            $copy = $link->replicate();
            $copy->profile_version_id = $target->id;
            $copy->save();
        }

        foreach ($source->references as $reference) {
            $copy = $reference->replicate();
            $copy->profile_version_id = $target->id;
            $copy->save();
        }

        foreach ($source->mediaLinks as $mediaLink) {
            $copy = $mediaLink->replicate();
            $copy->profile_version_id = $target->id;
            $copy->save();
        }

        foreach ($source->relationships as $relationship) {
            $copy = $relationship->replicate();
            $copy->profile_version_id = $target->id;
            $copy->save();
        }

        if ($source->seo) {
            $copy = $source->seo->replicate();
            $copy->profile_version_id = $target->id;
            $copy->save();
        }
    }

    private function log(ProfileVersion $version, string $action, ?User $admin, ?Customer $customer, ?string $note): void
    {
        ProfileReviewLog::create([
            'profile_version_id' => $version->id,
            'action' => $action,
            'admin_id' => $admin?->id,
            'customer_id' => $customer?->id,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    private function profileName(string $profileType, array $data): string
    {
        return (string) ($profileType === Profile::TYPE_PERSONAL
            ? ($data['full_name'] ?? 'Profile')
            : ($data['company_name'] ?? 'Company'));
    }

    private function uniqueSlug(string $value, ?int $ignoreProfileId = null): string
    {
        $base = Str::slug($value) ?: 'profile';
        $slug = Str::limit($base, 180, '');
        $counter = 2;

        while (in_array($slug, self::RESERVED_SLUGS, true) || Profile::query()
            ->where('slug', $slug)
            ->when($ignoreProfileId, fn ($query) => $query->where('id', '!=', $ignoreProfileId))
            ->exists()) {
            $suffix = '-' . $counter++;
            $slug = Str::limit($base, 180 - strlen($suffix), '') . $suffix;
        }

        return $slug;
    }
}
