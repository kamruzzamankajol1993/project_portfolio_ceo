<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\HomepageSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Honeypot field: silently accept bot submissions without storing them.
        if ($request->filled('website')) {
            return back()->with('newsletter_success', 'Thank you for subscribing.');
        }

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:191'],
        ]);

        $subscriber = NewsletterSubscriber::where('email', mb_strtolower(trim($data['email'])))->first();

        if ($subscriber) {
            $subscriber->forceFill([
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ])->save();
        } else {
            NewsletterSubscriber::create([
                'email' => mb_strtolower(trim($data['email'])),
                'status' => 'subscribed',
                'subscribed_at' => now(),
            ]);
        }

        $successMessage = HomepageSetting::getValue(
            'newsletter_success_message',
            'Thank you for subscribing to our newsletter.'
        );

        return back()->with('newsletter_success', (string) $successMessage);
    }
}
