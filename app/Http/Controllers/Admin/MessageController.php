<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    /**
     * Display live chat messages (Admin view)
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get all chat messages grouped by client conversations
        $messages = \App\Models\Message::with(['sender', 'recipient'])
            ->whereJsonContains('metadata->type', 'chat')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->body,
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'sender_name' => $message->sender->name ?? 'Unknown',
                    'created_at' => $message->created_at->toISOString(),
                    'is_from_admin' => $message->sender?->hasAnyRole(['super-admin', 'administrator', 'staff']) ?? false,
                    'client_id' => $message->sender?->hasRole('client') ? $message->sender_id : $message->recipient_id,
                ];
            });

        // Get list of clients who have sent messages
        $clients = \App\Models\User::whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->where(function ($query) {
                $query->whereHas('sentMessages', function ($query) {
                    $query->whereJsonContains('metadata->type', 'chat');
                })
                ->orWhereHas('receivedMessages', function ($query) {
                    $query->whereJsonContains('metadata->type', 'chat');
                });
            })
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                ];
            });

        return Inertia::render('Admin/Messages/Index', [
            'messages' => $messages,
            'clients' => $clients,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => true,
            ],
        ]);
    }

    /**
     * Store a new chat message from admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'recipient_id' => 'required|integer|exists:users,id',
        ]);

        $user = auth()->user();
        
        // Admin must specify which client to reply to
        $recipientId = $request->recipient_id;

        $message = \App\Models\Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'subject' => 'Chat Message',
            'body' => $request->message,
            'priority' => 'normal',
            'is_read' => false,
            'metadata' => [
                'type' => 'chat',
                'timestamp' => now()->toISOString(),
                'admin_message' => true,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->body,
                'sender_id' => $message->sender_id,
                'recipient_id' => $message->recipient_id,
                'sender_name' => $user->name,
                'created_at' => $message->created_at->toISOString(),
                'is_from_admin' => true,
                'client_id' => $recipientId,
            ]
        ]);
    }



    /**
     * Display the specified message
     */
    public function show($id)
    {
        // Dummy message data
        $message = (object) [
            'id' => $id,
            'client_name' => 'John Smith',
            'client_email' => 'john.smith@example.com',
            'subject' => 'Question about LLC Operating Agreement',
            'message' => 'Hi, I have a question about the operating agreement for my LLC. When will it be ready?',
            'created_at' => now()->subMinutes(30)->toISOString(),
            'is_urgent' => false,
            'status' => 'read',
            'order_number' => 'ORD-2025-001'
        ];

        return Inertia::render('Admin/Messages/Show', [
            'message' => $message
        ]);
    }

    /**
     * Update the specified message
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:read,unread,replied'
        ]);

        // TODO: Update message status in database
        return back()->with('success', 'Message updated successfully!');
    }
}