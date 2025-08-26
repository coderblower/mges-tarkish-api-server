<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserDeletedNotification;

class ApiNotificationController extends Controller
{
    /**
     * Send notification to a specific user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Get all notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotification()
    {
        try {
            $notifications = auth()->user()->unreadNotifications;

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark a specific notification as read.
     *
     * @param string $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($notificationId)
    {
        try {
            $notification = auth()->user()->notifications()->findOrFail($notificationId);
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        try {
            auth()->user()->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
