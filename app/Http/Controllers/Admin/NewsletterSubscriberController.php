<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:subscribed,unsubscribed'],
            'search' => ['nullable', 'string', 'max:191'],
        ]);

        $query = NewsletterSubscriber::query()->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where('email', 'like', "%{$term}%");
        }

        $subscribers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => NewsletterSubscriber::count(),
            'subscribed' => NewsletterSubscriber::where('status', 'subscribed')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
        ];

        return view('admin.newsletter-subscribers.index', compact('subscribers', 'stats'));
    }

    public function toggleStatus(NewsletterSubscriber $subscriber)
    {
        if ($subscriber->status === 'subscribed') {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
            $message = 'Subscriber unsubscribed.';
        } else {
            $subscriber->update([
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
            $message = 'Subscriber reactivated.';
        }

        return back()->with('success', $message);
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber removed.');
    }
}
