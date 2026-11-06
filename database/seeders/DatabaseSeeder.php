<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\ExtraPage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Account names and email addresses are defined here; nothing is read from .env.
     * Strong random initial passwords are printed ONCE when an account is created.
     */
    private const SUPER_ADMIN_EMAIL = 'superadmin@example.test';
    private const ADMIN_EMAIL = 'admin@example.test';

    public function run(): void
    {
        $createdAccounts = [];

        DB::transaction(function () use (&$createdAccounts): void {
            $groups = [
                'Dashboard'   => ['access.admin'],
                'Users'       => ['users.manage'],
                'Customer Accounts' => ['customers.manage'],
                'Profile Requests' => ['profile-requests.manage'],
                'Profile Management' => ['profiles.manage'],
                'Roles'       => ['roles.manage'],
                'Permissions' => ['permissions.manage'],
                'Settings'    => ['settings.manage'],
                'Activity Logs' => ['activity.view','activity.cleanup'],
                'Storage' => ['storage.view','storage.cleanup'],
                'Backups' => ['backups.manage'],
                'SEO' => ['seo.manage'],
                'Blog' => ['blog.manage'],
                'Extra Pages' => ['extra-pages.manage'],
            ];

            foreach ($groups as $group => $names) {
                foreach ($names as $name) {
                    Permission::firstOrCreate(
                        ['name' => $name, 'guard_name' => 'web'],
                        ['group' => $group],
                    );
                }
            }

            $superRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
            $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
            $superRole->syncPermissions(Permission::all());
            $adminRole->syncPermissions(Permission::all());

            foreach ([
                [
                    'name' => 'Super Admin',
                    'email' => self::SUPER_ADMIN_EMAIL,
                    'role' => $superRole,
                ],
                [
                    'name' => 'Admin',
                    'email' => self::ADMIN_EMAIL,
                    'role' => $adminRole,
                ],
            ] as $account) {
                // Never hardcode a shared/known password in source code or a migration.
                // The User model's 'hashed' cast stores this value securely in the DB.
                $initialPassword = 'Aa7!' . bin2hex(random_bytes(16));
                $user = User::firstOrCreate(
                    ['email' => $account['email']],
                    [
                        'name' => $account['name'],
                        'password' => $initialPassword,
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ],
                );

                $user->syncRoles([$account['role']]);

                if ($user->wasRecentlyCreated) {
                    $createdAccounts[] = [
                        'role' => $account['name'],
                        'email' => $account['email'],
                        'password' => $initialPassword,
                    ];
                }
            }

            SiteSetting::current();

            ExtraPage::firstOrCreate(['page_key' => 'about'], [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<p>EntrepreneurWiki is an independent platform documenting entrepreneurs, founders, business leaders and the organisations they build.</p><p>Our goal is to make reliable, useful and clearly presented information easier to discover. We bring together professional stories, company information and sourced achievements in one accessible place.</p><p>We value accuracy, transparency and responsible contributions. Public profile information is reviewed before publication, and readers can contact our team with corrections or suggestions.</p>',
                'meta_title' => 'About Us | EntrepreneurWiki',
                'meta_description' => 'Learn about EntrepreneurWiki, our purpose and our approach to entrepreneur and business information.',
                'meta_robots' => 'index,follow',
                'is_published' => true,
            ]);
            ExtraPage::firstOrCreate(['page_key' => 'contact'], [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<p>We welcome questions, profile suggestions, corrections and partnership enquiries.</p><p>Use the contact details shown on this page to reach the EntrepreneurWiki team. For profile submissions, please create an account and follow the review process.</p>',
                'meta_title' => 'Contact Us | EntrepreneurWiki',
                'meta_description' => 'Contact the EntrepreneurWiki team for questions, corrections, profile suggestions and partnerships.',
                'meta_robots' => 'index,follow',
                'is_published' => true,
            ]);

            ExtraPage::firstOrCreate(['page_key' => 'terms'], [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-and-conditions',
                'content' => '',
                'meta_title' => 'Terms & Conditions',
                'meta_robots' => 'index,follow',
                'is_published' => false,
            ]);
            ExtraPage::firstOrCreate(['page_key' => 'privacy'], [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '',
                'meta_title' => 'Privacy Policy',
                'meta_robots' => 'index,follow',
                'is_published' => false,
            ]);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($createdAccounts !== []) {
            $this->command?->warn('New account credentials (shown only ONCE; save them now):');
            foreach ($createdAccounts as $account) {
                $this->command?->line($account['role'] . ' | Email: ' . $account['email'] . ' | Password: ' . $account['password']);
            }
            $this->command?->warn('Change both passwords after first login. Never publish or commit these credentials.');
        } else {
            $this->command?->info('Seed completed. Accounts already exist; their passwords were NOT changed.');
        }
    }
}
