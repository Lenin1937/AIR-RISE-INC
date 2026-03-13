<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    /**
     * Display live chat messages.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get chat messages (both sent and received)
        $messages = Message::where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->with(['sender', 'recipient'])
            ->whereJsonContains('metadata->type', 'chat')
            ->orderBy('created_at', 'asc')
            ->limit(100) // Limit to last 100 messages
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->body,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name ?? 'Unknown',
                    'created_at' => $message->created_at->toISOString(),
                    'is_from_admin' => $message->sender?->hasAnyRole(['super-admin', 'administrator', 'staff']) ?? false,
                ];
            });

        // HMAC identity hash so Chatwoot links conversations to the real user
        $identitySecret = config('services.chatwoot.identity_secret');
        $identifierHash = $identitySecret
            ? hash_hmac('sha256', (string) $user->id, $identitySecret)
            : null;

        return Inertia::render('Messages/Index', [
            'messages' => $messages,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->hasAnyRole(['super-admin', 'administrator', 'staff']),
            ],
            'chatwootToken'    => config('services.chatwoot.website_token'),
            'identifierHash'   => $identifierHash,
        ]);
    }

    /**
     * Display the specified message.
     */
    public function show(Message $message): Response
    {
        // Ensure user can only view their own messages
        if ($message->recipient_id !== auth()->id()) {
            abort(403);
        }

        // Mark message as read if not already read
        if (!$message->is_read) {
            $message->markAsRead();
        }

        $message->load(['sender', 'recipient']);

        $messageData = [
            'id' => $message->id,
            'subject' => $message->subject,
            'body' => $message->body,
            'is_read' => $message->is_read,
            'priority' => $message->priority,
            'priority_display' => $message->priority_display,
            'created_at' => $message->created_at->toISOString(),
            'updated_at' => $message->updated_at->toISOString(),
            'read_at' => $message->read_at?->toISOString(),
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender_display_name,
                'email' => $message->sender->email,
                'avatar' => $message->sender->avatar_url,
                'role' => $message->sender->roles->first()?->name,
            ],
            'recipient' => [
                'id' => $message->recipient->id,
                'name' => $message->recipient->full_name,
                'email' => $message->recipient->email,
            ],
            'metadata' => $message->metadata,
            'attachments' => $message->metadata['attachments'] ?? [],
        ];

        return Inertia::render('Messages/Show', [
            'message' => $messageData,
        ]);
    }

    /**
     * Store a new chat message.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // If user is admin, they must specify recipient_id in request
        if ($user->hasAnyRole(['super-admin', 'administrator', 'staff'])) {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
                'recipient_id' => 'required|integer|exists:users,id',
                'order_id' => 'nullable|integer|exists:orders,id'
            ]);
            
            $recipient = User::find($validated['recipient_id']);
        } else {
            // Client sending to admin - validate without recipient_id
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
                'order_id' => 'nullable|integer|exists:orders,id'
            ]);
            
            // Client sending to admin - find any admin/staff
            $recipient = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super-admin', 'admin', 'staff']);
            })->first();
        }

        if (!$recipient) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No recipient available to receive your message.'
                ], 400);
            }
            return back()->withErrors(['message' => 'No recipient available to receive your message.']);
        }

        $message = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $recipient->id,
            'subject' => 'Live Chat Message',
            'body' => $validated['message'],
            'priority' => 'normal',
            'is_read' => false,
            'metadata' => [
                'type' => 'chat',
                'order_id' => $validated['order_id'] ?? null,
                'source' => 'live_chat',
            ],
        ]);

        // If this is an AJAX request, return JSON response
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'message' => $message->body,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $user->name,
                    'created_at' => $message->created_at->toISOString(),
                    'is_from_admin' => $user->hasAnyRole(['super-admin', 'administrator', 'staff']),
                ]
            ]);
        }

        return redirect()->route('messages.index')->with([
            'success' => 'Message sent!',
            'message' => [
                'id' => $message->id,
                'message' => $message->body,
                'sender_id' => $message->sender_id,
                'sender_name' => $user->name,
                'created_at' => $message->created_at->toISOString(),
                'is_from_admin' => $user->hasAnyRole(['super-admin', 'administrator', 'staff']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new message.
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        // Get user's orders for the order selection dropdown
        $orders = $user->orders()
            ->select('id', 'order_number', 'service_type', 'entity_name')
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'label' => $order->order_number . ' - ' . $order->service_type_name . ' (' . $order->entity_name . ')',
                    'order_number' => $order->order_number,
                    'service_type' => $order->service_type_name,
                    'entity_name' => $order->entity_name,
                ];
            });

        return Inertia::render('Messages/Create', [
            'orders' => $orders,
        ]);
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(Message $message)
    {
        // Ensure user can only mark their own messages as read
        if ($message->recipient_id !== auth()->id()) {
            abort(403);
        }

        $message->markAsRead();

        return response()->json([
            'message' => 'Message marked as read',
            'message_id' => $message->id
        ]);
    }

    /**
     * Mark a message as unread.
     */
    public function markAsUnread(Message $message)
    {
        // Ensure user can only mark their own messages as unread
        if ($message->recipient_id !== auth()->id()) {
            abort(403);
        }

        $message->markAsUnread();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all messages as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->receivedMessages()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'message' => 'All messages marked as read'
        ]);
    }
}