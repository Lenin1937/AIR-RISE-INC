<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $notifications = $user->notifications()
            ->when($request->unread_only, function ($query) {
                return $query->where('is_read', false);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = $user->notifications()->where('is_read', false)->count();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'filters' => $request->only(['unread_only', 'type']),
            'notificationTypes' => $this->getNotificationTypes(),
            'stats' => [
                'total' => $user->notifications()->count(),
                'unread' => $unreadCount,
                'today' => $user->notifications()->whereDate('created_at', today())->count(),
                'this_week' => $user->notifications()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
            ],
            'preferences' => [
                'email_notifications' => true,
                'order_updates' => true,
                'document_notifications' => true,
                'compliance_reminders' => true,
                'marketing_emails' => false
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Ensure user can only mark their own notifications as read
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        auth()->user()->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'order_updates' => 'boolean',
            'document_notifications' => 'boolean',
            'compliance_reminders' => 'boolean',
            'marketing_emails' => 'boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Map validated fields to actual DB columns and save.
        // order_updates / document_notifications / compliance_reminders have no
        // dedicated columns yet; only persist what the schema supports.
        $dbUpdate = [];

        if (isset($validated['email_notifications'])) {
            $dbUpdate['email_notifications'] = $validated['email_notifications'];
        }

        if (isset($validated['marketing_emails'])) {
            $dbUpdate['marketing_consent'] = $validated['marketing_emails'];
        }

        if (!empty($dbUpdate)) {
            $user->update($dbUpdate);
        }

        return response()->json([
            'message' => 'Notification preferences updated successfully!',
            'preferences' => $validated,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification)
    {
        // Ensure user can only delete their own notifications
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    private function getNotificationTypes()
    {
        return [
            'document_uploaded' => 'Document Uploaded',
            'document_approved' => 'Document Approved',
            'document_rejected' => 'Document Rejected',
            'document_expiring' => 'Document Expiring',
            'document_expired' => 'Document Expired',
            'order_status_changed' => 'Order Status Changed',
            'order_approved' => 'Order Approved',
            'order_rejected' => 'Order Rejected',
            'payment_received' => 'Payment Received',
            'compliance_reminder' => 'Compliance Reminder',
            'system' => 'System Notification',
        ];
    }

    /**
     * Create a document-related notification
     */
    public static function createDocumentNotification(User $user, Document $document, string $type, array $data = [])
    {
        // Map document types to notification enum types
        $typeMapping = [
            'document_uploaded' => 'document_ready',
            'document_approved' => 'document_ready',
            'document_rejected' => 'document_required',
            'document_expiring' => 'compliance_reminder',
            'document_expired' => 'compliance_reminder',
        ];

        $messages = [
            'document_uploaded' => 'A new document has been uploaded for you',
            'document_approved' => 'Your document has been approved',
            'document_rejected' => 'Your document has been rejected',
            'document_expiring' => 'Your document is expiring soon',
            'document_expired' => 'Your document has been expired',
        ];

        $notificationData = array_merge([
            'document_id' => $document->id,
            'document_name' => $document->name,
            'document_category' => $document->category,
        ], $data);

        Notification::create([
            'user_id' => $user->id,
            'type' => $typeMapping[$type] ?? 'document_ready',
            'title' => $messages[$type] ?? 'Document Update',
            'message' => $messages[$type] ?? 'Document Update',
            'metadata' => $notificationData,
        ]);
    }

    /**
     * Create an order-related notification
     */
    public static function createOrderNotification(User $user, $order, string $type, array $data = [])
    {
        // Map order types to notification enum types
        $typeMapping = [
            'order_status_changed' => 'order_update',
            'order_approved' => 'order_update',
            'order_rejected' => 'order_update',
            'payment_received' => 'payment_received',
        ];

        $messages = [
            'order_status_changed' => 'Your order status has been updated',
            'order_approved' => 'Your order has been approved',
            'order_rejected' => 'Your order has been rejected',
            'payment_received' => 'Payment confirmation received',
        ];

        $notificationData = array_merge([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'entity_name' => $order->entity_name,
        ], $data);

        Notification::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => $typeMapping[$type] ?? 'order_update',
            'title' => $messages[$type] ?? 'Order Update',
            'message' => $messages[$type] ?? 'Order Update',
            'metadata' => $notificationData,
        ]);
    }

    /**
     * Send document expiration reminders
     */
    public static function sendExpirationReminders()
    {
        // Find documents expiring in 30 days
        $expiringDocuments = Document::where('expires_at', '<=', now()->addDays(30))
            ->where('expires_at', '>', now())
            ->whereDoesntHave('user.notifications', function ($query) {
                $query->where('type', 'compliance_reminder')
                    ->where('created_at', '>=', now()->subDays(7)); // Don't spam notifications
            })
            ->with('user')
            ->get();

        foreach ($expiringDocuments as $document) {
            if ($document->user) {
                self::createDocumentNotification(
                    $document->user,
                    $document,
                    'document_expiring',
                    ['expires_at' => $document->expires_at->toISOString()]
                );
            }
        }

        // Find expired documents and archive them (status must be a DB-valid value)
        $expiredDocuments = Document::where('expires_at', '<=', now())
            ->whereNotIn('status', ['archived', 'rejected'])
            ->with('user')
            ->get();

        foreach ($expiredDocuments as $document) {
            // 'expired' is not in the DB constraint — use 'archived' instead
            $document->update(['status' => 'archived']);

            if ($document->user) {
                self::createDocumentNotification(
                    $document->user,
                    $document,
                    'document_expired',
                    ['expired_at' => $document->expires_at->toISOString()]
                );
            }
        }
    }
}
