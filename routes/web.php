<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\HomepageManagerController;
use App\Http\Controllers\Admin\GlobalNoticeController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\ExtraPageController;
use App\Http\Controllers\BlogController as PublicBlogController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\PublicContactController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\PublicSearchController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerProfileSettingsController;
use App\Http\Controllers\CustomerProfileAccessRequestController;
use App\Http\Controllers\Admin\CustomerAccountController;
use App\Http\Controllers\Admin\ProfileAccessRequestController as AdminProfileAccessRequestController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Middleware\EnsureCustomerApproved;
use App\Http\Middleware\RedirectIfCustomerAuthenticated;

Route::get('/', [FrontController::class, 'index'])->name('site.home');
Route::get('/profiles', [FrontController::class, 'allProfiles'])->name('site.profiles.index');
Route::get('/companies', [FrontController::class, 'companyProfilesDirectory'])->name('site.companies.index');
Route::get('/entrepreneurs', [FrontController::class, 'personalProfilesDirectory'])->name('site.entrepreneurs.index');
Route::get('/company-profile', [FrontController::class, 'companyProfile'])->name('site.company-profile');
Route::get('/personal-profile', [FrontController::class, 'personalProfile'])->name('site.personal-profile');
Route::get('/about-us', [FrontController::class, 'aboutUs'])->name('site.about-us');
Route::get('/contact-us', [FrontController::class, 'contactUs'])->name('site.contact-us');
Route::post('/contact-us', [PublicContactController::class, 'store'])->middleware('throttle:5,1')->name('site.contact.submit');
Route::post('/newsletter/subscribe', [NewsletterSubscriptionController::class, 'store'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::get('/ajax/search', PublicSearchController::class)->middleware('throttle:60,1')->name('site.search');

Route::get('terms-and-conditions', [LegalPageController::class, 'terms'])->name('site.terms');
Route::get('privacy-policy', [LegalPageController::class, 'privacy'])->name('site.privacy');


// Customer authentication uses its own guard and never shares admin credentials.
Route::middleware(RedirectIfCustomerAuthenticated::class)->group(function () {
    Route::get('/customer-login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/customer-login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('customer.login.submit');

    Route::get('/customer-register', [CustomerAuthController::class, 'showRegistration'])->name('customer.register');
    Route::post('/customer-register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('customer.register.submit');

    Route::get('/customer-forgot-password', [CustomerAuthController::class, 'showForgotPassword'])->name('customer.forgot-password');
    Route::post('/customer-forgot-password', [CustomerAuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('customer.password.email');

    Route::get('/customer-reset-password/{token}', [CustomerAuthController::class, 'showResetForm'])->name('customer.reset-password');
    Route::post('/customer-reset-password', [CustomerAuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('customer.password.update');
});

Route::get('/customer-email-verification-notice', [CustomerAuthController::class, 'verificationNotice'])->name('customer.verification.notice');
Route::post('/customer-email-verification/resend', [CustomerAuthController::class, 'resendVerification'])->middleware('throttle:3,1')->name('customer.verification.resend');
Route::get('/customer-email-verification/{id}/{hash}', [CustomerAuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('customer.verification.verify');

Route::middleware(EnsureCustomerApproved::class)->group(function () {
    Route::post('/customer-logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    Route::get('/customer-dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::get('/customer-profile-history', [CustomerDashboardController::class, 'history'])->name('customer.profile-history');
    Route::post('/customer-profile-requests', [CustomerProfileAccessRequestController::class, 'store'])->middleware('throttle:5,1')->name('customer.profile-requests.store');

    Route::get('/customer-profile', [CustomerProfileSettingsController::class, 'edit'])->name('customer.profile');
    Route::put('/customer-profile', [CustomerProfileSettingsController::class, 'update'])->name('customer.profile.update');

    Route::get('/customer-company-profile-update', [CustomerProfileController::class, 'editCompany'])->name('customer.company-profile-update');
    Route::post('/customer-company-profile-update', [CustomerProfileController::class, 'storeCompany'])->middleware('throttle:10,1')->name('customer.profiles.company.store');
    Route::put('/customer-company-profile-update/{profile}', [CustomerProfileController::class, 'updateCompany'])->middleware('throttle:10,1')->name('customer.profiles.company.update');
    Route::get('/customer-personal-profile-update', [CustomerProfileController::class, 'editPersonal'])->name('customer.personal-profile-update');
    Route::post('/customer-personal-profile-update', [CustomerProfileController::class, 'storePersonal'])->middleware('throttle:10,1')->name('customer.profiles.personal.store');
    Route::put('/customer-personal-profile-update/{profile}', [CustomerProfileController::class, 'updatePersonal'])->middleware('throttle:10,1')->name('customer.profiles.personal.update');
});





// Legacy Laravel auth landing URL: never show the default /home dashboard in this project.
Route::get('/home', function () {
    $admin = \Illuminate\Support\Facades\Auth::guard('web')->user();

    if ($admin) {
        return $admin->can('access.admin')
            ? redirect()->route('admin.dashboard')
            : redirect()->route('site.home');
    }

    if (\Illuminate\Support\Facades\Auth::guard('customer')->check()) {
        return redirect()->route('customer.dashboard');
    }

    return redirect()->route('site.home');
})->name('home');
Route::get('/blog', [PublicBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PublicBlogController::class, 'show'])->name('blog.show');
Route::prefix('admin')->name('admin.')->middleware(\App\Http\Middleware\RecordAdminActivity::class)->group(function () {
    Route::middleware(\App\Http\Middleware\RedirectIfAdminAuthenticated::class)->group(function () {
        Route::get('/login',[AuthController::class,'loginPage'])->name('login');
        Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1')->name('login.submit');
        Route::get('/forgot-password',[AuthController::class,'forgotPasswordPage'])->name('forgot');
        Route::post('/forgot-password',[AuthController::class,'checkEmail'])->middleware('throttle:3,1')->name('forgot.submit');
        Route::get('/reset-password/{token}',[AuthController::class,'resetPasswordPage'])->name('reset.form');
        Route::post('/reset-password/{token}',[AuthController::class,'resetPassword'])->middleware('throttle:3,1')->name('reset.submit');
    });
    Route::post('/logout',[AuthController::class,'logout'])->middleware(\App\Http\Middleware\EnsureAdminAuthenticated::class)->name('logout');
    Route::middleware([\App\Http\Middleware\EnsureAdminAuthenticated::class,'can:access.admin'])->group(function () {
        Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
        Route::get('/notifications/customer-registrations', [AdminNotificationController::class, 'customerRegistrations'])->middleware('can:customers.manage')->name('notifications.customer-registrations');

        Route::get('/contact-messages', [ContactMessageController::class, 'index'])->middleware('can:contact-messages.manage')->name('contact-messages.index');
        Route::get('/newsletter-subscribers', [NewsletterSubscriberController::class, 'index'])->middleware('can:settings.manage')->name('newsletter-subscribers.index');
        Route::post('/newsletter-subscribers/{subscriber}/toggle-status', [NewsletterSubscriberController::class, 'toggleStatus'])->middleware('can:settings.manage')->name('newsletter-subscribers.toggle-status');
        Route::delete('/newsletter-subscribers/{subscriber}', [NewsletterSubscriberController::class, 'destroy'])->middleware('can:settings.manage')->name('newsletter-subscribers.destroy');
        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->middleware('can:contact-messages.manage')->name('contact-messages.show');
        Route::post('/contact-messages/{contactMessage}/toggle-read', [ContactMessageController::class, 'toggleRead'])->middleware('can:contact-messages.manage')->name('contact-messages.toggle-read');
        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->middleware('can:contact-messages.manage')->name('contact-messages.destroy');

        Route::get('/customer-accounts', [CustomerAccountController::class, 'index'])->middleware('can:customers.manage')->name('customer-accounts.index');
        Route::post('/customer-accounts/{customer}/approve', [CustomerAccountController::class, 'approve'])->middleware('can:customers.manage')->name('customer-accounts.approve');
        Route::post('/customer-accounts/{customer}/reject', [CustomerAccountController::class, 'reject'])->middleware('can:customers.manage')->name('customer-accounts.reject');

        Route::get('/profile-requests', [AdminProfileAccessRequestController::class, 'index'])->middleware('can:profile-requests.manage')->name('profile-requests.index');
        Route::post('/profile-requests/{profileRequest}/approve', [AdminProfileAccessRequestController::class, 'approve'])->middleware('can:profile-requests.manage')->name('profile-requests.approve');
        Route::post('/profile-requests/{profileRequest}/reject', [AdminProfileAccessRequestController::class, 'reject'])->middleware('can:profile-requests.manage')->name('profile-requests.reject');

        Route::get('/profiles', [AdminProfileController::class, 'index'])->middleware('can:profiles.manage')->name('profiles.index');
        Route::get('/profiles/individual', [AdminProfileController::class, 'individualProfiles'])->middleware('can:profiles.manage')->name('profiles.individual');
        Route::get('/profiles/companies', [AdminProfileController::class, 'companyProfiles'])->middleware('can:profiles.manage')->name('profiles.companies');
        Route::get('/profiles/revision-history', [AdminProfileController::class, 'revisionHistory'])->middleware('can:profiles.manage')->name('profiles.revision-history');
        Route::get('/profiles/verification-center', [AdminProfileController::class, 'verificationCenter'])->middleware('can:profiles.manage')->name('profiles.verification-center');
        Route::post('/profiles/{profile}/verification', [AdminProfileController::class, 'toggleVerification'])->middleware('can:profiles.manage')->name('profiles.verification.update');
        Route::get('/profiles/submissions', [AdminProfileController::class, 'submissions'])->middleware('can:profiles.manage')->name('profiles.submissions');
        Route::get('/profiles/create', [AdminProfileController::class, 'create'])->middleware('can:profiles.manage')->name('profiles.create');
        Route::post('/profiles', [AdminProfileController::class, 'store'])->middleware('can:profiles.manage')->name('profiles.store');
        Route::get('/profiles/{profile}/edit', [AdminProfileController::class, 'edit'])->middleware('can:profiles.manage')->name('profiles.edit');
        Route::put('/profiles/{profile}', [AdminProfileController::class, 'update'])->middleware('can:profiles.manage')->name('profiles.update');
        Route::get('/profiles/{profile}/history', [AdminProfileController::class, 'history'])->middleware('can:profiles.manage')->name('profiles.history');
        Route::get('/profile-versions/{version}/review', [AdminProfileController::class, 'review'])->middleware('can:profiles.manage')->name('profiles.review');
        Route::post('/profile-versions/{version}/approve', [AdminProfileController::class, 'approve'])->middleware('can:profiles.manage')->name('profiles.approve');
        Route::post('/profile-versions/{version}/return', [AdminProfileController::class, 'returnForChanges'])->middleware('can:profiles.manage')->name('profiles.return');
        Route::post('/profile-versions/{version}/reject', [AdminProfileController::class, 'reject'])->middleware('can:profiles.manage')->name('profiles.reject');
        Route::resource('/users',UserController::class)->except('show')->middleware('can:users.manage');
        Route::resource('/roles',RoleController::class)->except('show')->middleware('can:roles.manage');
        Route::resource('/permissions',PermissionController::class)->except('show')->middleware('can:permissions.manage');
        Route::get('/settings',[SettingController::class,'index'])->middleware('can:settings.manage')->name('settings.index');
        Route::put('/settings',[SettingController::class,'update'])->middleware('can:settings.manage')->name('settings.update');
        Route::get('/homepage-manager', [HomepageManagerController::class, 'index'])->middleware('can:settings.manage')->name('homepage-manager.index');
        Route::put('/homepage-manager', [HomepageManagerController::class, 'update'])->middleware('can:settings.manage')->name('homepage-manager.update');
        Route::post('/homepage-manager/profile-sections', [HomepageManagerController::class, 'storeProfileSection'])->middleware('can:settings.manage')->name('homepage-manager.profile-sections.store');
        Route::put('/homepage-manager/profile-sections/{section}', [HomepageManagerController::class, 'updateProfileSection'])->middleware('can:settings.manage')->name('homepage-manager.profile-sections.update');
        Route::delete('/homepage-manager/profile-sections/{section}', [HomepageManagerController::class, 'destroyProfileSection'])->middleware('can:settings.manage')->name('homepage-manager.profile-sections.destroy');
        Route::get('/global-notice', [GlobalNoticeController::class, 'index'])->middleware('can:settings.manage')->name('global-notice.index');
        Route::put('/global-notice', [GlobalNoticeController::class, 'update'])->middleware('can:settings.manage')->name('global-notice.update');

        Route::get('/activity',[ActivityLogController::class,'index'])->middleware('can:activity.view')->name('activity.index');
        Route::post('/activity/prune',[ActivityLogController::class,'prune'])->middleware(['can:activity.cleanup','throttle:5,1'])->name('activity.prune');
        Route::get('/storage',[StorageController::class,'index'])->middleware('can:storage.view')->name('storage.index');
        Route::post('/storage/cleanup',[StorageController::class,'cleanup'])->middleware(['can:storage.cleanup','throttle:5,1'])->name('storage.cleanup');
        Route::get('/backups',[BackupController::class,'index'])->middleware('can:backups.manage')->name('backups.index');
        Route::post('/backups',[BackupController::class,'create'])->middleware(['can:backups.manage','throttle:3,1'])->name('backups.create');
        Route::get('/backups/download/{filename}',[BackupController::class,'download'])->middleware('can:backups.manage')->name('backups.download');
        Route::delete('/backups/{filename}',[BackupController::class,'destroy'])->middleware('can:backups.manage')->name('backups.destroy');
        Route::post('/backups/maintenance',[BackupController::class,'maintenance'])->middleware('can:backups.manage')->name('backups.maintenance');
        Route::post('/backups/clear-cache',[BackupController::class,'clearCache'])->middleware('can:backups.manage')->name('backups.cache');
        Route::get('/seo',[SeoController::class,'index'])->middleware('can:seo.manage')->name('seo.index');
        Route::put('/seo/global',[SeoController::class,'globalUpdate'])->middleware('can:seo.manage')->name('seo.global');
        Route::post('/seo/pages',[SeoController::class,'store'])->middleware('can:seo.manage')->name('seo.pages.store');
        Route::put('/seo/pages/{page}',[SeoController::class,'update'])->middleware('can:seo.manage')->name('seo.pages.update');
        Route::delete('/seo/pages/{page}',[SeoController::class,'destroy'])->middleware('can:seo.manage')->name('seo.pages.destroy');

        Route::resource('/blog', AdminBlogController::class)->except('show')->middleware('can:blog.manage');
        Route::get('/extra-pages', [ExtraPageController::class, 'index'])->middleware('can:extra-pages.manage')->name('extra-pages.index');
        Route::put('/extra-pages/{extraPage}', [ExtraPageController::class, 'update'])->middleware('can:extra-pages.manage')->name('extra-pages.update');
    });
});

// Public profile URLs use the existing root-level slug convention. Keep this catch-all last.
Route::get('/{profile:slug}', [FrontController::class, 'showProfile'])
    ->where('profile', '[A-Za-z0-9._-]+')
    ->name('site.profile.show');
