<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated user.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();

        $count = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent unread notifications.
     */
    public function recent(): JsonResponse
    {
        $user = Auth::user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(AppNotification $notification): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();

        AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(AppNotification $notification): RedirectResponse
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return redirect()->back()->with('success', 'Уведомление успешно удалено');
    }
}
