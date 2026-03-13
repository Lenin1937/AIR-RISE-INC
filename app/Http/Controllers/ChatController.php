<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\AiMessage;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Generator;

class ChatController extends Controller
{
    public function __construct(
        private OpenAiService $aiService
    ) {}

    /**
     * Start new chat session
     */
    public function startSession(Request $request)
    {
        $validated = $request->validate([
            'page_url' => 'required|string',
            'page_name' => 'required|string',
            'page_context' => 'required|string',
        ]);

        $chat = AiChat::create([
            'session_id' => 'chat_' . Str::random(32),
            'user_id' => auth()->id(),
            'user_type' => auth()->check() ? (auth()->user()->role ?? 'client') : 'guest',
            'page_url' => $validated['page_url'],
            'page_name' => $validated['page_name'],
            'status' => 'active',
            'metadata' => [
                'page_context' => $validated['page_context'],
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ],
        ]);

        return response()->json([
            'session_id' => $chat->session_id,
            'chat_id' => $chat->id,
        ]);
    }

    /**
     * Stream chat response using Laravel streaming
     */
    public function stream(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string|max:2000',
            'page_context' => 'required|string',
        ]);

        $chat = AiChat::where('session_id', $validated['session_id'])->firstOrFail();

        // Save user message
        $userMessage = AiMessage::create([
            'ai_chat_id' => $chat->id,
            'role' => 'user',
            'content' => $validated['message'],
            'page_context' => $validated['page_context'],
        ]);

        $chat->incrementMessageCount();

        // Get conversation history
        $messages = $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();

        // Stream response using Laravel's official pattern
        return response()->stream(function () use ($chat, $messages, $validated) {
            $startTime = microtime(true);
            $fullResponse = '';
            
            foreach ($this->aiService->streamResponse($messages, $validated['page_context']) as $chunk) {
                echo $chunk;
                
                // Extract content for storage
                $decoded = json_decode(trim($chunk), true);
                if ($decoded && $decoded['type'] === 'content') {
                    $fullResponse .= $decoded['data'];
                }
                
                // Flush the output buffer
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            
            $responseTime = (int) round((microtime(true) - $startTime) * 1000);

            // Save assistant response
            if ($fullResponse) {
                AiMessage::create([
                    'ai_chat_id' => $chat->id,
                    'role' => 'assistant',
                    'content' => $fullResponse,
                    'model' => 'gpt-4o-mini',
                    'response_time_ms' => $responseTime,
                    'is_streamed' => true,
                    'page_context' => $validated['page_context'],
                ]);

                $chat->incrementMessageCount();
            }
        }, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get chat history
     */
    public function history(Request $request, string $sessionId)
    {
        $chat = AiChat::where('session_id', $sessionId)->firstOrFail();

        // Ensure user owns this chat
        if ($chat->user_id && $chat->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $messages = $chat->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at->toIso8601String(),
            ]);

        return response()->json([
            'messages' => $messages,
            'chat' => [
                'session_id' => $chat->session_id,
                'status' => $chat->status,
                'page_name' => $chat->page_name,
            ],
        ]);
    }

    /**
     * Close chat session
     */
    public function close(Request $request, string $sessionId)
    {
        $chat = AiChat::where('session_id', $sessionId)->firstOrFail();

        if ($chat->user_id && $chat->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $chat->close();

        return response()->json(['message' => 'Chat closed successfully']);
    }

    /**
     * Mark chat as lead
     */
    public function markAsLead(Request $request, string $sessionId)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $chat = AiChat::where('session_id', $sessionId)->firstOrFail();

        $chat->markAsLead($validated['email'], [
            'name' => $validated['name'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return response()->json(['message' => 'Marked as lead successfully']);
    }
}
