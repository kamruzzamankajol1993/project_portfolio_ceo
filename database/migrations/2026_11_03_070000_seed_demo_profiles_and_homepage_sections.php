<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $people = [
            ['Ahsanul Karim','Technology Entrepreneur','Dhaka, Bangladesh','Technology'],
            ['Farzana Rahman','Chief Executive Officer','Dhaka, Bangladesh','Technology'],
            ['Mohammad Imran','Agritech Founder','Chattogram, Bangladesh','Agriculture'],
            ['Nusrat Jahan','Social Entrepreneur','Dhaka, Bangladesh','Social Impact'],
            ['Tanjim Hasan','Fintech Co-Founder','Dhaka, Bangladesh','Finance'],
            ['Sadia Akter','Education Innovator','Sylhet, Bangladesh','Education'],
            ['Shafayet Ahmed','Digital Marketing Strategist','Dhaka, Bangladesh','Marketing'],
            ['Rubaiyat Chowdhury','Startup Advisor','Dhaka, Bangladesh','Business Services'],
            ['Mitu Akhter','Women Entrepreneur','Rajshahi, Bangladesh','Retail'],
            ['Arifur Rahman','Export Business Owner','Chattogram, Bangladesh','Trade'],
            ['Ripa Islam','Interior Designer','Dhaka, Bangladesh','Design'],
            ['Jahid Hasan','IT Consultant','Dhaka, Bangladesh','Technology'],
            ['Sumaiya Sultana','Health Entrepreneur','Khulna, Bangladesh','Healthcare'],
            ['Rakibul Islam','E-commerce Founder','Dhaka, Bangladesh','E-commerce'],
            ['Amanda Chowdhury','Creative Director','Dhaka, Bangladesh','Creative'],
            ['Nazmul Hasan','Agri-Tech Innovator','Mymensingh, Bangladesh','Agriculture'],
            ['Lamiya Akter','Food Entrepreneur','Dhaka, Bangladesh','Food'],
            ['Emon Hossain','Logistics Specialist','Narayanganj, Bangladesh','Logistics'],
            ['Sharmin Akter','HR & People Leader','Dhaka, Bangladesh','Human Resources'],
            ['Hasan Mahmud','Young Entrepreneur','Cumilla, Bangladesh','Business'],
            ];
            $companies = [
            ['TechNova Ltd.','IT & Software','Dhaka, Bangladesh','Software development and digital transformation solutions for growing organisations.'],
            ['AgroNext BD','Agriculture & Agri-Tech','Mymensingh, Bangladesh','Technology-enabled tools and services for modern farming communities.'],
            ['FinBridge Ltd.','Fintech','Dhaka, Bangladesh','Digital financial services and payment infrastructure for businesses.'],
            ['HealthPlus Ltd.','Healthcare & Wellness','Dhaka, Bangladesh','Accessible healthcare services supported by modern technology.'],
            ['EduConnect','Education & EdTech','Chattogram, Bangladesh','Learning platforms and education services for students and professionals.'],
            ['GreenWorld Ltd.','Renewable Energy','Dhaka, Bangladesh','Renewable energy solutions and sustainability advisory services.'],
            ['FashionCraft','Garments & Fashion','Narayanganj, Bangladesh','Apparel design, sourcing and responsible garment production.'],
            ['BuildMax Construction','Real Estate & Construction','Dhaka, Bangladesh','Construction and project delivery services for residential and commercial clients.'],
            ['FoodNest Ltd.','Food & Beverage','Dhaka, Bangladesh','Food products and distribution focused on quality and convenience.'],
            ['TravelWay BD','Travel & Tourism','Cox’s Bazar, Bangladesh','Travel planning and destination services across Bangladesh.'],
            ['CloudNode','Cloud & IT Services','Dhaka, Bangladesh','Cloud infrastructure, managed IT and cybersecurity services.'],
            ['RiverBank Ltd.','Banking & Financial Services','Dhaka, Bangladesh','Financial services and business banking solutions.'],
            ['SmartHome BD','Smart Living & IoT','Dhaka, Bangladesh','Connected home devices and automation solutions.'],
            ['MediStar Hospital','Healthcare Services','Sylhet, Bangladesh','Multidisciplinary medical care and diagnostic services.'],
            ['BrightSkills Academy','Skills & Training','Dhaka, Bangladesh','Professional training and career development programmes.'],
            ['EcoPack Ltd.','Sustainable Packaging','Gazipur, Bangladesh','Packaging products designed to reduce environmental impact.'],
            ['UrbanDesign','Interior & Architecture','Dhaka, Bangladesh','Architecture, interior planning and built-environment design.'],
            ['NextGen Tech','Electronics & Innovation','Dhaka, Bangladesh','Technology products and innovation services for modern workplaces.'],
            ['PrimeLogistics','Logistics & Supply Chain','Chattogram, Bangladesh','Freight, warehousing and supply-chain coordination services.'],
            ['BookNest Publications','Publishing & Media','Dhaka, Bangladesh','Publishing and editorial services for authors and organisations.'],
            ];
            $now = now();
            $profileIds = ['people' => [], 'companies' => []];
            foreach ($people as $index => [$name, $title, $location, $industry]) {
                $slug = Str::slug($name);
                $profileId = DB::table('profiles')->insertGetId([
                    'profile_type' => 'personal', 'slug' => $slug, 'status' => 'published', 'is_verified' => 1,
                    'created_by_admin_id' => null, 'created_by_customer_id' => null, 'published_at' => $now,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $versionId = DB::table('profile_versions')->insertGetId([
                    'profile_id' => $profileId, 'version_number' => 1, 'proposed_slug' => $slug,
                    'review_status' => 'approved', 'submitted_at' => $now, 'reviewed_at' => $now,
                    'review_note' => 'Demo seed profile for initial website content.', 'change_summary' => 'Initial sample profile',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('personal_profile_details')->insert([
                    'profile_version_id' => $versionId, 'full_name' => $name, 'headline' => $title,
                    'professional_title' => $title, 'current_position' => $title, 'company_organisation' => 'Independent',
                    'location' => $location, 'nationality' => 'Bangladeshi', 'occupation' => $title,
                    'short_bio' => $name.' is a sample '.$title.' profile created to demonstrate the directory layout.',
                    'about' => $name.' works across '.$industry.' and focuses on building practical solutions, developing teams and creating long-term value. This is illustrative demo content, not a verified biography.',
                    'profile_quote' => 'Build with purpose, learn continuously and create meaningful impact.',
                    'early_life' => 'Background information can be added and verified by an administrator before publication.',
                    'career_entrepreneurship' => 'Career highlights and entrepreneurial milestones are presented here as sample content for the profile layout.',
                    'education_summary' => 'Education and professional training details can be added during editorial review.',
                    'awards_publications_recognition' => 'Awards, publications and recognition details are not supplied in this sample profile.',
                    'show_public_email' => 0, 'show_public_phone' => 0, 'show_date_of_birth' => 0,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $path = 'website/assets/demo-profiles/person-'.str_pad((string)($index+1),2,'0',STR_PAD_LEFT).'.svg';
                $mediaId = DB::table('media_assets')->insertGetId([
                    'collection' => 'profile_portrait', 'disk' => 'public', 'path' => $path,
                    'original_name' => basename($path), 'mime_type' => 'image/svg+xml', 'alt_text' => $name.' sample profile illustration',
                    'caption' => 'Illustrative sample profile artwork', 'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('profile_version_media')->insert(['profile_version_id'=>$versionId,'media_asset_id'=>$mediaId,'role'=>'portrait','sort_order'=>0,'created_at'=>$now,'updated_at'=>$now]);
                DB::table('profiles')->where('id',$profileId)->update(['published_version_id'=>$versionId]);
                $profileIds['people'][] = $profileId;
            }
            foreach ($companies as $index => [$name, $industry, $location, $description]) {
                $slug = Str::slug($name);
                $profileId = DB::table('profiles')->insertGetId([
                    'profile_type' => 'company', 'slug' => $slug, 'status' => 'published', 'is_verified' => 1,
                    'created_by_admin_id' => null, 'created_by_customer_id' => null, 'published_at' => $now,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $versionId = DB::table('profile_versions')->insertGetId([
                    'profile_id' => $profileId, 'version_number' => 1, 'proposed_slug' => $slug,
                    'review_status' => 'approved', 'submitted_at' => $now, 'reviewed_at' => $now,
                    'review_note' => 'Demo seed profile for initial website content.', 'change_summary' => 'Initial sample company profile',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('company_profile_details')->insert([
                    'profile_version_id' => $versionId, 'company_name' => $name, 'tagline' => $description,
                    'founded_year' => 2018 + ($index % 7), 'headquarters' => $location, 'company_size' => ['11-50','51-200','201-500'][$index%3],
                    'business_type' => 'Private Company', 'area_served' => 'Bangladesh', 'official_website' => 'https://example.com',
                    'short_description' => $description, 'overview' => $description.' This sample company profile demonstrates the public profile layout and can be replaced with verified company information.',
                    'history_background' => 'Company history and milestones should be reviewed and completed by an administrator.',
                    'mission' => 'Deliver useful, dependable solutions and create sustainable value for customers and communities.',
                    'vision' => 'Become a trusted contributor to the growth of the '.$industry.' sector.',
                    'show_public_email' => 0, 'show_public_phone' => 0, 'created_at' => $now, 'updated_at' => $now,
                ]);
                $path = 'website/assets/demo-profiles/company-'.str_pad((string)($index+1),2,'0',STR_PAD_LEFT).'.svg';
                $mediaId = DB::table('media_assets')->insertGetId([
                    'collection' => 'company_logo', 'disk' => 'public', 'path' => $path,
                    'original_name' => basename($path), 'mime_type' => 'image/svg+xml', 'alt_text' => $name.' sample logo artwork',
                    'caption' => 'Illustrative sample company logo', 'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('profile_version_media')->insert(['profile_version_id'=>$versionId,'media_asset_id'=>$mediaId,'role'=>'logo','sort_order'=>0,'created_at'=>$now,'updated_at'=>$now]);
                DB::table('profiles')->where('id',$profileId)->update(['published_version_id'=>$versionId]);
                $profileIds['companies'][] = $profileId;
            }

            $sections = [
                ['hero_profiles','Hero Profiles','Hero visual profiles',array_slice($profileIds['people'],0,6),0],
                ['featured_entrepreneurs','Featured Entrepreneurs','Selected entrepreneurs and business leaders',array_slice($profileIds['people'],0,8),10],
                ['entrepreneur_spotlights','Entrepreneur Spotlights','Selected people and their professional journeys',array_slice($profileIds['people'],8,6),20],
                ['featured_story','FEATURED STORY','Administrator-selected featured profile story',[$profileIds['people'][16]],30],
                ['recently_published_profiles','Recently Published Profiles','Recently added people and companies',array_merge(array_slice($profileIds['companies'],0,6),array_slice($profileIds['people'],0,6)),40],
            ];
            foreach ($sections as [$key,$title,$subtitle,$ids,$order]) {
                $sectionId = DB::table('homepage_sections')->insertGetId([
                    'section_key'=>$key,'section_type'=>'profiles','title'=>$title,'subtitle'=>$subtitle,
                    'content'=>null,'settings'=>json_encode(['card_style'=>'profile','seeded'=>true]),
                    'is_enabled'=>1,'sort_order'=>$order,'created_at'=>$now,'updated_at'=>$now,
                ]);
                foreach ($ids as $sort => $profileId) {
                    DB::table('homepage_section_items')->insert([
                        'homepage_section_id'=>$sectionId,'profile_id'=>$profileId,'item_type'=>'profile',
                        'is_enabled'=>1,'sort_order'=>$sort,'created_at'=>$now,'updated_at'=>$now,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        $keys = ['hero_profiles','featured_entrepreneurs','entrepreneur_spotlights','featured_story','recently_published_profiles'];
        $sectionIds = DB::table('homepage_sections')->whereIn('section_key',$keys)->pluck('id');
        DB::table('homepage_section_items')->whereIn('homepage_section_id',$sectionIds)->delete();
        DB::table('homepage_sections')->whereIn('id',$sectionIds)->delete();
        $slugs = array_map(fn($name) => Str::slug($name), [
            'Ahsanul Karim', 'Farzana Rahman', 'Mohammad Imran', 'Nusrat Jahan', 'Tanjim Hasan', 'Sadia Akter', 'Shafayet Ahmed', 'Rubaiyat Chowdhury', 'Mitu Akhter', 'Arifur Rahman', 'Ripa Islam', 'Jahid Hasan', 'Sumaiya Sultana', 'Rakibul Islam', 'Amanda Chowdhury', 'Nazmul Hasan', 'Lamiya Akter', 'Emon Hossain', 'Sharmin Akter', 'Hasan Mahmud', 'TechNova Ltd.', 'AgroNext BD', 'FinBridge Ltd.', 'HealthPlus Ltd.', 'EduConnect', 'GreenWorld Ltd.', 'FashionCraft', 'BuildMax Construction', 'FoodNest Ltd.', 'TravelWay BD', 'CloudNode', 'RiverBank Ltd.', 'SmartHome BD', 'MediStar Hospital', 'BrightSkills Academy', 'EcoPack Ltd.', 'UrbanDesign', 'NextGen Tech', 'PrimeLogistics', 'BookNest Publications', 'Nazim Uddin Hridom', 'GrowBig Digital'
        ]);
        $profileIds = DB::table('profiles')->whereIn('slug',$slugs)->pluck('id');
        $versionIds = DB::table('profile_versions')->whereIn('profile_id',$profileIds)->pluck('id');
        $mediaIds = DB::table('profile_version_media')->whereIn('profile_version_id',$versionIds)->pluck('media_asset_id');
        DB::table('profile_version_media')->whereIn('profile_version_id',$versionIds)->delete();
        DB::table('media_assets')->whereIn('id',$mediaIds)->delete();
        DB::table('personal_profile_details')->whereIn('profile_version_id',$versionIds)->delete();
        DB::table('company_profile_details')->whereIn('profile_version_id',$versionIds)->delete();
        DB::table('profile_versions')->whereIn('id',$versionIds)->delete();
        DB::table('profiles')->whereIn('id',$profileIds)->delete();
    }
};
