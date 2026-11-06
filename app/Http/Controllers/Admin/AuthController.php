<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        $success = Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => 'active',
        ], $request->boolean('remember'));

        if (!$success) {
            return back()->withErrors(['email' => 'Invalid credentials or inactive account.'])->onlyInput('email');
        }

        if (!Auth::user()->can('access.admin')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'This account cannot access the admin panel.']);
        }

        $request->session()->regenerate();

        // Do not honour stale intended URLs such as Laravel's legacy /home route.
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function forgotPasswordPage()
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Exactly the requested no-email flow: check email in DB, then allow a reset.
     * WARNING: knowing an email is NOT proof of identity. Do not deploy this
     * unverified recovery flow on a public site. Super Admin is excluded.
     */
    public function checkEmail(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);

        $user = User::query()
            ->where('email', $data['email'])
            ->where('status', 'active')
            ->first();

        if (!$user || $user->isFullSuperAdmin() || !$user->can('access.admin')) {
            return back()->withErrors(['email' => 'Account not found or not eligible for password recovery.'])
                ->onlyInput('email');
        }

        $token = Str::random(64);
        $request->session()->regenerate();
        $request->session()->put('admin_password_recovery', [
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(5)->timestamp,
        ]);

        // The token stays inside this browser's session; NO email is sent.
        return redirect()->route('admin.reset.form', ['token' => $token]);
    }

    public function resetPasswordPage(Request $request, string $token)
    {
        $this->checkRecovery($request, $token);

        return view('admin.auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request, string $token)
    {
        $userId = $this->checkRecovery($request, $token);
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
        ]);

        $user = User::whereKey($userId)->where('status', 'active')->firstOrFail();
        abort_if($user->isFullSuperAdmin() || !$user->can('access.admin'), 403);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->forget('admin_password_recovery');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Password updated. You can now sign in.');
    }

    private function checkRecovery(Request $request, string $token): int
    {
        $recovery = $request->session()->get('admin_password_recovery');

        abort_unless(
            is_array($recovery)
            && ($recovery['expires_at'] ?? 0) >= now()->timestamp
            && isset($recovery['token_hash'], $recovery['user_id'])
            && hash_equals($recovery['token_hash'], hash('sha256', $token)),
            403,
            'Recovery session expired or invalid.',
        );

        return (int) $recovery['user_id'];
    }
}
