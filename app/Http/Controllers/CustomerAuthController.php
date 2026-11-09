<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CustomerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('website.customer.auth.login');
    }

    public function showRegistration(): View
    {
        return view('website.customer.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // A single ID ties all registration and email log entries together.
        $attemptId = (string) Str::uuid();

        Log::info('Customer registration request received.', [
            'event' => 'customer.registration.received',
            'attempt_id' => $attemptId,
            'route_name' => $request->route()?->getName(),
            'environment' => config('app.env'),
        ]);

        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:191', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        try {
            $customer = Customer::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
            ]);

            // Registration never creates an authenticated session. Customers must verify
            // their email first and then wait for an administrator to approve the account.
            $customer->forceFill(['status' => 'pending_verification'])->save();
        } catch (Throwable $exception) {
            Log::error('Customer account creation failed.', array_merge(
                [
                    'event' => 'customer.registration.account_creation_failed',
                    'attempt_id' => $attemptId,
                    'email_domain' => $this->emailDomain($data['email'] ?? null),
                ],
                $this->exceptionLogContext($exception)
            ));

            throw $exception;
        }

        $mailContext = $this->customerVerificationMailContext($customer, $attemptId, 'registration');

        Log::info('Customer account created; attempting verification email.', array_merge(
            ['event' => 'customer.verification_email.attempting'],
            $mailContext
        ));

        $this->warnIfVerificationMailerDoesNotDeliver($mailContext);

        try {
            $customer->sendEmailVerificationNotification();

            Log::info('Customer verification email dispatch completed.', array_merge(
                ['event' => 'customer.verification_email.dispatch_completed'],
                $mailContext
            ));

            $message = 'Your account has been created. Please check your email for the verification link.';
        } catch (Throwable $exception) {
            Log::error('Customer verification email dispatch failed.', array_merge(
                [
                    'event' => 'customer.verification_email.dispatch_failed',
                    'failure_stage' => 'sendEmailVerificationNotification',
                ],
                $mailContext,
                $this->exceptionLogContext($exception)
            ));

            $message = 'Your account has been created, but the verification email could not be sent. Please use the resend option below or contact support.';
        }

        return redirect()->route('customer.verification.notice', [
            'email' => $customer->email,
        ])->with('status', $message);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);


        if (!Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The email address or password is incorrect.',
            ])->onlyInput('email');
        }

        $customer = Auth::guard('customer')->user();

        if (!$customer->hasVerifiedEmail()) {
            Auth::guard('customer')->logout();

            return redirect()->route('customer.verification.notice', [
                'email' => $customer->email,
            ])->with('status', 'Please verify your email address using the link we sent you.');
        }

        if (!$customer->isActive()) {
            $message = $customer->status === 'rejected'
                ? 'Your account approval request was declined. Please contact the website administrator.'
                : 'Your email is verified. Your account is waiting for administrator approval.';

            Auth::guard('customer')->logout();

            return redirect()->route('customer.login')->with('status', $message);
        }

        $request->session()->regenerate();
        $customer->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }

    public function showForgotPassword(): View
    {
        return view('website.customer.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);


        try {
            $status = Password::broker('customers')->sendResetLink($data);
        } catch (Throwable $exception) {
            Log::error('Customer password reset email could not be sent.', [
                'email' => $data['email'],
                'exception' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'We could not send the reset email right now. Please try again later.',
            ])->onlyInput('email');
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => __($status)])->onlyInput('email');
        }

        // Do not disclose whether an email address belongs to a registered customer.
        return back()->with('status', 'If an account exists for that email address, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email');

        if (blank($email)) {
            return redirect()->route('customer.forgot-password');
        }

        return view('website.customer.auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:191'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);


        $status = Password::broker('customers')->reset(
            $data,
            function (Customer $customer, string $password): void {
                $customer->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('customer.login')->with('status', 'Your password has been reset. You can now sign in.');
        }

        return back()->withErrors([
            'email' => __($status),
        ])->withInput($request->only('email'));
    }

    public function verificationNotice(Request $request): View
    {
        return view('website.customer.auth.verify-email', [
            'email' => $request->query('email', old('email')),
        ]);
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $attemptId = (string) Str::uuid();

        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        $customer = Customer::where('email', strtolower($data['email']))->first();

        if (!$customer) {
            Log::info('Customer verification resend skipped: customer not found.', [
                'event' => 'customer.verification_email.resend_skipped',
                'attempt_id' => $attemptId,
                'reason' => 'customer_not_found',
                'email_domain' => $this->emailDomain($data['email']),
            ]);
        } elseif ($customer->hasVerifiedEmail()) {
            Log::info('Customer verification resend skipped: email already verified.', [
                'event' => 'customer.verification_email.resend_skipped',
                'attempt_id' => $attemptId,
                'reason' => 'already_verified',
                'customer_id' => $customer->getKey(),
            ]);
        } else {
            $mailContext = $this->customerVerificationMailContext($customer, $attemptId, 'resend');

            Log::info('Customer verification email resend attempting.', array_merge(
                ['event' => 'customer.verification_email.resend_attempting'],
                $mailContext
            ));

            $this->warnIfVerificationMailerDoesNotDeliver($mailContext);

            try {
                $customer->sendEmailVerificationNotification();

                Log::info('Customer verification email resend dispatch completed.', array_merge(
                    ['event' => 'customer.verification_email.resend_completed'],
                    $mailContext
                ));
            } catch (Throwable $exception) {
                Log::error('Customer verification email resend failed.', array_merge(
                    [
                        'event' => 'customer.verification_email.resend_failed',
                        'failure_stage' => 'sendEmailVerificationNotification',
                    ],
                    $mailContext,
                    $this->exceptionLogContext($exception)
                ));
            }
        }

        return back()->with('status', 'If the account needs email verification, a new verification link has been sent.');
    }

    /**
     * Safe mail configuration details for troubleshooting.
     * Never include SMTP passwords, MAIL_URL, or raw request credentials in logs.
     */
    private function customerVerificationMailContext(Customer $customer, string $attemptId, string $flow): array
    {
        $mailerName = (string) config('mail.default');
        $mailerConfig = (array) config('mail.mailers.' . $mailerName, []);
        $appUrl = (string) config('app.url');
        $parsedAppUrl = parse_url($appUrl) ?: [];

        return [
            'flow' => $flow,
            'attempt_id' => $attemptId,
            'customer_id' => $customer->getKey(),
            'email_domain' => $this->emailDomain($customer->email),
            'app_environment' => config('app.env'),
            'app_url_scheme' => $parsedAppUrl['scheme'] ?? null,
            'app_url_host' => $parsedAppUrl['host'] ?? null,
            'mail_default' => $mailerName,
            'mail_transport' => $mailerConfig['transport'] ?? $mailerName,
            'mail_fallback_mailers' => $mailerConfig['mailers'] ?? [],
            'mail_host' => $mailerConfig['host'] ?? null,
            'mail_port' => $mailerConfig['port'] ?? null,
            'mail_scheme' => $mailerConfig['scheme'] ?? null,
            'mail_url_configured' => !empty($mailerConfig['url']),
            'mail_smtp_username_configured' => !empty($mailerConfig['username']),
            'mail_smtp_password_configured' => !empty($mailerConfig['password']),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name_configured' => !empty(config('mail.from.name')),
            'notification_queued' => false,
        ];
    }

    /**
     * Log the complete exception chain and stack trace without exposing secrets.
     */
    private function exceptionLogContext(Throwable $exception): array
    {
        $exceptionChain = [];

        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            $exceptionChain[] = [
                'class' => get_class($current),
                'message' => $current->getMessage(),
                'code' => $current->getCode(),
                'file' => $current->getFile(),
                'line' => $current->getLine(),
            ];
        }

        return [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'exception_chain' => $exceptionChain,
            'stack_trace' => $exception->getTraceAsString(),
        ];
    }

    private function emailDomain(?string $email): ?string
    {
        if (!$email || !str_contains($email, '@')) {
            return null;
        }

        return substr(strrchr($email, '@'), 1) ?: null;
    }

    private function warnIfVerificationMailerDoesNotDeliver(array $mailContext): void
    {
        $transport = $mailContext['mail_transport'] ?? null;

        $fallbackMailers = $mailContext['mail_fallback_mailers'] ?? [];
        $usesLogFallback = is_array($fallbackMailers) && in_array('log', $fallbackMailers, true);

        if (in_array($transport, ['log', 'array', 'null'], true) || $usesLogFallback) {
            Log::warning('Customer verification mailer may not deliver email to the customer inbox.', array_merge(
                [
                    'event' => 'customer.verification_email.non_delivery_transport',
                    'warning' => 'The configured transport or fallback may only store the email in logs or memory.',
                ],
                $mailContext
            ));
        }
    }

    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        abort_unless(hash_equals(sha1($customer->getEmailForVerification()), $hash), 403);

        if (!$customer->hasVerifiedEmail()) {
            $customer->markEmailAsVerified();

            if ($customer->status === 'pending_verification') {
                $customer->forceFill(['status' => 'pending_approval'])->save();
            }
        }

        $message = match ($customer->status) {
            'active' => 'Your email is verified. You can now sign in.',
            'rejected' => 'Your email is verified, but the account approval request was declined. Please contact the website administrator.',
            default => 'Your email has been verified. Your account is now waiting for administrator approval.',
        };

        return redirect()->route('customer.login')->with('status', $message);
    }
}
