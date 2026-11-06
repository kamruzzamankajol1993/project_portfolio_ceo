<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $person = DB::table('profiles')
                ->whereIn('slug', ['nazim-uddin-hridom', 'ahsanul-karim'])
                ->orderByRaw("CASE WHEN slug = 'nazim-uddin-hridom' THEN 0 ELSE 1 END")
                ->first();

            $company = DB::table('profiles')
                ->whereIn('slug', ['growbig-digital', 'technova-ltd'])
                ->orderByRaw("CASE WHEN slug = 'growbig-digital' THEN 0 ELSE 1 END")
                ->first();

            if (!$person || !$company || !$person->published_version_id || !$company->published_version_id) {
                return;
            }

            $personVersionId = (int) $person->published_version_id;
            $companyVersionId = (int) $company->published_version_id;

            $personVersion = DB::table('profile_versions')->where('id', $personVersionId)->first();
            $companyVersion = DB::table('profile_versions')->where('id', $companyVersionId)->first();

            // Only convert the untouched sample records created by the 40-profile seed migration.
            // This prevents the migration from overwriting a profile that an admin/customer has edited.
            if (
                !$personVersion
                || !$companyVersion
                || $personVersion->change_summary !== 'Initial sample profile'
                || $companyVersion->change_summary !== 'Initial sample company profile'
            ) {
                return;
            }

            $now = now();

            $industry = DB::table('industries')
                ->where('slug', 'digital-marketing-personal-branding')
                ->first();

            $industryId = $industry?->id;

            if (!$industryId) {
                $industryId = DB::table('industries')->insertGetId([
                    'name' => 'Digital Marketing, Personal Branding, Online Reputation Management',
                    'slug' => 'digital-marketing-personal-branding',
                    'description' => 'Digital marketing, personal branding, online visibility and reputation management.',
                    'icon' => 'bi-megaphone',
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->renameProfile($person, 'nazim-uddin-hridom', $now);
            $this->renameProfile($company, 'growbig-digital', $now);

            $personAbout = 'Nazim Uddin Hridom is a well-known entrepreneur, digital marketer and content creator from Bangladesh. He is the co-founder and Acting CEO of GrowBig Digital, a digital marketing agency that helps businesses build their online presence through SEO, content marketing and digital strategy. With years of experience in online marketing, he has worked with clients across different countries and industries, contributing to their growth and success.';

            $personShortBio = 'Nazim Uddin Hridom is a Bangladeshi entrepreneur, digital marketer and content creator. He is the co-founder and Acting CEO of GrowBig Digital, a digital marketing agency dedicated to helping businesses thrive in the online world.';

            $personEarlyLife = 'Nazim Uddin Hridom was born on 15 June 1996 in Bangladesh. From an early age, he was interested in technology, online business and creative work. His passion for learning and exploring new ideas led him to the world of digital marketing, where he developed the skills and knowledge that later shaped his professional journey.';

            $personCareer = 'Nazim Uddin Hridom began his career working on graphic design and digital marketing projects. Over time, he gained expertise in search engine optimization (SEO), content marketing and online branding.

In 2021, he co-founded GrowBig Digital, an online platform focused on strengthening digital presence and online visibility for individuals and businesses. Under his leadership, GrowBig Digital has expanded its operations and works with a distributed team supporting clients across different regions.

He is also a writer and YouTube content creator, where he shares educational insights and practical guidance on digital marketing, entrepreneurship and personal branding.';

            DB::table('personal_profile_details')
                ->where('profile_version_id', $personVersionId)
                ->update([
                    'full_name' => 'Nazim Uddin Hridom',
                    'headline' => 'Co-founder & Acting CEO of GrowBig Digital',
                    'professional_title' => 'Co-founder & Acting CEO of GrowBig Digital',
                    'current_position' => 'Co-founder & Acting CEO',
                    'company_organisation' => 'GrowBig Digital',
                    'industry_id' => $industryId,
                    'location' => 'Bangladesh',
                    'hometown' => 'Kurigram, Rangpur',
                    'nationality' => 'Bangladesh',
                    'date_of_birth' => null,
                    'occupation' => 'Entrepreneur, Digital Marketer, Content Creator',
                    'public_contact_email' => null,
                    'public_phone' => null,
                    'personal_website' => 'https://growbigdigital.com',
                    'short_bio' => $personShortBio,
                    'about' => $personAbout,
                    'profile_quote' => 'Building digital opportunities for people and businesses around the world.',
                    'early_life' => $personEarlyLife,
                    'career_entrepreneurship' => $personCareer,
                    'education_summary' => 'Rangpur Ideal Institute of Technology, Rangpur.',
                    'awards_publications_recognition' => 'Supporting businesses and entrepreneurs through digital marketing, sharing practical knowledge and creating opportunities to reach audiences in different regions.',
                    'show_public_email' => false,
                    'show_public_phone' => false,
                    'show_date_of_birth' => false,
                    'updated_at' => $now,
                ]);

            $companyAbout = 'GrowBig Digital is a digital services company focused on personal branding, online reputation management and Google presence optimization for individuals and businesses. The company provides strategic digital solutions designed to improve online visibility, build credibility and create long-term growth opportunities in the global digital landscape.';

            $companyHistory = 'GrowBig Digital was founded in 2021 by Nazim Uddin Hridom, with a vision to help individuals and businesses build a stronger and more credible online presence. Since its establishment, the company has focused on ethical, transparent and result-driven digital marketing practices. Over time, GrowBig Digital has expanded its services and worked with clients from different countries and industries, becoming a trusted name in personal branding and digital reputation management.';

            $servicesIntro = 'GrowBig Digital offers a range of digital services tailored to individuals, entrepreneurs, and businesses.';

            DB::table('company_profile_details')
                ->where('profile_version_id', $companyVersionId)
                ->update([
                    'company_name' => 'GrowBig Digital',
                    'tagline' => 'Digital Marketing & Personal Branding',
                    'industry_id' => $industryId,
                    'founded_year' => 2021,
                    'headquarters' => 'Dhaka, Bangladesh',
                    'company_size' => null,
                    'business_type' => 'Digital Services Company',
                    'area_served' => 'Worldwide',
                    'official_website' => 'https://growbigdigital.com',
                    'public_email' => null,
                    'public_phone' => null,
                    'short_description' => 'A full-service digital marketing agency providing SEO, online reputation management, personal branding, content marketing and brand building services to help individuals and businesses grow their online presence.',
                    'overview' => $companyAbout,
                    'history_background' => $companyHistory,
                    'mission' => null,
                    'vision' => null,
                    'parent_company' => null,
                    'show_public_email' => false,
                    'show_public_phone' => false,
                    'updated_at' => $now,
                ]);

            DB::table('profile_versions')->where('id', $personVersionId)->update([
                'proposed_slug' => 'nazim-uddin-hridom',
                'change_summary' => 'Restored original personal profile content as dynamic profile data.',
                'updated_at' => $now,
            ]);

            DB::table('profile_versions')->where('id', $companyVersionId)->update([
                'proposed_slug' => 'growbig-digital',
                'change_summary' => 'Restored original company profile content and multi-item services as dynamic profile data.',
                'updated_at' => $now,
            ]);

            $this->replaceMedia(
                $personVersionId,
                'portrait',
                'website/assets/nazim-founder.jpg',
                'profile_portrait',
                'Nazim Uddin Hridom',
                'Profile portrait'
            );

            $this->replaceMedia(
                $companyVersionId,
                'logo',
                'website/assets/growbig-logo.jpg',
                'company_logo',
                'GrowBig Digital logo',
                'Company logo'
            );

            $this->replaceMedia(
                $companyVersionId,
                'banner',
                'website/assets/company-banner.jpg',
                'company_banner',
                'GrowBig Digital company banner',
                'Company banner'
            );

            $this->replaceSections($personVersionId, [
                [
                    'key' => 'early_life',
                    'title' => 'Early Life',
                    'intro' => $personEarlyLife,
                    'items' => [[
                        'title' => null,
                        'subtitle' => null,
                        'description' => null,
                        'icon' => null,
                        'image_path' => 'website/assets/nazim-early-life.jpg',
                        'url' => null,
                    ]],
                ],
                [
                    'key' => 'career_entrepreneurship',
                    'title' => 'Career & Entrepreneurship',
                    'intro' => $personCareer,
                    'items' => [],
                ],
                [
                    'key' => 'impact',
                    'title' => 'Impact',
                    'intro' => 'A profile of business support, knowledge sharing and global opportunities.',
                    'items' => [
                        [
                            'title' => 'Helping Businesses Grow',
                            'subtitle' => null,
                            'description' => 'Supporting companies and entrepreneurs through digital marketing and online strategy.',
                            'icon' => 'bi-people',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Sharing Knowledge',
                            'subtitle' => null,
                            'description' => 'Creating educational content to help people build skills in digital marketing and entrepreneurship.',
                            'icon' => 'bi-mortarboard',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Global Opportunities',
                            'subtitle' => null,
                            'description' => 'Working with clients from different countries and contributing to the global digital economy.',
                            'icon' => 'bi-globe2',
                            'image_path' => null,
                            'url' => null,
                        ],
                    ],
                ],
            ]);

            $this->replaceSections($companyVersionId, [
                [
                    'key' => 'about',
                    'title' => 'About',
                    'intro' => $companyAbout,
                    'items' => [
                        [
                            'title' => 'Personal Branding',
                            'subtitle' => 'Build your digital identity',
                            'description' => null,
                            'icon' => 'bi-person-fill',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'SEO & Online Visibility',
                            'subtitle' => 'Improve search presence',
                            'description' => null,
                            'icon' => 'bi-search',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Content & Strategy',
                            'subtitle' => 'Create impactful content',
                            'description' => null,
                            'icon' => 'bi-file-earmark-text-fill',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Reputation Management',
                            'subtitle' => 'Control & protect your brand',
                            'description' => null,
                            'icon' => 'bi-shield-check',
                            'image_path' => null,
                            'url' => null,
                        ],
                    ],
                ],
                [
                    'key' => 'history_background',
                    'title' => 'History & Background',
                    'intro' => $companyHistory,
                    'items' => [[
                        'title' => null,
                        'subtitle' => null,
                        'description' => null,
                        'icon' => null,
                        'image_path' => 'website/assets/company-history.jpg',
                        'url' => null,
                    ]],
                ],
                [
                    'key' => 'services_operations',
                    'title' => 'Services & Operations',
                    'intro' => $servicesIntro,
                    'items' => [
                        [
                            'title' => 'Google Knowledge Panel',
                            'subtitle' => null,
                            'description' => 'Creation and optimization for individuals and businesses.',
                            'icon' => 'bi-person-video3',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Reputation Management',
                            'subtitle' => null,
                            'description' => 'Online reputation monitoring and brand protection.',
                            'icon' => 'bi-shield-check',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'SEO & Online Visibility',
                            'subtitle' => null,
                            'description' => 'Search engine optimization for stronger online presence.',
                            'icon' => 'bi-bar-chart-fill',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Content Marketing',
                            'subtitle' => null,
                            'description' => 'Strategic content creation for personal and business growth.',
                            'icon' => 'bi-file-earmark-text-fill',
                            'image_path' => null,
                            'url' => null,
                        ],
                    ],
                ],
                [
                    'key' => 'leadership_founders',
                    'title' => 'Leadership & Founders',
                    'intro' => null,
                    'items' => [],
                ],
                [
                    'key' => 'impact_achievements',
                    'title' => 'Impact & Achievements',
                    'intro' => null,
                    'items' => [
                        [
                            'title' => '100+',
                            'subtitle' => 'Clients Worldwide',
                            'description' => null,
                            'icon' => 'bi-globe2',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Individuals & Brands',
                            'subtitle' => 'Supported',
                            'description' => null,
                            'icon' => 'bi-people-fill',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Stronger Online Presence',
                            'subtitle' => 'for Clients',
                            'description' => null,
                            'icon' => 'bi-graph-up-arrow',
                            'image_path' => null,
                            'url' => null,
                        ],
                        [
                            'title' => 'Trusted Digital Partner',
                            'subtitle' => 'in Multiple Industries',
                            'description' => null,
                            'icon' => 'bi-award',
                            'image_path' => null,
                            'url' => null,
                        ],
                    ],
                ],
            ]);

            $this->replaceLinks($personVersionId, [
                ['website', 'Website', 'https://growbigdigital.com', 'bi-globe2'],
                ['youtube', 'YouTube', 'https://youtube.com/@nazimhridom', 'bi-youtube'],
                ['facebook', 'Facebook', 'https://facebook.com/nazimhridom', 'bi-facebook'],
                ['linkedin', 'LinkedIn', 'https://linkedin.com/in/nazimhridom', 'bi-linkedin'],
                ['instagram', 'Instagram', 'https://instagram.com/nazimhridom', 'bi-instagram'],
            ]);

            $this->replaceLinks($companyVersionId, [
                ['website', 'Official Website', 'https://growbigdigital.com', 'bi-globe2'],
                ['facebook', 'Facebook', 'https://facebook.com/growbigdigital', 'bi-facebook'],
                ['linkedin', 'LinkedIn', 'https://linkedin.com/company/growbigdigital', 'bi-linkedin'],
                ['youtube', 'YouTube', 'https://youtube.com/@growbigdigital', 'bi-youtube'],
                ['instagram', 'Instagram', 'https://instagram.com/growbigdigital', 'bi-instagram'],
                ['x', 'X (Twitter)', 'https://twitter.com/growbigdigital', 'bi-twitter-x'],
            ]);

            $this->replaceReferences($personVersionId, [
                ['GrowBig Digital – Official Website', 'GrowBig Digital', 'https://growbigdigital.com', 'Official company website.'],
            ]);

            $this->replaceReferences($companyVersionId, [
                ['GrowBig Digital – Official Website', 'GrowBig Digital', 'https://growbigdigital.com', 'Official company website.'],
            ]);

            $this->replaceRelationship(
                $personVersionId,
                (int) $company->id,
                'founder',
                'Co-founder & Acting CEO',
                'A full-service digital marketing agency providing SEO, online reputation management, personal branding and brand building services. The company helps businesses, entrepreneurs and professionals build a strong and lasting online presence.'
            );

            $this->replaceRelationship(
                $companyVersionId,
                (int) $person->id,
                'founder',
                'Founder & Acting CEO',
                'Nazim Uddin Hridom is a Bangladeshi entrepreneur, digital marketer and content creator. He founded GrowBig Digital with the goal of helping individuals and businesses build a strong online presence.'
            );

            $this->saveSeo($personVersionId, 'Nazim Uddin Hridom', 'Entrepreneur, digital marketer and content creator from Bangladesh.');
            $this->saveSeo($companyVersionId, 'GrowBig Digital', 'Digital marketing, personal branding, SEO and online reputation management services.');
        });
    }

    private function renameProfile(object $profile, string $newSlug, mixed $now): void
    {
        $slugTaken = DB::table('profiles')
            ->where('slug', $newSlug)
            ->where('id', '!=', $profile->id)
            ->exists();

        if ($slugTaken) {
            return;
        }

        DB::table('profiles')->where('id', $profile->id)->update([
            'slug' => $newSlug,
            'status' => 'published',
            'is_verified' => true,
            'updated_at' => $now,
        ]);
    }

    private function replaceMedia(
        int $versionId,
        string $role,
        string $path,
        string $collection,
        string $altText,
        string $caption
    ): void {
        $existingLink = DB::table('profile_version_media')
            ->where('profile_version_id', $versionId)
            ->where('role', $role)
            ->first();

        $now = now();

        if ($existingLink) {
            DB::table('media_assets')->where('id', $existingLink->media_asset_id)->update([
                'collection' => $collection,
                'disk' => 'public',
                'path' => $path,
                'original_name' => basename($path),
                'mime_type' => 'image/jpeg',
                'alt_text' => $altText,
                'caption' => $caption,
                'updated_at' => $now,
            ]);

            return;
        }

        $mediaId = DB::table('media_assets')->insertGetId([
            'collection' => $collection,
            'disk' => 'public',
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => 'image/jpeg',
            'alt_text' => $altText,
            'caption' => $caption,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('profile_version_media')->insert([
            'profile_version_id' => $versionId,
            'media_asset_id' => $mediaId,
            'role' => $role,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function replaceSections(int $versionId, array $sections): void
    {
        DB::table('profile_sections')->where('profile_version_id', $versionId)->delete();

        $now = now();

        foreach ($sections as $sectionIndex => $sectionData) {
            $sectionId = DB::table('profile_sections')->insertGetId([
                'profile_version_id' => $versionId,
                'section_key' => $sectionData['key'],
                'title' => $sectionData['title'],
                'intro' => $sectionData['intro'],
                'section_type' => 'content',
                'layout' => 'default',
                'settings' => json_encode([]),
                'is_visible' => true,
                'sort_order' => $sectionIndex,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($sectionData['items'] as $itemIndex => $item) {
                DB::table('profile_section_items')->insert([
                    'section_id' => $sectionId,
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'description' => $item['description'],
                    'icon' => $item['icon'],
                    'image_path' => $item['image_path'],
                    'url' => $item['url'],
                    'metadata' => json_encode([]),
                    'is_visible' => true,
                    'sort_order' => $itemIndex,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function replaceLinks(int $versionId, array $links): void
    {
        DB::table('profile_version_links')->where('profile_version_id', $versionId)->delete();

        $now = now();

        foreach ($links as $index => [$type, $label, $url, $icon]) {
            DB::table('profile_version_links')->insert([
                'profile_version_id' => $versionId,
                'link_type' => $type,
                'label' => $label,
                'url' => $url,
                'icon' => $icon,
                'is_visible' => true,
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function replaceReferences(int $versionId, array $references): void
    {
        DB::table('profile_references')->where('profile_version_id', $versionId)->delete();

        $now = now();

        foreach ($references as $index => [$title, $publisher, $url, $description]) {
            DB::table('profile_references')->insert([
                'profile_version_id' => $versionId,
                'title' => $title,
                'publisher' => $publisher,
                'url' => $url,
                'source_date' => null,
                'description' => $description,
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function replaceRelationship(
        int $versionId,
        int $relatedProfileId,
        string $type,
        string $position,
        string $description
    ): void {
        DB::table('profile_version_relationships')
            ->where('profile_version_id', $versionId)
            ->delete();

        DB::table('profile_version_relationships')->insert([
            'profile_version_id' => $versionId,
            'related_profile_id' => $relatedProfileId,
            'relation_type' => $type,
            'position' => $position,
            'description' => $description,
            'is_visible' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function saveSeo(int $versionId, string $name, string $description): void
    {
        $now = now();

        DB::table('profile_version_seo')->updateOrInsert(
            ['profile_version_id' => $versionId],
            [
                'meta_title' => $name . ' | EntrepreneurWiki',
                'meta_description' => $description,
                'meta_keywords' => Str::lower($name . ', entrepreneur, company profile'),
                'robots' => 'index,follow',
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        // This content migration intentionally keeps the published profiles intact on rollback.
        // Reverting the presentation data would discard any subsequent customer/admin edits.
    }
};
