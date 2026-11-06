<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PublicContactController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // Honeypot: return a normal success response to bots without storing spam.
        if ($request->filled('website')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Thank you. Your message has been received.'], 201)
                : back()->with('success', 'Thank you. Your message has been received.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:180',
            'email' => 'required|email:rfc|max:191',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:180',
            'message' => 'required|string|min:10|max:5000',
        ]);

        ContactMessage::create([
            ...$data,
            'status' => 'unread',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        $message = 'Your message has been sent successfully. Our team will review it shortly.';

        return $request->expectsJson()
            ? response()->json(['message' => $message], 201)
            : back()->with('success', $message);
    }
}
