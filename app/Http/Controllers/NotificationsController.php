<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(string $id)
    {
        $notification = DatabaseNotification::where('id', $id)
            ->where('notifiable_id', Auth::id())
            ->where('notifiable_type', Auth::user()::class)
            ->firstOrFail();

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function markUnread(string $id)
    {
        $notification = DatabaseNotification::where('id', $id)
            ->where('notifiable_id', Auth::id())
            ->where('notifiable_type', Auth::user()::class)
            ->firstOrFail();

        $notification->update(['read_at' => null]);

        return back()->with('success', 'Notification marked as unread.');
    }

    public function destroy(string $id)
    {
        $notification = DatabaseNotification::where('id', $id)
            ->where('notifiable_id', Auth::id())
            ->where('notifiable_type', Auth::user()::class)
            ->firstOrFail();

        $notification->delete();

        return back()->with('success', 'Notification dismissed.');
    }
}
