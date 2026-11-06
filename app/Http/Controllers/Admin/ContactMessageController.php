<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:unread,read',
            'search' => 'nullable|string|max:120',
        ]);

        $query = ContactMessage::query()->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        }

        $messages = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('admin.contact-messages._table', compact('messages'));
        }

        $stats = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::where('status', 'unread')->count(),
            'today' => ContactMessage::whereDate('created_at', today())->count(),
        ];

        return view('admin.contact-messages.index', compact('messages', 'stats'));
    }

    public function show(ContactMessage $contactMessage)
    {
        $contactMessage->markAsRead();

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    public function toggleRead(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->forceFill(['status' => 'read', 'read_at' => now()])->save();
            $notice = 'Message marked as read.';
        } else {
            $contactMessage->forceFill(['status' => 'unread', 'read_at' => null])->save();
            $notice = 'Message marked as unread.';
        }

        return back()->with('success', $notice);
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')->with('success', 'Contact message deleted.');
    }
}
