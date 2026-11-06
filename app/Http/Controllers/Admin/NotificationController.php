<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function customerRegistrations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'after_id' => ['required', 'integer', 'min:0'],
        ]);

        $afterId = (int) $validated['after_id'];
        $customers = Customer::query()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->limit(20)
            ->get(['id', 'name', 'email', 'status', 'created_at']);

        return response()->json([
            'registrations' => $customers->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'status' => $customer->status,
                'created_at' => $customer->created_at?->toIso8601String(),
            ])->values(),
            'next_id' => $customers->last()?->id ?? $afterId,
            'pending_count' => Customer::query()
                ->whereIn('status', ['pending_verification', 'pending_approval'])
                ->count(),
        ]);
    }
}
