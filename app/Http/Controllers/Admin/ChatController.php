<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ChatwootService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    protected $chatwoot;

    public function __construct(ChatwootService $chatwoot)
    {
        $this->chatwoot = $chatwoot;
    }

    /**
     * Display all conversations
     */
    public function index()
    {
        $conversations = $this->chatwoot->getConversations([
            'status' => 'open',
            'assignee_type' => 'all',
        ]);

        return Inertia::render('Admin/Chat/Index', [
            'conversations' => $conversations ?? [],
        ]);
    }

    /**
     * Show specific conversation
     */
    public function show($conversationId)
    {
        $conversation = $this->chatwoot->getConversation($conversationId);
        $messages = $this->chatwoot->getMessages($conversationId);

        return Inertia::render('Admin/Chat/Show', [
            'conversation' => $conversation,
            'messages' => $messages ?? [],
        ]);
    }

    /**
     * Send a message to client
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'private' => 'boolean',
        ]);

        $response = $this->chatwoot->sendMessage(
            $conversationId,
            $validated['message'],
            $validated['private'] ?? false
        );

        return back()->with('success', 'Message sent successfully');
    }

    /**
     * Assign conversation to agent
     */
    public function assign(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'agent_id' => 'required|integer',
        ]);

        $this->chatwoot->assignConversation($conversationId, $validated['agent_id']);

        return back()->with('success', 'Conversation assigned');
    }

    /**
     * Toggle conversation status
     */
    public function toggleStatus($conversationId)
    {
        $conversation = $this->chatwoot->getConversation($conversationId);
        $newStatus = $conversation['status'] === 'open' ? 'resolved' : 'open';
        
        $this->chatwoot->toggleStatus($conversationId, $newStatus);

        return back()->with('success', 'Status updated');
    }

    /**
     * Get real-time updates (polling endpoint)
     */
    public function pollUpdates(Request $request)
    {
        $lastFetch = $request->input('last_fetch');
        
        $conversations = $this->chatwoot->getConversations([
            'status' => 'open',
        ]);

        return response()->json([
            'conversations' => $conversations ?? [],
            'timestamp' => now()->timestamp,
        ]);
    }
}
