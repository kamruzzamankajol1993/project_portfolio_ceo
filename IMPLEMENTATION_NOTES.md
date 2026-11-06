# EntrepreneurWiki — Admin Module Implementation

This ZIP is an **overlay of the provided project files**, not a standalone Laravel installation. The uploaded archive had **no** `artisan`, `composer.json`, `bootstrap/`, `.env`, or `vendor/`. Merge these files with your existing Laravel project (same version and installed packages), then run commands in that complete Laravel project.

## Modules in this phase

- Admin login/logout: `/admin/login`, admin permission check, session renewal, remember me, request throttling.
- Forgot/reset without email: email database lookup, session-bound five-minute token, password update. **Critical security warning: email lookup is NOT proof of ownership. Anyone knowing a registered email address can take over an account. The feature is disabled by default; only enable it for local testing with `ALLOW_UNSAFE_EMAIL_ONLY_RESET=true` in `.env`. THIS FLOW MUST NOT BE DEPLOYED PUBLICLY.** Replace with a genuine recovery factor before public launch. Super Admin is blocked from this recovery flow.
- Users CRUD (name, email, phone, photo, password, status, address, bio and assigned roles). Super Admin is hidden and cannot be edited or deleted through these controllers.
- Role CRUD with select-all / group / individual permission checkboxes, including indeterminate state.
- Permission CRUD with a `group` column and dynamic add-more inputs to create several permissions in one group.
- Singleton settings table with site branding, contact details, social links, metadata/OG/Twitter/robots/canonical, analytics, maintenance flag (503 maintenance page for non-admin visitors) and scripts; SEO meta and scripts are rendered in `resources/views/welcome.blade.php`.
- First/Previous/Next/Last custom AJAX pagers for users, roles, permissions, with page and query URL updates.
- CDN assets included globally in admin layout: Flatpickr 4.6.13, Choices.js 11.2.4, SweetAlert2 11, Summernote Lite 0.9.1; widgets initialized in `public/admin/js/modules.js`.
- Uploaded images processed with Intervention Image Laravel v3, resized and saved as WebP to `public/uploads/{users,settings}/` and displayed as requested using `asset('public/uploads/...')`.

## Install steps

1. Copy these files into the complete Laravel project, keeping directory structure; back up files you overwrite.
2. Check that Composer already has `spatie/laravel-permission`, `intervention/image-laravel` (v1.5.9, which integrates Intervention Image v3), `laravel/ui`, and the full Laravel framework (as indicated by `composer.lock`).
3. Configure database and app in `.env`. Add the following variables with **your own strong secret passwords** before seeding (do not commit `.env`):

```dotenv
SEED_SUPER_ADMIN_EMAIL=superadmin@example.test
SEED_SUPER_ADMIN_PASSWORD=REPLACE_WITH_UNIQUE_STRONG_PASSWORD
SEED_ADMIN_EMAIL=admin@example.test
SEED_ADMIN_PASSWORD=REPLACE_WITH_ANOTHER_UNIQUE_STRONG_PASSWORD
```

4. Run `php artisan migrate` and `php artisan db:seed` in your complete project. If your host uses config caching, run `php artisan config:clear` before seeding, then `php artisan permission:cache-reset` and `php artisan optimize:clear`.
5. Ensure `public/uploads/` is writable by PHP. No `storage:link` is necessary for these uploads.
6. Test routes on your deployment, including denied actions by low-privilege users. **Do not enable the email-only reset on a public site (`ALLOW_UNSAFE_EMAIL_ONLY_RESET` stays false).**

## Public asset URL warning

`asset('public/...')` implements the requested URL prefix. It assumes your domain's document root is the Laravel project root and `/public` remains visible in URLs. On a standard recommended Laravel setup whose document root is already `project/public`, these URLs become incorrect (double public); in that configuration change the image accessor and admin asset URL calls to `asset('uploads/...')` and `asset('admin/...')`. Use correct document-root configuration for production.

## Scope exclusions / constraints

- End-user verified registration and personal/company page request/approval/publishing are intentionally postponed.
- No real database can be modified from this archive. The two accounts are **defined in the seeder**, not created in your running database until migration/seeding.
- No full framework runtime/browser tests were possible with the files supplied. PHP source lint and static checks were done in the build environment.
- Admin settings include raw head/body script fields: restrict `settings.manage` to fully trusted admins and sanitize/user-limit any use of rich HTML on the public site.
- The `dashboard.blade.php` sample contained hardcoded fictitious statistics and pending profile features; it has been replaced by real counts and a phase notice.
