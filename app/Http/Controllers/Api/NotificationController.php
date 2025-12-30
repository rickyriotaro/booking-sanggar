<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Store FCM token for user
     * 
     * POST /api/notifications/fcm-token
     * Body: { "fcm_token": "token_string" }
     */
    public function storeFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get fresh user instance from database and update
        $user = User::find($user->id);
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'message' => 'FCM token stored successfully',
            'data' => [
                'user_id' => $user->id,
                'fcm_token' => $user->fcm_token,
            ],
        ]);
    }

    /**
     * Get unread notifications for current user
     * GET /api/notifications/unread?limit=50
     */
    public function unread(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $limit = min($request->get('limit', 50), 100);
        
        $notifications = $user->notifications()
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Unread notifications retrieved',
            'data' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    /**
     * Get all notifications for current user
     * GET /api/notifications?page=1&limit=20
     */
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $limit = min($request->get('limit', 20), 100);
        $page = $request->get('page', 1);
        
        // Try new Notification model first, fallback to NotificationLog
        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'count' => $notifications->count(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * Mark notification as read
     * PATCH /api/notifications/{id}/read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Try new Notification model first
        $notification = Notification::where('user_id', $user->id)->find($id);
        
        if (!$notification) {
            // Fallback to NotificationLog
            $notification = NotificationLog::where('user_id', $user->id)->find($id);
            if ($notification) {
                if (!$notification->read_at) {
                    $notification->update(['read_at' => now()]);
                }
                return response()->json([
                    'message' => 'Notification marked as read',
                    'data' => $notification,
                ]);
            }
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     * POST /api/notifications/mark-all-read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete notification
     * DELETE /api/notifications/{id}
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('user_id', $user->id)->find($id);
        
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }
}
