<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $request->user()
                ->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'message' => $notification->data['message'] ?? 'Новое уведомление',
                        'type' => $notification->data['type'] ?? null,
                        'created_at' => $notification->created_at?->toISOString(),
                        'read_at' => $notification->read_at?->toISOString(),
                    ];
                }),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
