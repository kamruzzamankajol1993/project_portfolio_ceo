<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class CustomerProfileSettingsController extends Controller
{
    public function edit(): View
    {
        return view('website.customer.auth.customerProfileSetting', [
            'customer' => Auth::guard('customer')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        $emailChanged = strtolower((string) $request->input('email')) !== strtolower($customer->email);
        $passwordChanged = filled($request->input('password'));
        $requiresCurrentPassword = $emailChanged || $passwordChanged;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:191', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'current_password' => [
                $requiresCurrentPassword ? 'required' : 'nullable',
                'current_password:customer',
            ],
            'password' => ['nullable', 'string', Password::min(8), 'confirmed'],
        ]);

        $customer->name = $data['name'];
        $customer->phone = $data['phone'] ?? null;

        if ($emailChanged) {
            $customer->email = strtolower($data['email']);
            $customer->email_verified_at = null;
            $customer->status = 'pending_verification';
        }

        if ($passwordChanged) {
            $customer->password = $data['password'];
        }

        $customer->save();

        if ($emailChanged) {
            try {
                $customer->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                Log::warning('Updated customer email verification could not be sent.', [
                    'customer_id' => $customer->id,
                    'exception' => $exception->getMessage(),
                ]);
            }

            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('customer.verification.notice', [
                'email' => $customer->email,
            ])->with('status', 'Your email address was updated. Verify the new address before signing in again. Administrator approval will be required after verification.');
        }

        return redirect()->route('customer.profile')->with('success', 'Your account details have been updated successfully.');
    }
}
