<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('extra_pages')) {
            $pages = [
                [
                    'page_key' => 'about',
                    'title' => 'About Us',
                    'slug' => 'about-us',
                    'content' => '<p>EntrepreneurWiki is an independent platform documenting entrepreneurs, founders, business leaders and the organisations they build.</p><p>Our goal is to make reliable, useful and clearly presented information easier to discover. We bring together professional stories, company information and sourced achievements in one accessible place.</p><p>We value accuracy, transparency and responsible contributions. Public profile information is reviewed before publication, and readers can contact our team with corrections or suggestions.</p>',
                    'meta_title' => 'About Us | EntrepreneurWiki',
                    'meta_description' => 'Learn about EntrepreneurWiki, our purpose and our approach to entrepreneur and business information.',
                    'meta_keywords' => null,
                    'meta_robots' => 'index,follow',
                    'canonical_url' => null,
                    'is_published' => 1,
                ],
                [
                    'page_key' => 'contact',
                    'title' => 'Contact Us',
                    'slug' => 'contact-us',
                    'content' => '<p>We welcome questions, profile suggestions, corrections and partnership enquiries.</p><p>Use the contact details shown on this page to reach the EntrepreneurWiki team. For profile submissions, please create an account and follow the review process.</p>',
                    'meta_title' => 'Contact Us | EntrepreneurWiki',
                    'meta_description' => 'Contact the EntrepreneurWiki team for questions, corrections, profile suggestions and partnerships.',
                    'meta_keywords' => null,
                    'meta_robots' => 'index,follow',
                    'canonical_url' => null,
                    'is_published' => 1,
                ],
            ];

            foreach ($pages as $page) {
                $exists = DB::table('extra_pages')
                    ->where('page_key', $page['page_key'])
                    ->orWhere('slug', $page['slug'])
                    ->exists();

                if (!$exists) {
                    $page['created_at'] = now();
                    $page['updated_at'] = now();
                    DB::table('extra_pages')->insert($page);
                }
            }
        }

        // Existing SQL dumps may have a Super Admin role without pivot permissions.
        // The application grants Super Admin all abilities through its Gate as well;
        // keeping the pivot in sync makes role screens and permission listings consistent.
        if (Schema::hasTable('roles') && Schema::hasTable('permissions') && Schema::hasTable('role_has_permissions')) {
            $superAdminRoleId = DB::table('roles')
                ->where('name', 'Super Admin')
                ->where('guard_name', 'web')
                ->value('id');

            if ($superAdminRoleId) {
                $permissionIds = DB::table('permissions')
                    ->where('guard_name', 'web')
                    ->pluck('id');

                $rows = $permissionIds->map(fn ($permissionId) => [
                    'permission_id' => $permissionId,
                    'role_id' => $superAdminRoleId,
                ])->all();

                if ($rows !== []) {
                    DB::table('role_has_permissions')->insertOrIgnore($rows);
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally retain published page content and permission assignments on rollback.
    }
};
