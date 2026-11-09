<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CustomerAccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($term): void {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('customer_code', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();
        $pendingApprovalCount = Customer::where('status', 'pending_approval')->count();
        $pendingVerificationCount = Customer::where('status', 'pending_verification')->count();
        $approvedCount = Customer::where('status', 'active')->count();

        return view('admin.customer-accounts.index', compact(
            'customers',
            'pendingApprovalCount',
            'pendingVerificationCount',
            'approvedCount'
        ));
    }

    public function edit(Request $request, Customer $customer): View
    {
        return view('admin.customer-accounts.edit', [
            'customer' => $customer,
            'returnFilters' => $request->only(['search', 'status', 'page']),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:191', Rule::unique('customers', 'email')->ignore($customer->getKey())],
            'phone' => ['nullable', 'string', 'max:30'],
            'return_search' => ['nullable', 'string', 'max:255'],
            'return_status' => ['nullable', 'in:pending_verification,pending_approval,active,inactive,rejected'],
            'return_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $newEmail = strtolower(trim($data['email']));
        $emailChanged = $newEmail !== strtolower($customer->email);
        $customer->forceFill([
            'name' => trim($data['name']),
            'email' => $newEmail,
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
        ]);

        if ($emailChanged) {
            // A changed email address must be verified again before the customer can sign in.
            $customer->forceFill([
                'email_verified_at' => null,
                'status' => 'pending_verification',
            ]);
        }

        $customer->save();

        $verificationEmailSent = false;
        if ($emailChanged) {
            try {
                $customer->sendEmailVerificationNotification();
                $verificationEmailSent = true;
            } catch (Throwable $exception) {
                Log::warning('Admin updated customer email but verification email could not be sent.', [
                    'customer_id' => $customer->getKey(),
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        $filters = array_filter([
            'search' => $data['return_search'] ?? null,
            'status' => $data['return_status'] ?? null,
            'page' => $data['return_page'] ?? null,
        ], static fn ($value) => filled($value));

        if ($emailChanged) {
            // Show the updated account even if the previous list was filtered by its old status/email.
            $filters = ['search' => $customer->email];
        }

        $message = $emailChanged
            ? ($verificationEmailSent
                ? 'Customer updated. A verification link was sent to the new email address.'
                : 'Customer updated, but the verification email could not be sent. Ask the customer to request a new verification link.')
            : 'Customer account updated successfully.';

        return redirect()->route('admin.customer-accounts.index', $filters)->with('success', $message);
    }

    public function toggleStatus(Customer $customer): RedirectResponse
    {
        abort_unless($customer->hasVerifiedEmail(), 422, 'The customer must verify their email before activation.');

        if ($customer->status === 'active') {
            $customer->forceFill(['status' => 'inactive'])->save();
            $message = 'Customer account deactivated.';
        } elseif (in_array($customer->status, ['inactive', 'rejected'], true)) {
            $customer->forceFill(['status' => 'active'])->save();
            $message = 'Customer account activated.';
        } else {
            abort(422, 'Only inactive, rejected, or active accounts can be toggled here. Use the approval action for pending accounts.');
        }

        return back()->with('success', $message);
    }

    public function approve(Customer $customer): RedirectResponse
    {
        abort_unless($customer->hasVerifiedEmail(), 422, 'The customer must verify their email before approval.');
        abort_unless($customer->status === 'pending_approval', 422, 'This customer account is not waiting for approval.');

        $customer->forceFill(['status' => 'active'])->save();

        return back()->with('success', 'Customer account approved. The customer can now access the dashboard.');
    }

    public function reject(Customer $customer): RedirectResponse
    {
        abort_unless($customer->status === 'pending_approval', 422, 'Only verified accounts waiting for approval can be declined.');

        $customer->forceFill(['status' => 'rejected'])->save();

        return back()->with('success', 'Customer account request declined.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return back()->with('success', 'Customer account deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_ids' => ['required', 'array', 'min:1', 'max:100'],
            'customer_ids.*' => ['required', 'integer', 'distinct', Rule::exists('customers', 'id')],
        ]);

        $ids = array_map('intval', $data['customer_ids']);
        $deletedCount = DB::transaction(fn () => Customer::whereKey($ids)->delete());

        return back()->with('success', $deletedCount . ' customer account(s) deleted successfully.');
    }
}
