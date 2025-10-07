<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Zeige alle Benachrichtigungen
     */
    public function index()
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = $this->notificationService->getUnreadCount($user);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Markiere Benachrichtigung als gelesen
     */
    public function markAsRead(Notification $notification)
    {
        // Sicherheit: Nur eigene Benachrichtigungen
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'Benachrichtigung als gelesen markiert'
        ]);
    }

    /**
     * Markiere alle Benachrichtigungen als gelesen
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'Alle Benachrichtigungen als gelesen markiert'
        ]);
    }

    /**
     * Lösche Benachrichtigung
     */
    public function destroy(Notification $notification)
    {
        // Sicherheit: Nur eigene Benachrichtigungen
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Benachrichtigung gelöscht'
        ]);
    }

    /**
     * Hole ungelesene Benachrichtigungen (für Dropdown)
     */
    public function getUnread()
    {
        $user = Auth::user();
        
        $notifications = $this->notificationService->getUnreadNotifications($user)
            ->take(5); // Nur die letzten 5 für Dropdown

        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}