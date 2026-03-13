<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Order;
use App\Models\Document;
use App\Models\Message;
use App\Models\Notification;

class DashboardController extends Controller
{
    /**
     * Display the client dashboard.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Cache user stats for 2 minutes per user
        $stats = cache()->remember("user_dashboard_stats_{$user->id}", 120, function () use ($user) {
            return [
                'total_orders' => $user->orders()->count(),
                'active_orders' => $user->orders()->whereIn('status', ['pending', 'in_progress', 'under_review'])->count(),
                'completed_orders' => $user->orders()->where('status', 'completed')->count(),
                'total_spent' => $user->payments()->where('status', 'succeeded')->sum('amount'),
                'pending_documents' => $user->documents()->where('status', 'pending_review')->count(),
                'unread_messages' => $user->receivedMessages()->where('is_read', false)->count(),
            ];
        });

        // Cache recent orders for 1 minute per user
        $recent_orders = cache()->remember("user_recent_orders_{$user->id}", 60, function () use ($user) {
            return $user->orders()
                ->select(['id', 'order_number', 'service_type', 'status', 'created_at', 'entity_name', 'total_amount'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'service_type' => $this->getServiceTypeDisplayName($order->service_type),
                        'status' => $order->status,
                        'created_at' => $order->created_at->toISOString(),
                        'business_name' => $order->entity_name,
                        'total' => $order->total_amount * 100, // Convert to cents for frontend
                    ];
                });
        });

        // Cache recent documents for 1 minute per user
        $recent_documents = cache()->remember("user_recent_documents_{$user->id}", 60, function () use ($user) {
            return $user->documents()
                ->select(['id', 'display_name', 'name', 'type', 'created_at', 'file_size', 'status'])
                ->where('status', 'approved')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($document) {
                    return [
                        'id' => $document->id,
                        'name' => $document->display_name ?: $document->name,
                        'type' => $document->type_display_name,
                        'created_at' => $document->created_at->toISOString(),
                        'size' => $document->formatted_file_size,
                        'status' => $document->status,
                    ];
                });
        });

        // Cache recent messages for 1 minute per user
        $recent_messages = cache()->remember("user_recent_messages_{$user->id}", 60, function () use ($user) {
            return $user->receivedMessages()
                ->with('sender:id,first_name,last_name')
                ->select(['id', 'subject', 'sender_id', 'is_read', 'created_at', 'priority'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'subject' => $message->subject,
                        'from' => $message->sender_display_name,
                        'is_read' => $message->is_read,
                        'created_at' => $message->created_at->toISOString(),
                        'priority' => $message->priority,
                    ];
                });
        });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recent_orders' => $recent_orders,
            'recent_documents' => $recent_documents,
            'recent_messages' => $recent_messages,
        ]);
    }

    /**
     * Get service type display name
     */
    private function getServiceTypeDisplayName($serviceType)
    {
        return match($serviceType) {
            'c_corp' => 'C-Corporation',
            's_corp' => 'S-Corporation', 
            'llc' => 'LLC Formation',
            'nonprofit' => 'Nonprofit Organization',
            'green_card' => 'Green Card Lottery',
            'ein_only' => 'EIN Only',
            'registered_agent' => 'Registered Agent',
            'compliance_kit' => 'Compliance Kit',
            'tax_filing' => 'Tax Filing',
            'bookkeeping' => 'Bookkeeping',
            default => ucfirst(str_replace('_', ' ', $serviceType))
        };
    }
}
