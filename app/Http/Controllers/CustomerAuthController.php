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
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:191', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
        ]);

        // Registration never creates an authenticated session. Customers must verify
        // their email first and then wait for an administrator to approve the account.
        $customer->forceFill(['status' => 'pending_verification'])->save();

        try {
            $customer->sendEmailVerificationNotification();
            $message = 'Your account has been created. Please check your email for the verification link.';
        } catch (Throwable $exception) {
            Log::warning('Customer verification email could not be sent.', [
                'customer_id' => $customer->id,
                'exception' => $exception->getMessage(),
            ]);

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
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        $customer = Customer::where('email', strtolower($data['email']))->first();

        if ($customer && !$customer->hasVerifiedEmail()) {
            try {
                $customer->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                Log::warning('Customer verification email resend failed.', [
                    'customer_id' => $customer->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return back()->with('status', 'If the account needs email verification, a new verification link has been sent.');
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
